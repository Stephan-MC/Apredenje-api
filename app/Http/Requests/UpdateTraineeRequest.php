<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTraineeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $trainee = $this->route('trainee');

        return $trainee ? ($this->user()?->can('update', $trainee) ?? false) : false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $trainee = $this->route('trainee');
        $traineeId = is_object($trainee) ? $trainee->id : $trainee;

        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($traineeId)],
            'password' => ['sometimes', Rule::excludeIf(fn (): bool =>
                $this->user()->isA('super-admin', 'admin') || blank($this->input('password'))),
                'current_password',
                'string',
                'min:8'
            ],
        ];
    }
}
