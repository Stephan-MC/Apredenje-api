<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $payment = $this->route('payment');

        return $payment ? ($this->user()?->can('update', $payment) ?? false) : false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'training_id' => ['sometimes', 'required', 'string', 'exists:trainings,id'],
            'student_id' => ['sometimes', 'required', 'string', 'exists:students,id'],
            'registrar_id' => ['sometimes', 'nullable', 'string', 'exists:users,id'],
        ];
    }
}
