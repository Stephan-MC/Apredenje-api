<?php

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Laravel\Sanctum\Sanctum;

describe('login', function () {
    test('authenticates a user with valid credentials and returns user resource', function () {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response = $this->from('http://localhost')->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', 'john@example.com')
            ->assertJsonPath('data.first_name', $user->first_name)
            ->assertJsonPath('data.last_name', $user->last_name);

        $this->assertAuthenticatedAs($user, 'web');
    });

    test('returns 422 when credentials do not match', function () {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email' => trans('auth.failed')]);

        $this->assertGuest('web');
    });

    test('returns 422 when required fields are missing', function () {
        $response = $this->postJson('/api/login', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    });

    test('throttles login attempts after 5 failures and returns 422', function () {
        $user = User::factory()->create([
            'email' => 'throttle@example.com',
            'password' => 'password123',
        ]);

        Event::fake([Lockout::class]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])->assertUnprocessable();
        }

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        Event::assertDispatched(Lockout::class);
    });
});

describe('logout', function () {
    test('logs out an authenticated user and returns 204', function () {
        $user = User::factory()->create([
            'email' => 'logout@example.com',
            'password' => 'password123',
        ]);

        $this->from('http://localhost')->postJson('/api/login', [
            'email' => 'logout@example.com',
            'password' => 'password123',
        ])->assertOk();

        $response = $this->from('http://localhost')->postJson('/api/logout');

        $response->assertNoContent();
        $this->assertGuest('web');
    });

    test('allows logout when authenticated via Sanctum actingAs', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertNoContent();
    });

    test('returns 401 when unauthenticated user attempts to logout', function () {
        $this->postJson('/api/logout')->assertUnauthorized();
    });
});

describe('forgot-password', function () {
    test('sends password reset link notification and returns 200', function () {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'reset@example.com',
        ]);

        $response = $this->postJson('/api/forgot-password', [
            'email' => 'reset@example.com',
        ]);

        $response->assertOk()
            ->assertExactJson([
                'status' => __(Password::RESET_LINK_SENT),
            ]);

        Notification::assertSentTo($user, ResetPassword::class);
    });

    test('returns 422 when user email does not exist', function () {
        Notification::fake();

        $response = $this->postJson('/api/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email' => __(Password::INVALID_USER),
            ]);

        Notification::assertNothingSent();
    });

    test('returns 422 when email is invalid', function () {
        $response = $this->postJson('/api/forgot-password', [
            'email' => 'not-an-email',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });
});

describe('reset-password', function () {
    test('resets user password with valid token and returns 200', function () {
        $user = User::factory()->create([
            'email' => 'newpass@example.com',
            'password' => 'old-password',
        ]);

        $token = Password::broker()->createToken($user);

        Event::fake([PasswordReset::class]);

        $response = $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => 'newpass@example.com',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

        $response->assertOk()
            ->assertExactJson([
                'status' => __(Password::PASSWORD_RESET),
            ]);

        expect(Hash::check('new-secure-password', $user->fresh()->password))->toBeTrue();

        Event::assertDispatched(PasswordReset::class, fn (PasswordReset $event) => $event->user->is($user));
    });

    test('returns 422 when password reset token is invalid', function () {
        $user = User::factory()->create([
            'email' => 'newpass@example.com',
            'password' => 'old-password',
        ]);

        $response = $this->postJson('/api/reset-password', [
            'token' => 'invalid-token',
            'email' => 'newpass@example.com',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email' => __(Password::INVALID_TOKEN),
            ]);

        expect(Hash::check('old-password', $user->fresh()->password))->toBeTrue();
    });

    test('returns 422 when password confirmation does not match', function () {
        $user = User::factory()->create([
            'email' => 'newpass@example.com',
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => 'newpass@example.com',
            'password' => 'new-secure-password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    });
});
