<?php

namespace App\Rules;

use App\Models\Trainer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class TrainerExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! Trainer::query()->whereKey($value)->exists()) {
            $fail('The selected :attribute must be a valid trainer.');
        }
    }
}
