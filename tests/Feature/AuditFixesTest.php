<?php

use App\Http\Resources\PaymentResource;
use App\Models\Admin;
use App\Models\Batch;
use App\Models\BatchType;
use App\Models\Payment;
use App\Models\Session;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Trainee;
use App\Models\Trainer;
use App\Models\Training;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Silber\Bouncer\BouncerFacade;

beforeEach(function () {
    BouncerFacade::dontCache();
});

describe('ulid morph schema fixes', function () {
    test('bouncer and sanctum morph id columns use string/varchar types instead of integer', function () {
        foreach ([
            ['abilities', 'entity_id'],
            ['assigned_roles', 'entity_id'],
            ['assigned_roles', 'restricted_to_id'],
            ['permissions', 'entity_id'],
            ['personal_access_tokens', 'tokenable_id'],
        ] as [$table, $column]) {
            expect(Schema::getColumnType($table, $column))->toBeIn(['varchar', 'string', 'char']);
        }
    });

    test('sanctum personal access token stores user ulid in tokenable_id', function () {
        $user = User::factory()->create();
        $token = $user->createToken('api-token');

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $token->accessToken->id,
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
        ]);
    });
});

describe('password exclusion on user updates', function () {
    test('preserves existing password when null, empty string, or whitespace is submitted', function (string $endpoint, Closure $createModel, mixed $passwordInput) {
        $superAdmin = Admin::factory()->superAdmin()->create();
        $target = $createModel();

        Sanctum::actingAs($superAdmin);

        $this->putJson("/api/{$endpoint}/{$target->id}", [
            'first_name' => 'UpdatedName',
            'password' => $passwordInput,
        ])->assertOk()
            ->assertJsonPath('data.first_name', 'UpdatedName');

        $fresh = $target->fresh();

        expect($fresh->password)->not->toBeNull()
            ->and(Hash::check('original-pass', $fresh->password))->toBeTrue();
    })->with([
        'admin with null' => ['admin', fn () => Admin::factory()->create(['password' => 'original-pass']), null],
        'admin with empty' => ['admin', fn () => Admin::factory()->create(['password' => 'original-pass']), ''],
        'staff with null' => ['staff', fn () => Staff::factory()->create(['password' => 'original-pass']), null],
        'staff with empty' => ['staff', fn () => Staff::factory()->create(['password' => 'original-pass']), ''],
        'staff with whitespace' => ['staff', fn () => Staff::factory()->create(['password' => 'original-pass']), '   '],
        'trainer with null' => ['trainer', fn () => Trainer::factory()->create(['password' => 'original-pass']), null],
        'trainer with empty' => ['trainer', fn () => Trainer::factory()->create(['password' => 'original-pass']), ''],
        'trainee with null' => ['trainee', fn () => Trainee::factory()->create(['password' => 'original-pass']), null],
        'trainee with empty' => ['trainee', fn () => Trainee::factory()->create(['password' => 'original-pass']), ''],
    ]);

    test('rejects too-short non-empty password and updates when valid password is supplied', function () {
        $admin = Admin::factory()->create();
        $staff = Staff::factory()->create(['password' => 'original-pass']);

        Sanctum::actingAs($admin);

        $this->putJson("/api/staff/{$staff->id}", [
            'password' => 'short',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);

        expect(Hash::check('original-pass', $staff->fresh()->password))->toBeTrue();

        $this->putJson("/api/staff/{$staff->id}", [
            'password' => 'new-secret-123',
        ])->assertOk();

        expect(Hash::check('new-secret-123', $staff->fresh()->password))->toBeTrue();
    });
});

describe('batch trainer_id validation', function () {
    test('accepts a valid trainer on batch store and update', function () {
        $admin = Admin::factory()->create();
        $batchType = BatchType::factory()->create();
        $session = Session::factory()->create();
        $trainer1 = Trainer::factory()->create();
        $trainer2 = Trainer::factory()->create();

        Sanctum::actingAs($admin);

        $storeRes = $this->postJson('/api/batch', [
            'batch_type_id' => $batchType->id,
            'session_id' => $session->id,
            'trainer_id' => $trainer1->id,
        ]);

        $storeRes->assertCreated()
            ->assertJsonPath('data.trainer_id', $trainer1->id);

        $batchId = $storeRes->json('data.id');

        $this->putJson("/api/batch/{$batchId}", [
            'trainer_id' => $trainer2->id,
        ])->assertOk()
            ->assertJsonPath('data.trainer_id', $trainer2->id);
    });

    test('rejects non-trainer users and nonexistent ids for trainer_id on store and update', function (Closure $resolveId) {
        $admin = Admin::factory()->create();
        $batchType = BatchType::factory()->create();
        $session = Session::factory()->create();
        $validTrainer = Trainer::factory()->create();
        $batch = Batch::factory()->create([
            'batch_type_id' => $batchType->id,
            'session_id' => $session->id,
            'trainer_id' => $validTrainer->id,
        ]);
        $invalidId = $resolveId();

        Sanctum::actingAs($admin);

        $this->postJson('/api/batch', [
            'batch_type_id' => $batchType->id,
            'session_id' => $session->id,
            'trainer_id' => $invalidId,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['trainer_id']);

        $this->putJson("/api/batch/{$batch->id}", [
            'trainer_id' => $invalidId,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['trainer_id']);

        expect(Batch::count())->toBe(1)
            ->and($batch->fresh()->trainer_id)->toBe($validTrainer->id);
    })->with([
        'plain user' => [fn (): string => User::factory()->create()->id],
        'trainee' => [fn (): string => Trainee::factory()->create()->id],
        'staff' => [fn (): string => Staff::factory()->create()->id],
        'admin' => [fn (): string => Admin::factory()->create()->id],
        'non-existent id' => [fn (): string => '01ARZ3NDEKTSV4RRFFQ69G5FAV'],
    ]);
});

describe('payment resource and update request', function () {
    test('payment responses omit training_id, include amount, and update succeeds without training_id', function () {
        $admin = Admin::factory()->create();
        $student = Student::factory()->create(['fee' => 300]);

        Sanctum::actingAs($admin);

        $storeRes = $this->postJson('/api/payment', [
            'student_id' => $student->id,
            'amount' => 300,
        ]);

        $storeRes->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'student_id',
                    'registrar_id',
                    'amount',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonMissingPath('data.training_id')
            ->assertJsonPath('data.student_id', $student->id)
            ->assertJsonPath('data.amount', 300);

        $paymentId = $storeRes->json('data.id');

        $this->getJson("/api/payment/{$paymentId}")
            ->assertOk()
            ->assertJsonMissingPath('data.training_id')
            ->assertJsonPath('data.id', $paymentId);

        $this->putJson("/api/payment/{$paymentId}", [
            'amount' => 350,
        ])->assertOk()
            ->assertJsonMissingPath('data.training_id')
            ->assertJsonPath('data.amount', 350);

        $payment = Payment::find($paymentId)->load('training');
        $withTraining = (new PaymentResource($payment))->toResponse(request())->getData(true)['data'];

        expect($withTraining)->not->toHaveKey('training_id')
            ->and($withTraining['training']['id'])->toBe($student->training_id);
    });
});

describe('payment deletion restriction', function () {
    test('throws AuthorizationException with 409 when deleting student, trainee, or training directly with payments and enforces restrictOnDelete at database level', function () {
        $payment = Payment::factory()->create();
        $student = $payment->student;
        $trainee = $student->trainee;
        $training = $student->training;

        $studentException = null;
        try {
            $student->delete();
        } catch (AuthorizationException $e) {
            $studentException = $e;
        }

        expect($studentException)->toBeInstanceOf(AuthorizationException::class)
            ->and($studentException->getMessage())->toBe('Cannot delete a student who has payments')
            ->and($studentException->status())->toBe(409);

        $traineeException = null;
        try {
            $trainee->delete();
        } catch (AuthorizationException $e) {
            $traineeException = $e;
        }

        expect($traineeException)->toBeInstanceOf(AuthorizationException::class)
            ->and($traineeException->getMessage())->toBe('Cannot delete a trainee that already has payments.')
            ->and($traineeException->status())->toBe(409);

        $trainingException = null;
        try {
            $training->delete();
        } catch (AuthorizationException $e) {
            $trainingException = $e;
        }

        expect($trainingException)->toBeInstanceOf(AuthorizationException::class)
            ->and($trainingException->getMessage())->toBe('Cannot delete a training that already has payments.')
            ->and($trainingException->status())->toBe(409);

        expect(fn () => DB::table('students')->where('id', $student->id)->delete())
            ->toThrow(QueryException::class);

        $this->assertModelExists($student);
        $this->assertModelExists($trainee);
        $this->assertModelExists($training);
        $this->assertModelExists($payment);
    });

    test('returns 409 conflict and preserves payments when deleting a trainee or training with recorded payments', function () {
        $admin = Admin::factory()->create();
        $payment = Payment::factory()->create();
        $student = $payment->student;
        $trainee = $student->trainee;
        $training = $student->training;

        Sanctum::actingAs($admin);

        $this->deleteJson("/api/trainee/{$trainee->id}")
            ->assertConflict()
            ->assertJsonPath('message', 'Cannot delete a student who has payments');

        $this->deleteJson("/api/training/{$training->id}")
            ->assertConflict()
            ->assertJsonPath('message', 'Cannot delete a student who has payments');

        $this->assertModelExists($trainee);
        $this->assertModelExists($training);
        $this->assertModelExists($student);
        $this->assertModelExists($payment);
    });

    test('allows deleting student, trainee, and training when no payments exist', function () {
        $admin = Admin::factory()->create();
        $student = Student::factory()->create();
        $traineeWithoutPayments = Trainee::factory()->create();
        $trainingWithoutPayments = Training::factory()->create();

        Student::factory()->for($traineeWithoutPayments, 'trainee')->create();
        Student::factory()->for($trainingWithoutPayments, 'training')->create();

        $student->delete();
        $this->assertModelMissing($student);

        Sanctum::actingAs($admin);

        $this->deleteJson("/api/trainee/{$traineeWithoutPayments->id}")->assertNoContent();
        $this->deleteJson("/api/training/{$trainingWithoutPayments->id}")->assertNoContent();

        $this->assertModelMissing($traineeWithoutPayments);
        $this->assertModelMissing($trainingWithoutPayments);
    });
});
