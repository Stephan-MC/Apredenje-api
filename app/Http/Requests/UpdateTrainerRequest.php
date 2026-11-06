<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTrainerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $trainer = $this->route('trainer');

        return $trainer ? ($this->user()?->can('update', $trainer) ?? false) : false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $trainer = $this->route('trainer');
        $trainerId = is_object($trainer) ? $trainer->id : $trainer;

        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($trainerId)],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
        ];
    }
}
