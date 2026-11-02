<?php

namespace App\Models;

use App\Observers\BatchTypeObserver;
use Database\Factories\BatchTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(BatchTypeObserver::class)]
#[Fillable('name')]
class BatchType extends Model
{
    /** @use HasFactory<BatchTypeFactory> */
    use HasFactory, HasUlids;

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }
}
