<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Trainee extends User
{
    use HasFactory, HasUlids;

    public function results()
    {
        return $this->hasMany(Result::class, 'trainee_id');
    }

    public function enrolledTrainings()
    {
        return $this->belongsToMany(Training::class, 'students', 'trainee_id', 'training_id')
            ->withPivot(['fee', 'fee_reason', 'registrar_id'])
            ->using(Student::class);
    }
}
