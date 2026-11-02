<?php

namespace App\Models;

use App\Observers\AdminObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(AdminObserver::class)]
class Admin extends Model
{
    // Inherits User[cite: 1]

    public function registrations()
    {
        return $this->hasMany(Student::class, 'registrar_id');
    }
}
