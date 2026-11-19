<?php

use App\Models\Admin;
use App\Models\Intern;
use App\Models\Payment;
use App\Models\Quiz;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Trainee;
use App\Models\Trainer;
use App\Models\Training;
use App\Models\User;
use App\Notifications\PaymentReceivedNotification;
use App\Notifications\WelcomeUserNotification;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Silber\Bouncer\BouncerFacade;

beforeEach(function () {
    BouncerFacade::dontCache();
});

test('submodels filter correctly by role via newQuery and assign role on creation', function () {
    $staff = Staff::create([
        'first_name' => 'Staff',
        'last_name' => 'User',
        'email' => 'staff@test.com',
        'password' => 'password123',
    ]);

    $admin = Admin::create([
        'first_name' => 'Admin',
        'last_name' => 'User',
        'email' => 'admin@test.com',
        'password' => 'password123',
    ]);

    $trainer = Trainer::create([
        'first_name' => 'Trainer',
        'last_name' => 'User',
        'email' => 'trainer@test.com',
        'password' => 'password123',
    ]);

    $trainee = Trainee::create([
        'first_name' => 'Trainee',
        'last_name' => 'User',
        'email' => 'trainee@test.com',
        'password' => 'password123',
    ]);

    $intern = Intern::create([
        'first_name' => 'Intern',
        'last_name' => 'User',
        'email' => 'intern@test.com',
        'password' => 'password123',
    ]);

    expect($admin)->toBeInstanceOf(Staff::class)
        ->and(Staff::count())->toBe(1)
        ->and(Admin::count())->toBe(1)
        ->and(Trainer::count())->toBe(1)
        ->and(Trainee::count())->toBe(1)
        ->and(Intern::count())->toBe(1)
        ->and(Staff::first()->id)->toBe($staff->id)
        ->and(Admin::first()->id)->toBe($admin->id)
        ->and(Trainer::first()->id)->toBe($trainer->id)
        ->and(Trainee::first()->id)->toBe($trainee->id)
        ->and(Intern::first()->id)->toBe($intern->id);
});

test('unauthenticated users cannot access API endpoints', function () {
    $this->getJson('/api/training')->assertUnauthorized();
    $this->getJson('/api/admin')->assertUnauthorized();
    $this->getJson('/api/staff')->assertUnauthorized();
    $this->getJson('/api/trainer')->assertUnauthorized();
    $this->getJson('/api/trainee')->assertUnauthorized();
    $this->getJson('/api/intern')->assertUnauthorized();
    $this->getJson('/api/batch')->assertUnauthorized();
    $this->getJson('/api/quiz')->assertUnauthorized();
    $this->getJson('/api/payment')->assertUnauthorized();
});

test('admin can perform full CRUD on staff while non-admins are forbidden', function () {
    $admin = Admin::create([
        'first_name' => 'Admin',
        'last_name' => 'Boss',
        'email' => 'admin-boss@test.com',
        'password' => 'password123',
    ]);

    $trainer = Trainer::create([
        'first_name' => 'Trainer',
        'last_name' => 'Guy',
        'email' => 'trainer-guy@test.com',
        'password' => 'password123',
    ]);

    // Non-admin trainer cannot view staff
    Sanctum::actingAs($trainer);
    $this->getJson('/api/staff')->assertForbidden();
    $this->postJson('/api/staff', [
        'first_name' => 'New',
        'last_name' => 'Staff',
        'email' => 'staff-guy@test.com',
        'password' => 'password123',
    ])->assertForbidden();

    // Admin can perform full CRUD on staff
    Sanctum::actingAs($admin);

    $postRes = $this->postJson('/api/staff', [
        'first_name' => 'Office',
        'last_name' => 'Staff',
        'email' => 'office-staff@test.com',
        'password' => 'password123',
    ]);
    $postRes->assertCreated();
    $staffId = $postRes->json('data.id');

    $this->getJson('/api/staff')
        ->assertOk()
        ->assertJsonFragment(['email' => 'office-staff@test.com']);

    $this->getJson("/api/staff/{$staffId}")
        ->assertOk()
        ->assertJsonPath('data.first_name', 'Office');

    $this->putJson("/api/staff/{$staffId}", [
        'first_name' => 'Senior Office',
    ])->assertOk()
        ->assertJsonPath('data.first_name', 'Senior Office');

    $this->deleteJson("/api/staff/{$staffId}")
        ->assertNoContent();

    expect(Staff::find($staffId))->toBeNull();
});

test('admin can perform full CRUD on training', function () {
    $admin = Admin::create([
        'first_name' => 'Admin',
        'last_name' => 'Boss',
        'email' => 'admin-boss@test.com',
        'password' => 'password123',
    ]);

    Sanctum::actingAs($admin);

    $postResponse = $this->postJson('/api/training', [
        'title' => 'Web Security Masterclass',
        'code' => 'SEC-101',
        'fee' => 500,
    ]);
    $postResponse->assertCreated()
        ->assertJsonPath('data.title', 'Web Security Masterclass');

    $trainingId = $postResponse->json('data.id');

    $this->getJson('/api/training')
        ->assertOk()
        ->assertJsonFragment(['title' => 'Web Security Masterclass']);

    $this->getJson("/api/training/{$trainingId}")
        ->assertOk()
        ->assertJsonPath('data.id', $trainingId);

    $this->putJson("/api/training/{$trainingId}", [
        'title' => 'Advanced Web Security',
    ])->assertOk()
        ->assertJsonPath('data.title', 'Advanced Web Security');

    $this->deleteJson("/api/training/{$trainingId}")
        ->assertNoContent();

    expect(Training::find($trainingId))->toBeNull();
});

test('non-admin trainee cannot create training', function () {
    $trainee = Trainee::create([
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'trainee-jane@test.com',
        'password' => 'password123',
    ]);

    Sanctum::actingAs($trainee);

    $this->postJson('/api/training', [
        'title' => 'Unauthorized Course',
        'code' => 'UNAUTH-1',
        'fee' => 100,
    ])->assertForbidden();
});

test('trainer can update assigned training but not training belonging to another trainer', function () {
    $trainer1 = Trainer::create([
        'first_name' => 'Trainer',
        'last_name' => 'One',
        'email' => 'trainer1@test.com',
        'password' => 'password123',
    ]);

    $trainer2 = Trainer::create([
        'first_name' => 'Trainer',
        'last_name' => 'Two',
        'email' => 'trainer2@test.com',
        'password' => 'password123',
    ]);

    $training = Training::create([
        'title' => 'Trainer 1 Training',
        'code' => 'TR1',
        'fee' => 200,
        'trainer_id' => $trainer1->id,
    ]);

    Sanctum::actingAs($trainer1);
    $this->putJson("/api/training/{$training->id}", [
        'title' => 'Trainer 1 Updated',
    ])->assertOk();

    Sanctum::actingAs($trainer2);
    $this->putJson("/api/training/{$training->id}", [
        'title' => 'Trainer 2 Tampering',
    ])->assertForbidden();
});

test('admin can manage user models and observers assign appropriate role', function () {
    $superAdmin = Admin::factory()->superAdmin()->create([
        'first_name' => 'Super',
        'last_name' => 'Admin',
        'email' => 'super@test.com',
        'password' => 'password123',
    ]);
    Sanctum::actingAs($superAdmin);

    $adminRes = $this->postJson('/api/admin', [
        'first_name' => 'New',
        'last_name' => 'Admin',
        'email' => 'newadmin@test.com',
        'password' => 'secret1234',
    ]);
    $adminRes->assertCreated();
    expect(Admin::where('email', 'newadmin@test.com')->first()->isAn('admin'))->toBeTrue();

    $trainerRes = $this->postJson('/api/trainer', [
        'first_name' => 'New',
        'last_name' => 'Trainer',
        'email' => 'newtrainer@test.com',
        'password' => 'secret1234',
    ]);
    $trainerRes->assertCreated();
    expect(Trainer::where('email', 'newtrainer@test.com')->first()->isA('trainer'))->toBeTrue();

    $traineeRes = $this->postJson('/api/trainee', [
        'first_name' => 'New',
        'last_name' => 'Trainee',
        'email' => 'newtrainee@test.com',
        'password' => 'secret1234',
    ]);
    $traineeRes->assertCreated();
    expect(Trainee::where('email', 'newtrainee@test.com')->first()->isA('trainee'))->toBeTrue();

    $internRes = $this->postJson('/api/intern', [
        'first_name' => 'New',
        'last_name' => 'Intern',
        'email' => 'newintern@test.com',
        'password' => 'secret1234',
    ]);
    $internRes->assertCreated();
    expect(Intern::where('email', 'newintern@test.com')->first()->isAn('intern'))->toBeTrue();
});

test('quizzes and payments security policies work as expected', function () {
    $admin = Admin::create([
        'first_name' => 'Admin',
        'last_name' => 'Root',
        'email' => 'root@test.com',
        'password' => 'password123',
    ]);

    $trainer = Trainer::create([
        'first_name' => 'Trainer',
        'last_name' => 'Bob',
        'email' => 'bob@test.com',
        'password' => 'password123',
    ]);

    $trainee = Trainee::create([
        'first_name' => 'Trainee',
        'last_name' => 'Alice',
        'email' => 'alice@test.com',
        'password' => 'password123',
    ]);

    $training = Training::create([
        'title' => 'Database Design',
        'code' => 'DB-201',
        'fee' => 350,
        'trainer_id' => $trainer->id,
    ]);

    $student = Student::create([
        'trainee_id' => $trainee->id,
        'training_id' => $training->id,
        'registrar_id' => $admin->id,
        'fee' => 350,
    ]);

    // Admin creates payment
    Sanctum::actingAs($admin);
    $paymentRes = $this->postJson('/api/payment', [
        'training_id' => $training->id,
        'student_id' => $student->id,
    ]);
    $paymentRes->assertCreated();
    $paymentId = $paymentRes->json('data.id');

    // Trainee cannot view all payments
    Sanctum::actingAs($trainee);
    $this->getJson('/api/payment')->assertForbidden();

    // Trainee can view own payment
    $this->getJson("/api/payment/{$paymentId}")->assertOk();

    // Trainee cannot create quiz
    $this->postJson('/api/quiz', [
        'training_id' => $training->id,
        'mark' => 100,
    ])->assertForbidden();

    // Trainer can create quiz
    Sanctum::actingAs($trainer);
    $quizRes = $this->postJson('/api/quiz', [
        'training_id' => $training->id,
        'mark' => 100,
    ]);
    $quizRes->assertCreated();
});

test('admin authenticated via api login retains bouncer roles across protected endpoints', function () {
    $admin = Admin::factory()->create([
        'email' => 'login-admin@test.com',
        'password' => 'password123',
    ]);

    $this->from('http://localhost')->postJson('/api/login', [
        'email' => 'login-admin@test.com',
        'password' => 'password123',
    ])->assertOk();

    expect(Auth::guard('web')->user())
        ->toBeInstanceOf(User::class)
        ->and(Auth::guard('web')->user()->id)->toBe($admin->id)
        ->and(Auth::guard('web')->user()->isAn('admin'))->toBeTrue();

    $this->app->make('auth')->forgetGuards();

    $this->from('http://localhost')->getJson('/api/staff')->assertOk();

    $this->from('http://localhost')->postJson('/api/staff', [
        'first_name' => 'Logged',
        'last_name' => 'Staff',
        'email' => 'logged-staff@test.com',
        'password' => 'password123',
    ])->assertCreated();
});

test('trainer authenticated via api login retains bouncer role for assigned training', function () {
    $trainer = Trainer::factory()->create([
        'email' => 'login-trainer@test.com',
        'password' => 'password123',
    ]);

    $training = Training::create([
        'title' => 'Auth Roles Course',
        'code' => 'ROLE-101',
        'fee' => 150,
        'trainer_id' => $trainer->id,
    ]);

    $this->from('http://localhost')->postJson('/api/login', [
        'email' => 'login-trainer@test.com',
        'password' => 'password123',
    ])->assertOk();

    $this->app->make('auth')->forgetGuards();

    $this->from('http://localhost')->putJson("/api/training/{$training->id}", [
        'title' => 'Updated Via Login',
    ])->assertOk()
        ->assertJsonPath('data.title', 'Updated Via Login');

    $this->from('http://localhost')->getJson('/api/staff')->assertForbidden();
});

test('regular admin can only view and update themselves while super-admin manages all admins', function () {
    $admin1 = Admin::factory()->create(['first_name' => 'AdminOne']);
    $admin2 = Admin::factory()->create(['first_name' => 'AdminTwo']);
    $superAdmin = Admin::factory()->superAdmin()->create();

    Sanctum::actingAs($admin1);

    $this->getJson('/api/admin')->assertForbidden();
    $this->postJson('/api/admin', [
        'first_name' => 'Blocked',
        'last_name' => 'Admin',
        'email' => 'blocked-admin@test.com',
        'password' => 'secret1234',
    ])->assertForbidden();
    $this->getJson("/api/admin/{$admin2->id}")->assertForbidden();
    $this->putJson("/api/admin/{$admin2->id}", ['first_name' => 'Tampered'])->assertForbidden();
    $this->deleteJson("/api/admin/{$admin2->id}")->assertForbidden();

    $this->getJson("/api/admin/{$admin1->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $admin1->id);

    $this->putJson("/api/admin/{$admin1->id}", ['first_name' => 'SelfUpdated'])
        ->assertOk()
        ->assertJsonPath('data.first_name', 'SelfUpdated');

    Sanctum::actingAs($superAdmin);

    $this->getJson('/api/admin')->assertOk();
    $this->getJson("/api/admin/{$admin2->id}")->assertOk();
    $this->putJson("/api/admin/{$admin2->id}", ['first_name' => 'SuperUpdated'])
        ->assertOk()
        ->assertJsonPath('data.first_name', 'SuperUpdated');
    $this->deleteJson("/api/admin/{$admin2->id}")->assertNoContent();

    expect(Admin::find($admin2->id))->toBeNull();
});

test('create super admin command creates a new super-admin and promotes an existing user', function () {
    $this->artisan('app:create-super-admin', [
        '--first-name' => 'Cli',
        '--last-name' => 'SuperAdmin',
        '--email' => 'cli-super@test.com',
        '--password' => 'secret1234',
    ])->assertSuccessful();

    $created = Admin::where('email', 'cli-super@test.com')->first();

    expect($created)->not->toBeNull()
        ->and($created->isAn('admin'))->toBeTrue()
        ->and($created->isA('super-admin'))->toBeTrue();

    $existingAdmin = Admin::factory()->create(['email' => 'promote-me@test.com']);

    $this->artisan('app:create-super-admin', [
        '--email' => 'promote-me@test.com',
    ])->assertSuccessful();

    expect($existingAdmin->fresh()->isA('super-admin'))->toBeTrue();
});

test('welcome and payment notifications are queued instead of sent synchronously during requests', function () {
    $admin = Admin::factory()->create();
    $trainer = Trainer::factory()->create();
    $trainee = Trainee::factory()->create();
    $training = Training::create([
        'title' => 'Queued Mails',
        'code' => 'QUEUE-101',
        'fee' => 250,
        'trainer_id' => $trainer->id,
    ]);
    $student = Student::create([
        'trainee_id' => $trainee->id,
        'training_id' => $training->id,
        'registrar_id' => $admin->id,
        'fee' => 250,
    ]);

    Queue::fake([SendQueuedNotifications::class]);
    Mail::fake();
    Sanctum::actingAs($admin);

    $this->postJson('/api/staff', [
        'first_name' => 'Queued',
        'last_name' => 'Staff',
        'email' => 'queued-staff@test.com',
        'password' => 'password123',
    ])->assertCreated();

    $this->postJson('/api/payment', [
        'student_id' => $student->id,
    ])->assertCreated();

    Queue::assertPushed(SendQueuedNotifications::class, fn (SendQueuedNotifications $job): bool => $job->notification instanceof WelcomeUserNotification
        && $job->afterCommit === true);

    Queue::assertPushed(SendQueuedNotifications::class, fn (SendQueuedNotifications $job): bool => $job->notification instanceof PaymentReceivedNotification
        && $job->afterCommit === true);

    Mail::assertNothingSent();
});

test('deleting a staff member preserves student registrations and linked payments', function () {
    $admin = Admin::factory()->create();
    $staff = Staff::factory()->create();
    $trainer = Trainer::factory()->create();
    $trainee = Trainee::factory()->create();
    $training = Training::create([
        'title' => 'Data Integrity',
        'code' => 'INT-101',
        'fee' => 400,
        'trainer_id' => $trainer->id,
    ]);

    $student = Student::create([
        'trainee_id' => $trainee->id,
        'training_id' => $training->id,
        'registrar_id' => $staff->id,
        'fee' => 400,
    ]);

    $payment = Payment::create([
        'student_id' => $student->id,
        'registrar_id' => $staff->id,
    ]);

    Sanctum::actingAs($admin);

    $this->deleteJson("/api/staff/{$staff->id}")
        ->assertNoContent();

    expect(Staff::find($staff->id))->toBeNull()
        ->and($student->fresh())->not->toBeNull()
        ->and($student->fresh()->registrar_id)->toBeNull()
        ->and($payment->fresh())->not->toBeNull()
        ->and($payment->fresh()->student_id)->toBe($student->id)
        ->and($payment->fresh()->registrar_id)->toBeNull();
});
