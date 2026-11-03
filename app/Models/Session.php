<?php

namespace App\Models;

use App\Observers\SessionObserver;
use Database\Factories\SessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(SessionObserver::class)]
#[Fillable('training_id', 'time', 'duration')]
#[Table('training_sessions')]
class Session extends Model
{
    /** @use HasFactory<SessionFactory> */
    use HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'time' => 'datetime',
            'duration' => 'integer',
        ];
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }
}
