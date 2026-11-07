<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        $batch = $this->route('batch');

        return $batch ? ($this->user()?->can('update', $batch) ?? false) : false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'batch_type_id' => ['sometimes', 'required', 'string', 'exists:batch_types,id'],
            'session_id' => ['sometimes', 'required', 'string', 'exists:training_sessions,id'],
            'trainer_id' => ['sometimes', 'required', 'string', 'exists:users,id'],
        ];
    }
}
