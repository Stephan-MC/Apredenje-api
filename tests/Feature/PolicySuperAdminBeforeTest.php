<?php

use App\Models\Admin;
use App\Models\Batch;
use App\Models\BatchType;
use App\Models\Internship;
use App\Models\Payment;
use App\Models\Quiz;
use App\Models\Session;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Trainee;
use App\Models\Trainer;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Silber\Bouncer\BouncerFacade;

beforeEach(function (): void {
    BouncerFacade::dontCache();
});

test('grants super-admin pre-authorization across all standard policies while falling through for non-super-admins', function (string $modelClass, Closure $createModel) {
    $superAdmin = User::factory()->create();
    $superAdmin->assign('super-admin');

    $plainUser = User::factory()->create();
    $instance = $createModel();

    expect($superAdmin->isA('super-admin'))->toBeTrue()
        ->and($superAdmin->isAn('admin'))->toBeFalse();

    $superAdminGate = Gate::forUser($superAdmin);
    $plainUserGate = Gate::forUser($plainUser);

    expect($superAdminGate->allows('viewAny', $modelClass))->toBeTrue()
        ->and($superAdminGate->allows('create', $modelClass))->toBeTrue()
        ->and($plainUserGate->allows('create', $modelClass))->toBeFalse();

    foreach (['view', 'update', 'delete', 'restore', 'forceDelete'] as $ability) {
        expect($superAdminGate->allows($ability, $instance))->toBeTrue();
    }

    foreach (['update', 'delete', 'restore', 'forceDelete'] as $restrictedAbility) {
        expect($plainUserGate->allows($restrictedAbility, $instance))->toBeFalse();
    }
})->with([
    'Admin' => [Admin::class, fn () => Admin::factory()->create()],
    'Batch' => [Batch::class, fn () => Batch::factory()->create()],
    'BatchType' => [BatchType::class, fn () => BatchType::factory()->create()],
    'Internship' => [Internship::class, fn () => Internship::factory()->create()],
    'Payment' => [Payment::class, fn () => Payment::factory()->create()],
    'Quiz' => [Quiz::class, fn () => Quiz::factory()->create()],
    'Session' => [Session::class, fn () => Session::factory()->create()],
    'Staff' => [Staff::class, fn () => Staff::factory()->create()],
    'Trainee' => [Trainee::class, fn () => Trainee::factory()->create()],
    'Trainer' => [Trainer::class, fn () => Trainer::factory()->create()],
    'Training' => [Training::class, fn () => Training::factory()->create()],
]);

test('preserves StudentPolicy payment deletion guard while granting super-admin pre-authorization for other abilities', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assign('super-admin');

    $studentWithoutPayments = Student::factory()->create();
    $studentWithPayments = Student::factory()->create();
    Payment::factory()->for($studentWithPayments)->create();

    $gate = Gate::forUser($superAdmin);

    expect($gate->allows('viewAny', Student::class))->toBeTrue()
        ->and($gate->allows('view', $studentWithPayments))->toBeTrue()
        ->and($gate->allows('create', Student::class))->toBeTrue()
        ->and($gate->allows('update', $studentWithPayments))->toBeTrue()
        ->and($gate->allows('restore', $studentWithPayments))->toBeTrue()
        ->and($gate->allows('delete', $studentWithoutPayments))->toBeTrue()
        ->and($gate->allows('forceDelete', $studentWithoutPayments))->toBeTrue();

    foreach (['delete', 'forceDelete'] as $ability) {
        $response = $gate->inspect($ability, $studentWithPayments);

        expect($response->denied())->toBeTrue()
            ->and($response->message())->toBe('Cannot delete a student who has payments')
            ->and($response->status())->toBe(409);
    }
});
