<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $session = $this->route('session');

        return $session ? ($this->user()?->can('update', $session) ?? false) : false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'training_id' => ['sometimes', 'required', 'string', 'exists:trainings,id'],
            'time' => ['sometimes', 'required', 'date'],
            'duration' => ['sometimes', 'required', 'integer', 'min:1'],
        ];
    }
}
