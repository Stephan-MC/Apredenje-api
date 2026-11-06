<?php

namespace App\Models;

use App\Observers\InternObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(InternObserver::class)]
#[Table('users')]
class Intern extends User
{
    use HasFactory, HasUlids;

    public function newQuery(): Builder
    {
        return parent::newQuery()->whereIs('intern');
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class, 'trainee_id');
    }

    public function enrolledTrainings(): BelongsToMany
    {
        return $this->belongsToMany(Training::class, 'students', 'trainee_id', 'training_id')
            ->withPivot(['fee', 'fee_reason', 'registrar_id'])
            ->using(Student::class);
    }
}
