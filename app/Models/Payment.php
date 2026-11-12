<?php

namespace App\Models;

use App\Observers\PaymentObserver;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

#[ObservedBy(PaymentObserver::class)]
#[Fillable('student_id', 'registrar_id')]
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory, HasUlids;

    public function training(): HasOneThrough
    {
        return $this->hasOneThrough(Training, Student);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function registrar(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'registrar_id');
    }
}
