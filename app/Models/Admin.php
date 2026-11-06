<?php

namespace App\Models;

use App\Observers\AdminObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(AdminObserver::class)]
#[Table('users')]
class Admin extends User
{
    use HasFactory, HasUlids;

    public function newQuery(): Builder
    {
        return parent::newQuery()->whereIs('admin');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Student::class, 'registrar_id');
    }
}
