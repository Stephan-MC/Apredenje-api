<?php

namespace App\Models;

use App\Observers\StaffObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ObservedBy(StaffObserver::class)]
#[Table('users')]
class Staff extends User
{
    use HasFactory, HasUlids;

    public function newQuery(): Builder
    {
        return parent::newQuery()->whereIs('staff');
    }
}
