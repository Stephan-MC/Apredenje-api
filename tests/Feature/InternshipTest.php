<?php

use App\Http\Resources\InternshipResource;
use App\Models\Admin;
use App\Models\Internship;
use App\Models\Trainee;
use App\Models\Trainer;
use App\Models\User;
use App\Observers\InternshipObserver;
use Carbon\CarbonInterface;
use Database\Seeders\InternshipSeeder;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Silber\Bouncer\BouncerFacade;

beforeEach(function () {
    BouncerFacade::dontCache();
});

describe('internship model, relationship, factory, and seeder', function () {
    test('a user has many internships and each internship belongs to its user', function () {
        $user = User::factory()->create();

        $first = Internship::factory()->for($user)->create([
            'start_time' => '2026-12-01 09:00:00',
            'duration' => 60,
        ]);
        $second = Internship::factory()->for($user)->create([
            'start_time' => '2027-02-01 09:00:00',
            'duration' => 90,
        ]);

        expect($user->internships)->toHaveCount(2)
            ->and($user->internships->pluck('id')->all())->toEqualCanonicalizing([$first->id, $second->id])
            ->and($first->user->is($user))->toBeTrue()
            ->and($second->user->is($user))->toBeTrue();
    });

    test('user subclasses resolve internships through user_id foreign key', function () {
        $trainee = Trainee::factory()->create();
        $internship = Internship::factory()->for($trainee, 'user')->create();

        expect($trainee->internships)->toHaveCount(1)
            ->and($trainee->internships->first()->is($internship))->toBeTrue();
    });

    test('casts start_time to CarbonInterface and duration to integer', function () {
        $internship = Internship::factory()->create([
            'start_time' => '2026-12-15 08:30:00',
            'duration' => '120',
        ]);

        $fresh = $internship->fresh();

        expect($fresh->start_time)->toBeInstanceOf(CarbonInterface::class)
            ->and($fresh->start_time->toDateTimeString())->toBe('2026-12-15 08:30:00')
            ->and($fresh->duration)->toBeInt()
            ->and($fresh->duration)->toBe(120);
    });

    test('deleting a user cascades and deletes their internships', function () {
        $user = User::factory()->create();
        $internship = Internship::factory()->for($user)->create();

        $user->delete();

        $this->assertModelMissing($internship);
    });

    test('observer is registered and fires lifecycle events for internship', function () {
        $events = [];

        Event::listen('eloquent.created: '.Internship::class, function () use (&$events): void {
            $events[] = 'created';
        });
        Event::listen('eloquent.updated: '.Internship::class, function () use (&$events): void {
            $events[] = 'updated';
        });
        Event::listen('eloquent.deleted: '.Internship::class, function () use (&$events): void {
            $events[] = 'deleted';
        });

        $reflection = new ReflectionClass(Internship::class);
        $attributes = $reflection->getAttributes();
        $observedBy = collect($attributes)->first(fn (ReflectionAttribute $attr): bool => $attr->getArguments() === [InternshipObserver::class]);

        $internship = Internship::factory()->create();
        $internship->update(['duration' => 45]);
        $internship->delete();

        expect($observedBy)->not->toBeNull()
            ->and($events)->toBe(['created', 'updated', 'deleted']);
    });

    test('factory creates valid internship records and seeder populates for empty and existing users', function () {
        $this->seed(InternshipSeeder::class);

        expect(Internship::count())->toBe(5)
            ->and(User::count())->toBe(5);

        Internship::query()->delete();
        $extraUser = User::factory()->create();

        $this->seed(InternshipSeeder::class);

        expect(Internship::count())->toBe(User::count())
            ->and($extraUser->internships()->count())->toBe(1);
    });

    test('resource includes loaded user when eager loaded and omits it otherwise', function () {
        $internship = Internship::factory()->create();

        $withoutUser = (new InternshipResource($internship))->toResponse(request())->getData(true)['data'];
        $withUser = (new InternshipResource($internship->load('user')))->toResponse(request())->getData(true)['data'];

        expect($withoutUser)->toHaveKeys(['id', 'user_id', 'start_time', 'duration', 'created_at', 'updated_at'])
            ->and($withoutUser)->not->toHaveKey('user')
            ->and($withUser['user']['id'])->toBe($internship->user_id);
    });

    test('legacy intern api endpoint returns 404', function () {
        $admin = Admin::factory()->create();
        Sanctum::actingAs($admin);

        $this->getJson('/api/intern')->assertNotFound();
    });
});

describe('internship api endpoints and authorization', function () {
    test('returns 401 when unauthenticated', function () {
        $internship = Internship::factory()->create();

        $this->getJson('/api/internships')->assertUnauthorized();
        $this->postJson('/api/internship', [])->assertUnauthorized();
        $this->getJson("/api/internship/{$internship->id}")->assertUnauthorized();
        $this->putJson("/api/internship/{$internship->id}", [])->assertUnauthorized();
        $this->deleteJson("/api/internship/{$internship->id}")->assertUnauthorized();
    });

    test('admin can perform full crud on internships', function () {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        Sanctum::actingAs($admin);

        $storeResponse = $this->postJson('/api/internship', [
            'user_id' => $user->id,
            'start_time' => '2026-12-01 10:00:00',
            'duration' => 90,
        ]);

        $storeResponse->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'start_time',
                    'duration',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.duration', 90);

        $internshipId = $storeResponse->json('data.id');
        $internship = Internship::find($internshipId);

        $this->assertModelExists($internship);
        $this->assertDatabaseHas('internships', [
            'id' => $internshipId,
            'user_id' => $user->id,
            'duration' => 90,
        ]);

        $this->getJson('/api/internships')
            ->assertOk()
            ->assertJsonFragment(['id' => $internshipId]);

        $this->getJson("/api/internship/{$internshipId}")
            ->assertOk()
            ->assertJsonPath('data.id', $internshipId)
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.duration', 90);

        $this->putJson("/api/internship/{$internshipId}", [
            'duration' => 180,
            'start_time' => '2027-01-15 08:00:00',
        ])->assertOk()
            ->assertJsonPath('data.duration', 180);

        $this->assertDatabaseHas('internships', [
            'id' => $internshipId,
            'duration' => 180,
        ]);

        $this->deleteJson("/api/internship/{$internshipId}")
            ->assertNoContent();

        $this->assertModelMissing($internship);
    });

    test('trainer can view internships but receives 403 on create, update, and delete', function () {
        $trainer = Trainer::factory()->create();
        $internship = Internship::factory()->create(['duration' => 60]);

        Sanctum::actingAs($trainer);

        $this->getJson('/api/internships')->assertOk();
        $this->getJson("/api/internship/{$internship->id}")->assertOk();

        $this->postJson('/api/internship', [
            'user_id' => $trainer->id,
            'start_time' => '2026-12-01 10:00:00',
            'duration' => 60,
        ])->assertForbidden();

        $this->putJson("/api/internship/{$internship->id}", [
            'duration' => 120,
        ])->assertForbidden();

        $this->deleteJson("/api/internship/{$internship->id}")->assertForbidden();

        expect($internship->fresh()->duration)->toBe(60);
    });

    test('regular user or trainee can only view their own internship and is forbidden from listing or mutating', function () {
        $trainee = Trainee::factory()->create();
        $otherUser = User::factory()->create();

        $ownInternship = Internship::factory()->for($trainee, 'user')->create(['duration' => 45]);
        $otherInternship = Internship::factory()->for($otherUser)->create(['duration' => 60]);

        Sanctum::actingAs($trainee);

        $this->getJson('/api/internships')->assertForbidden();
        $this->getJson("/api/internship/{$ownInternship->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $ownInternship->id);
        $this->getJson("/api/internship/{$otherInternship->id}")->assertForbidden();

        $this->postJson('/api/internship', [
            'user_id' => $trainee->id,
            'start_time' => '2026-12-01 10:00:00',
            'duration' => 30,
        ])->assertForbidden();

        $this->putJson("/api/internship/{$ownInternship->id}", [
            'duration' => 100,
        ])->assertForbidden();

        $this->deleteJson("/api/internship/{$ownInternship->id}")->assertForbidden();

        expect($ownInternship->fresh()->duration)->toBe(45);
    });

    test('policy grants restore and forceDelete only to admin', function () {
        $admin = Admin::factory()->create();
        $trainer = Trainer::factory()->create();
        $user = User::factory()->create();
        $internship = Internship::factory()->for($user)->create();

        expect($admin->can('restore', $internship))->toBeTrue()
            ->and($admin->can('forceDelete', $internship))->toBeTrue()
            ->and($trainer->can('restore', $internship))->toBeFalse()
            ->and($trainer->can('forceDelete', $internship))->toBeFalse()
            ->and($user->can('restore', $internship))->toBeFalse()
            ->and($user->can('forceDelete', $internship))->toBeFalse();
    });
});

describe('internship validation', function () {
    test('returns 422 when required fields are missing on store', function () {
        $admin = Admin::factory()->create();
        Sanctum::actingAs($admin);

        $this->postJson('/api/internship', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['user_id', 'start_time', 'duration']);

        expect(Internship::count())->toBe(0);
    });

    test('returns 422 for invalid payload values on store and update', function (array $payload, string $errorField) {
        $admin = Admin::factory()->create();
        $internship = Internship::factory()->create([
            'start_time' => '2026-12-10 09:00:00',
            'duration' => 60,
        ]);

        Sanctum::actingAs($admin);

        $this->postJson('/api/internship', array_merge([
            'user_id' => $internship->user_id,
            'start_time' => '2026-12-15 09:00:00',
            'duration' => 30,
        ], $payload))
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$errorField]);

        $this->putJson("/api/internship/{$internship->id}", $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$errorField]);

        expect(Internship::count())->toBe(1)
            ->and($internship->fresh()->duration)->toBe(60);
    })->with([
        'non-existent user_id' => [['user_id' => '01ARZ3NDEKTSV4RRFFQ69G5FAV'], 'user_id'],
        'invalid start_time' => [['start_time' => 'not-a-valid-date'], 'start_time'],
        'zero duration' => [['duration' => 0], 'duration'],
        'negative duration' => [['duration' => -15], 'duration'],
        'non-integer duration' => [['duration' => 'thirty'], 'duration'],
    ]);
});
