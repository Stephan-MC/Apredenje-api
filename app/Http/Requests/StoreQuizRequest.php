<?php

namespace App\Http\Requests;

use App\Models\Quiz;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        $training = $this->route('training');

        return $training
            ? ($this->user()?->can('create', [Quiz::class, $training]) ?? false)
            : false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'mark' => ['required', 'integer', 'min:1'],
        ];
    }
}
