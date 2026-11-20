<?php

use App\Models\Admin;
use App\Models\Batch;
use App\Models\BatchType;
use App\Models\Internship;
use App\Models\Payment;
use App\Models\Quiz;
use App\Models\Result;
use App\Models\Session;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Trainee;
use App\Models\Trainer;
use App\Models\Training;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\BatchSeeder;
use Database\Seeders\BatchTypeSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\InternshipSeeder;
use Database\Seeders\PaymentSeeder;
use Database\Seeders\QuizSeeder;
use Database\Seeders\ResultSeeder;
use Database\Seeders\SessionSeeder;
use Database\Seeders\StaffSeeder;
use Database\Seeders\StudentSeeder;
use Database\Seeders\TraineeSeeder;
use Database\Seeders\TrainerSeeder;
use Database\Seeders\TrainingSeeder;
use Database\Seeders\UserSeeder;
use Silber\Bouncer\BouncerFacade;

beforeEach(function () {
    BouncerFacade::dontCache();
});

describe('model factories', function () {
    test('every model factory creates a persisted record with valid relations and roles', function () {
        $user = User::factory()->create();
        $staff = Staff::factory()->create();
        $admin = Admin::factory()->create();
        $superAdmin = Admin::factory()->superAdmin()->create();
        $trainer = Trainer::factory()->create();
        $trainee = Trainee::factory()->create();
        $internship = Internship::factory()->create();
        $batchType = BatchType::factory()->create();
        $training = Training::factory()->create();
        $trainingWithoutTrainer = Training::factory()->withoutTrainer()->create();
        $session = Session::factory()->create();
        $batch = Batch::factory()->create();
        $quiz = Quiz::factory()->create();
        $student = Student::factory()->create();
        $studentWithoutRegistrar = Student::factory()->withoutRegistrar()->create();
        $result = Result::factory()->create();
        $payment = Payment::factory()->create();
        $paymentWithoutRegistrar = Payment::factory()->withoutRegistrar()->create();

        foreach ([
            $user,
            $staff,
            $admin,
            $superAdmin,
            $trainer,
            $trainee,
            $internship,
            $batchType,
            $training,
            $trainingWithoutTrainer,
            $session,
            $batch,
            $quiz,
            $student,
            $studentWithoutRegistrar,
            $result,
            $payment,
            $paymentWithoutRegistrar,
        ] as $model) {
            $this->assertModelExists($model);
        }

        expect($staff->isA('staff'))->toBeTrue()
            ->and($admin->isAn('admin'))->toBeTrue()
            ->and($admin->isA('staff'))->toBeFalse()
            ->and($superAdmin->isAn('admin'))->toBeTrue()
            ->and($superAdmin->isA('super-admin'))->toBeTrue()
            ->and($trainer->isA('trainer'))->toBeTrue()
            ->and($trainee->isA('trainee'))->toBeTrue()
            ->and($internship->user)->toBeInstanceOf(User::class)
            ->and($training->trainer)->toBeInstanceOf(Trainer::class)
            ->and($trainingWithoutTrainer->trainer_id)->toBeNull()
            ->and($session->training)->toBeInstanceOf(Training::class)
            ->and($batch->batchType)->toBeInstanceOf(BatchType::class)
            ->and($batch->session)->toBeInstanceOf(Session::class)
            ->and($batch->trainer)->toBeInstanceOf(Trainer::class)
            ->and($quiz->training)->toBeInstanceOf(Training::class)
            ->and($student->trainee)->toBeInstanceOf(Trainee::class)
            ->and($student->training)->toBeInstanceOf(Training::class)
            ->and($student->registrar)->toBeInstanceOf(User::class)
            ->and($studentWithoutRegistrar->registrar_id)->toBeNull()
            ->and($result->trainee)->toBeInstanceOf(Trainee::class)
            ->and($result->quiz)->toBeInstanceOf(Quiz::class)
            ->and($payment->student)->toBeInstanceOf(Student::class)
            ->and($payment->registrar)->toBeInstanceOf(User::class)
            ->and($payment->amount)->not->toBeNull()
            ->and($paymentWithoutRegistrar->registrar_id)->toBeNull();
    });
});

describe('individual seeders', function () {
    test('seeds records independently on a fresh database', function (string $seederClass, string $modelClass) {
        $this->seed($seederClass);

        expect($modelClass::query()->count())->toBeGreaterThan(0);
    })->with([
        'UserSeeder' => [UserSeeder::class, User::class],
        'AdminSeeder' => [AdminSeeder::class, Admin::class],
        'StaffSeeder' => [StaffSeeder::class, Staff::class],
        'TrainerSeeder' => [TrainerSeeder::class, Trainer::class],
        'TraineeSeeder' => [TraineeSeeder::class, Trainee::class],
        'BatchTypeSeeder' => [BatchTypeSeeder::class, BatchType::class],
        'TrainingSeeder' => [TrainingSeeder::class, Training::class],
        'SessionSeeder' => [SessionSeeder::class, Session::class],
        'BatchSeeder' => [BatchSeeder::class, Batch::class],
        'QuizSeeder' => [QuizSeeder::class, Quiz::class],
        'StudentSeeder' => [StudentSeeder::class, Student::class],
        'ResultSeeder' => [ResultSeeder::class, Result::class],
        'PaymentSeeder' => [PaymentSeeder::class, Payment::class],
        'InternshipSeeder' => [InternshipSeeder::class, Internship::class],
    ]);
});

describe('database seeder', function () {
    test('seeds all models with coherent relationships and roles', function () {
        $this->seed(DatabaseSeeder::class);

        expect(User::query()->count())->toBeGreaterThan(0)
            ->and(Admin::query()->count())->toBe(3)
            ->and(Staff::query()->count())->toBe(3)
            ->and(Trainer::query()->count())->toBe(4)
            ->and(Trainee::query()->count())->toBe(10)
            ->and(BatchType::query()->count())->toBe(6)
            ->and(Training::query()->count())->toBe(5)
            ->and(Session::query()->count())->toBe(10)
            ->and(Batch::query()->count())->toBe(10)
            ->and(Quiz::query()->count())->toBe(10)
            ->and(Student::query()->count())->toBe(20)
            ->and(Result::query()->count())->toBe(40)
            ->and(Payment::query()->count())->toBe(20)
            ->and(Internship::query()->count())->toBe(User::query()->count());

        $superAdmin = Admin::query()->where('email', 'superadmin@apredenje.test')->first();
        $systemAdmin = Admin::query()->where('email', 'admin@apredenje.test')->first();
        $testUser = User::query()->where('email', 'test@example.com')->first();

        expect($superAdmin)->not->toBeNull()
            ->and($superAdmin->isAn('admin'))->toBeTrue()
            ->and($superAdmin->isA('super-admin'))->toBeTrue()
            ->and($systemAdmin)->not->toBeNull()
            ->and($systemAdmin->isAn('admin'))->toBeTrue()
            ->and($testUser)->not->toBeNull();

        $trainerIds = Trainer::query()->pluck('id')->all();

        Training::query()->get()->each(function (Training $training) use ($trainerIds): void {
            expect($training->trainer_id)->toBeIn($trainerIds);
        });

        Batch::query()->get()->each(function (Batch $batch) use ($trainerIds): void {
            expect($batch->trainer_id)->toBeIn($trainerIds);
        });

        Result::query()->with('quiz')->get()->each(function (Result $result): void {
            expect($result->mark)->toBeGreaterThanOrEqual(0)
                ->and($result->mark)->toBeLessThanOrEqual($result->quiz->mark);
        });

        Payment::query()->get()->each(function (Payment $payment): void {
            expect((float) $payment->amount)->toBeGreaterThan(0);
        });
    });
});
