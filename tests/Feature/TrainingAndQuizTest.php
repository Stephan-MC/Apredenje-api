<?php

use App\Models\Admin;
use App\Models\Quiz;
use App\Models\Staff;
use App\Models\Trainee;
use App\Models\Trainer;
use App\Models\Training;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Silber\Bouncer\BouncerFacade;

beforeEach(function () {
    BouncerFacade::dontCache();
});

describe('routing registrar', function () {
    test('resolves plural index routes without .index suffix and singular item routes', function () {
        $training = Training::factory()->create();
        $quiz = Quiz::factory()->for($training)->create();

        expect(route('trainings', absolute: false))->toBe('/api/trainings')
            ->and(route('training.store', absolute: false))->toBe('/api/training')
            ->and(route('training.show', $training, absolute: false))->toBe("/api/training/{$training->id}")
            ->and(route('training.quizzes', $training, absolute: false))->toBe("/api/training/{$training->id}/quizzes")
            ->and(route('training.quiz.store', $training, absolute: false))->toBe("/api/training/{$training->id}/quiz")
            ->and(route('training.quiz.show', [$training, $quiz], absolute: false))->toBe("/api/training/{$training->id}/quiz/{$quiz->id}");
    });

    test('legacy flat quiz endpoint returns 404', function () {
        $admin = Admin::factory()->create();
        Sanctum::actingAs($admin);

        $this->getJson('/api/quiz')->assertNotFound();
        $this->getJson('/api/quizzes')->assertNotFound();
    });
});

describe('training endpoints and trainer_id validation', function () {
    test('returns 401 when unauthenticated', function () {
        $training = Training::factory()->create();

        $this->getJson('/api/trainings')->assertUnauthorized();
        $this->postJson('/api/training', [])->assertUnauthorized();
        $this->getJson("/api/training/{$training->id}")->assertUnauthorized();
        $this->putJson("/api/training/{$training->id}", [])->assertUnauthorized();
        $this->deleteJson("/api/training/{$training->id}")->assertUnauthorized();
    });

    test('admin can perform full crud on trainings with valid trainer or null trainer_id', function () {
        $admin = Admin::factory()->create();
        $trainer = Trainer::factory()->create();

        Sanctum::actingAs($admin);

        $storeWithTrainer = $this->postJson('/api/training', [
            'title' => 'Laravel Architecture',
            'code' => 'LAR-101',
            'trainer_id' => $trainer->id,
            'fee' => 450,
        ]);

        $storeWithTrainer->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'code',
                    'trainer_id',
                    'fee',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.title', 'Laravel Architecture')
            ->assertJsonPath('data.code', 'LAR-101')
            ->assertJsonPath('data.trainer_id', $trainer->id)
            ->assertJsonPath('data.fee', 450);

        $trainingId = $storeWithTrainer->json('data.id');
        $training = Training::find($trainingId);

        $this->assertModelExists($training);

        $storeWithoutTrainer = $this->postJson('/api/training', [
            'title' => 'Unassigned Training',
            'code' => 'UN-100',
            'trainer_id' => null,
            'fee' => 199.50,
        ]);

        $storeWithoutTrainer->assertCreated()
            ->assertJsonPath('data.trainer_id', null);

        $this->getJson('/api/trainings')
            ->assertOk()
            ->assertJsonFragment(['id' => $trainingId])
            ->assertJsonFragment(['code' => 'UN-100']);

        $this->getJson("/api/training/{$trainingId}")
            ->assertOk()
            ->assertJsonPath('data.id', $trainingId);

        $this->putJson("/api/training/{$trainingId}", [
            'title' => 'Laravel Architecture v2',
            'code' => 'LAR-101',
        ])->assertOk()
            ->assertJsonPath('data.title', 'Laravel Architecture v2');

        $this->assertDatabaseHas('trainings', [
            'id' => $trainingId,
            'title' => 'Laravel Architecture v2',
        ]);

        $this->deleteJson("/api/training/{$trainingId}")
            ->assertNoContent();

        $this->assertModelMissing($training);
    });

    test('trainer_id rejects non-trainer users and nonexistent ids on store and update', function (Closure $resolveTrainerId) {
        $admin = Admin::factory()->create();
        $validTrainer = Trainer::factory()->create();
        $training = Training::factory()->create(['trainer_id' => $validTrainer->id]);
        $invalidTrainerId = $resolveTrainerId();

        Sanctum::actingAs($admin);

        $this->postJson('/api/training', [
            'title' => 'Invalid Trainer Training',
            'code' => 'INV-001',
            'trainer_id' => $invalidTrainerId,
            'fee' => 250,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['trainer_id']);

        $this->putJson("/api/training/{$training->id}", [
            'trainer_id' => $invalidTrainerId,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['trainer_id']);

        expect(Training::count())->toBe(1)
            ->and($training->fresh()->trainer_id)->toBe($validTrainer->id);
    })->with([
        'plain user' => [fn (): string => User::factory()->create()->id],
        'trainee' => [fn (): string => Trainee::factory()->create()->id],
        'staff' => [fn (): string => Staff::factory()->create()->id],
        'admin' => [fn (): string => Admin::factory()->create()->id],
        'non-existent id' => [fn (): string => '01ARZ3NDEKTSV4RRFFQ69G5FAV'],
    ]);

    test('validates required fields, code uniqueness, and non-negative numeric fee', function () {
        $admin = Admin::factory()->create();
        $existing = Training::factory()->create(['code' => 'DUP-100']);
        $other = Training::factory()->create(['code' => 'OTH-200']);

        Sanctum::actingAs($admin);

        $this->postJson('/api/training', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'code', 'fee']);

        $this->postJson('/api/training', [
            'title' => 'Duplicate Code',
            'code' => 'DUP-100',
            'fee' => 100,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);

        $this->putJson("/api/training/{$other->id}", [
            'code' => 'DUP-100',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);

        $this->postJson('/api/training', [
            'title' => 'Negative Fee',
            'code' => 'NEG-100',
            'fee' => -1,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['fee']);

        expect($existing->fresh()->code)->toBe('DUP-100')
            ->and($other->fresh()->code)->toBe('OTH-200');
    });

    test('enforces authorization for trainee and trainers on training endpoints', function () {
        $trainer1 = Trainer::factory()->create();
        $trainer2 = Trainer::factory()->create();
        $trainee = Trainee::factory()->create();
        $training = Training::factory()->create(['trainer_id' => $trainer1->id]);

        Sanctum::actingAs($trainee);
        $this->getJson('/api/trainings')->assertOk();
        $this->getJson("/api/training/{$training->id}")->assertOk();
        $this->postJson('/api/training', [
            'title' => 'Blocked',
            'code' => 'BLK-1',
            'fee' => 100,
        ])->assertForbidden();
        $this->putJson("/api/training/{$training->id}", ['title' => 'Blocked'])->assertForbidden();
        $this->deleteJson("/api/training/{$training->id}")->assertForbidden();

        Sanctum::actingAs($trainer1);
        $this->postJson('/api/training', [
            'title' => 'Trainer Blocked',
            'code' => 'TRN-BLK',
            'fee' => 100,
        ])->assertForbidden();
        $this->putJson("/api/training/{$training->id}", ['title' => 'Trainer Updated'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Trainer Updated');
        $this->deleteJson("/api/training/{$training->id}")->assertForbidden();

        Sanctum::actingAs($trainer2);
        $this->putJson("/api/training/{$training->id}", ['title' => 'Other Trainer Tamper'])->assertForbidden();
    });
});

describe('nested training.quiz endpoints', function () {
    test('returns 401 when unauthenticated', function () {
        $training = Training::factory()->create();
        $quiz = Quiz::factory()->for($training)->create();

        $this->getJson("/api/training/{$training->id}/quizzes")->assertUnauthorized();
        $this->postJson("/api/training/{$training->id}/quiz", [])->assertUnauthorized();
        $this->getJson("/api/training/{$training->id}/quiz/{$quiz->id}")->assertUnauthorized();
        $this->putJson("/api/training/{$training->id}/quiz/{$quiz->id}", [])->assertUnauthorized();
        $this->deleteJson("/api/training/{$training->id}/quiz/{$quiz->id}")->assertUnauthorized();
    });

    test('admin can perform full nested crud and index only lists quizzes for the parent training', function () {
        $admin = Admin::factory()->create();
        $training = Training::factory()->create();
        $otherTraining = Training::factory()->create();
        $otherQuiz = Quiz::factory()->for($otherTraining)->create(['mark' => 80]);

        Sanctum::actingAs($admin);

        $storeResponse = $this->postJson("/api/training/{$training->id}/quiz", [
            'mark' => 100,
        ]);

        $storeResponse->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'training_id',
                    'mark',
                ],
            ])
            ->assertJsonPath('data.training_id', $training->id)
            ->assertJsonPath('data.mark', 100);

        $quizId = $storeResponse->json('data.id');
        $quiz = Quiz::find($quizId);

        $this->assertModelExists($quiz);

        $this->getJson("/api/training/{$training->id}/quizzes")
            ->assertOk()
            ->assertJsonFragment(['id' => $quizId])
            ->assertJsonMissing(['id' => $otherQuiz->id]);

        $this->getJson("/api/training/{$training->id}/quiz/{$quizId}")
            ->assertOk()
            ->assertJsonPath('data.id', $quizId)
            ->assertJsonPath('data.training_id', $training->id)
            ->assertJsonPath('data.mark', 100);

        $this->putJson("/api/training/{$training->id}/quiz/{$quizId}", [
            'mark' => 50,
        ])->assertOk()
            ->assertJsonPath('data.mark', 50);

        $this->assertDatabaseHas('quizzes', [
            'id' => $quizId,
            'training_id' => $training->id,
            'mark' => 50,
        ]);

        $this->deleteJson("/api/training/{$training->id}/quiz/{$quizId}")
            ->assertNoContent();

        $this->assertModelMissing($quiz);
    });

    test('scoped route binding returns 404 when quiz does not belong to training', function () {
        $admin = Admin::factory()->create();
        $trainingA = Training::factory()->create();
        $trainingB = Training::factory()->create();
        $quizOfB = Quiz::factory()->for($trainingB)->create(['mark' => 75]);

        Sanctum::actingAs($admin);

        $this->getJson("/api/training/{$trainingA->id}/quiz/{$quizOfB->id}")->assertNotFound();
        $this->putJson("/api/training/{$trainingA->id}/quiz/{$quizOfB->id}", ['mark' => 10])->assertNotFound();
        $this->deleteJson("/api/training/{$trainingA->id}/quiz/{$quizOfB->id}")->assertNotFound();

        $this->assertModelExists($quizOfB);
        expect($quizOfB->fresh()->mark)->toBe(75);
    });

    test('assigned trainer manages own training quizzes while other trainer and trainee are restricted', function () {
        $assignedTrainer = Trainer::factory()->create();
        $otherTrainer = Trainer::factory()->create();
        $trainee = Trainee::factory()->create();
        $training = Training::factory()->create(['trainer_id' => $assignedTrainer->id]);
        $existingQuiz = Quiz::factory()->for($training)->create(['mark' => 40]);

        // Assigned trainer can create, update, and delete quizzes on own training
        Sanctum::actingAs($assignedTrainer);
        $created = $this->postJson("/api/training/{$training->id}/quiz", ['mark' => 60]);
        $created->assertCreated()
            ->assertJsonPath('data.training_id', $training->id);
        $createdQuizId = $created->json('data.id');

        $this->putJson("/api/training/{$training->id}/quiz/{$createdQuizId}", ['mark' => 70])
            ->assertOk()
            ->assertJsonPath('data.mark', 70);

        $this->deleteJson("/api/training/{$training->id}/quiz/{$createdQuizId}")
            ->assertNoContent();

        // Other trainer can view but cannot create, update, or delete
        Sanctum::actingAs($otherTrainer);
        $this->getJson("/api/training/{$training->id}/quizzes")->assertOk();
        $this->getJson("/api/training/{$training->id}/quiz/{$existingQuiz->id}")->assertOk();
        $this->postJson("/api/training/{$training->id}/quiz", ['mark' => 90])->assertForbidden();
        $this->putJson("/api/training/{$training->id}/quiz/{$existingQuiz->id}", ['mark' => 90])->assertForbidden();
        $this->deleteJson("/api/training/{$training->id}/quiz/{$existingQuiz->id}")->assertForbidden();

        // Trainee can view but cannot create, update, or delete
        Sanctum::actingAs($trainee);
        $this->getJson("/api/training/{$training->id}/quizzes")->assertOk();
        $this->getJson("/api/training/{$training->id}/quiz/{$existingQuiz->id}")->assertOk();
        $this->postJson("/api/training/{$training->id}/quiz", ['mark' => 90])->assertForbidden();
        $this->putJson("/api/training/{$training->id}/quiz/{$existingQuiz->id}", ['mark' => 90])->assertForbidden();
        $this->deleteJson("/api/training/{$training->id}/quiz/{$existingQuiz->id}")->assertForbidden();

        expect($existingQuiz->fresh()->mark)->toBe(40);
    });

    test('validates quiz mark on store and update', function (array $payload) {
        $admin = Admin::factory()->create();
        $training = Training::factory()->create();
        $quiz = Quiz::factory()->for($training)->create(['mark' => 50]);

        Sanctum::actingAs($admin);

        $this->postJson("/api/training/{$training->id}/quiz", $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['mark']);

        if ($payload !== []) {
            $this->putJson("/api/training/{$training->id}/quiz/{$quiz->id}", $payload)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['mark']);
        }

        expect(Quiz::count())->toBe(1)
            ->and($quiz->fresh()->mark)->toBe(50);
    })->with([
        'missing mark' => [[]],
        'zero mark' => [['mark' => 0]],
        'negative mark' => [['mark' => -10]],
        'non-integer mark' => [['mark' => 'hundred']],
    ]);
});
