<?php

use App\Models\Admin;
use App\Models\Payment;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Trainee;
use App\Models\Trainer;
use App\Models\User;
use App\Policies\StudentPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\Sanctum;
use Silber\Bouncer\BouncerFacade;

beforeEach(function (): void {
    BouncerFacade::dontCache();
});

describe('student policy registration and permissions', function () {
    test('registers StudentPolicy for the Student model via #[UsePolicy]', function () {
        expect(Gate::getPolicyFor(Student::class))->toBeInstanceOf(StudentPolicy::class);
    });

    test('enforces viewAny, view, create, update, and restore permissions by role', function () {
        $superAdmin = Admin::factory()->superAdmin()->create();
        $admin = Admin::factory()->create();
        $trainer = Trainer::factory()->create();
        $staff = Staff::factory()->create();
        $ownerTrainee = Trainee::factory()->create();
        $otherTrainee = Trainee::factory()->create();
        $student = Student::factory()->for($ownerTrainee, 'trainee')->create();

        foreach ([$superAdmin, $admin] as $privilegedUser) {
            expect($privilegedUser->can('viewAny', Student::class))->toBeTrue()
                ->and($privilegedUser->can('view', $student))->toBeTrue()
                ->and($privilegedUser->can('create', Student::class))->toBeTrue()
                ->and($privilegedUser->can('update', $student))->toBeTrue()
                ->and($privilegedUser->can('restore', $student))->toBeTrue();
        }

        expect($trainer->can('viewAny', Student::class))->toBeTrue()
            ->and($trainer->can('view', $student))->toBeTrue()
            ->and($trainer->can('create', Student::class))->toBeFalse()
            ->and($trainer->can('update', $student))->toBeFalse()
            ->and($trainer->can('restore', $student))->toBeFalse();

        expect($ownerTrainee->can('viewAny', Student::class))->toBeFalse()
            ->and($ownerTrainee->can('view', $student))->toBeTrue()
            ->and($ownerTrainee->can('create', Student::class))->toBeFalse()
            ->and($ownerTrainee->can('update', $student))->toBeFalse()
            ->and($ownerTrainee->can('restore', $student))->toBeFalse();

        expect($otherTrainee->can('viewAny', Student::class))->toBeFalse()
            ->and($otherTrainee->can('view', $student))->toBeFalse()
            ->and($otherTrainee->can('create', Student::class))->toBeFalse()
            ->and($otherTrainee->can('update', $student))->toBeFalse()
            ->and($otherTrainee->can('restore', $student))->toBeFalse();

        expect($staff->can('viewAny', Student::class))->toBeFalse()
            ->and($staff->can('view', $student))->toBeFalse()
            ->and($staff->can('create', Student::class))->toBeFalse()
            ->and($staff->can('update', $student))->toBeFalse()
            ->and($staff->can('restore', $student))->toBeFalse();
    });

    test('allows admin and super-admin to delete and forceDelete a student without payments and denies other roles', function () {
        $superAdmin = Admin::factory()->superAdmin()->create();
        $admin = Admin::factory()->create();
        $trainer = Trainer::factory()->create();
        $staff = Staff::factory()->create();
        $trainee = Trainee::factory()->create();
        $student = Student::factory()->for($trainee, 'trainee')->create();

        foreach ([$superAdmin, $admin] as $privilegedUser) {
            expect(Gate::forUser($privilegedUser)->inspect('delete', $student)->allowed())->toBeTrue()
                ->and(Gate::forUser($privilegedUser)->inspect('forceDelete', $student)->allowed())->toBeTrue();
        }

        foreach ([$trainer, $trainee, $staff] as $unprivilegedUser) {
            expect(Gate::forUser($unprivilegedUser)->inspect('delete', $student)->denied())->toBeTrue()
                ->and(Gate::forUser($unprivilegedUser)->inspect('forceDelete', $student)->denied())->toBeTrue();
        }
    });

    test('denies delete and forceDelete with 409 for admin and super-admin when student has payments', function () {
        $superAdmin = Admin::factory()->superAdmin()->create();
        $onlySuperAdmin = User::factory()->create();
        $onlySuperAdmin->assign('super-admin');
        $admin = Admin::factory()->create();
        $trainer = Trainer::factory()->create();
        $staff = Staff::factory()->create();
        $trainee = Trainee::factory()->create();

        $student = Student::factory()->for($trainee, 'trainee')->create();
        Payment::factory()->for($student)->create();

        $freshStudent = Student::findOrFail($student->id);
        $eagerLoadedStudent = Student::query()->withExists('payments')->findOrFail($student->id);

        foreach ([$freshStudent, $eagerLoadedStudent] as $target) {
            foreach ([$superAdmin, $onlySuperAdmin, $admin] as $privilegedUser) {
                $deleteResponse = Gate::forUser($privilegedUser)->inspect('delete', $target);
                $forceDeleteResponse = Gate::forUser($privilegedUser)->inspect('forceDelete', $target);

                expect($deleteResponse->denied())->toBeTrue()
                    ->and($deleteResponse->message())->toBe('Cannot delete a student who has payments')
                    ->and($deleteResponse->status())->toBe(409);

                expect($forceDeleteResponse->denied())->toBeTrue()
                    ->and($forceDeleteResponse->message())->toBe('Cannot delete a student who has payments')
                    ->and($forceDeleteResponse->status())->toBe(409);
            }

            foreach ([$trainer, $trainee, $staff] as $unprivilegedUser) {
                $deleteResponse = Gate::forUser($unprivilegedUser)->inspect('delete', $target);
                $forceDeleteResponse = Gate::forUser($unprivilegedUser)->inspect('forceDelete', $target);

                expect($deleteResponse->denied())->toBeTrue()
                    ->and($deleteResponse->status())->toBeNull();

                expect($forceDeleteResponse->denied())->toBeTrue()
                    ->and($forceDeleteResponse->status())->toBeNull();
            }
        }
    });
});

describe('student observer policy enforcement during authenticated deletes', function () {
    test('denies non-admin user deleting a student without payments through StudentPolicy', function () {
        $trainee = Trainee::factory()->create();
        $student = Student::factory()->for($trainee, 'trainee')->create();

        Sanctum::actingAs($trainee);

        expect(fn () => $student->delete())
            ->toThrow(AuthorizationException::class);

        $this->assertModelExists($student);
    });

    test('denies admin and super-admin deleting a student with payments through StudentObserver and StudentPolicy with 409', function (Closure $createActor) {
        $actor = $createActor();
        $payment = Payment::factory()->create();
        $student = $payment->student;

        Sanctum::actingAs($actor);

        $exception = null;
        try {
            $student->delete();
        } catch (AuthorizationException $e) {
            $exception = $e;
        }

        expect($exception)->toBeInstanceOf(AuthorizationException::class)
            ->and($exception->getMessage())->toBe('Cannot delete a student who has payments')
            ->and($exception->status())->toBe(409);

        $this->assertModelExists($student);
        $this->assertModelExists($payment);
    })->with([
        'admin' => [fn () => Admin::factory()->create()],
        'super-admin' => [fn () => Admin::factory()->superAdmin()->create()],
    ]);

    test('allows admin and super-admin to delete a student without payments', function (Closure $createActor) {
        $actor = $createActor();
        $student = Student::factory()->create();

        Sanctum::actingAs($actor);

        $student->delete();

        $this->assertModelMissing($student);
    })->with([
        'admin' => [fn () => Admin::factory()->create()],
        'super-admin' => [fn () => Admin::factory()->superAdmin()->create()],
    ]);
});
