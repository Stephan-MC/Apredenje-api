<?php

namespace App\Http\Requests;

use App\Models\Batch;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Batch::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'batch_type_id' => ['required', 'string', 'exists:batch_types,id'],
            'session_id' => ['required', 'string', 'exists:training_sessions,id'],
            'trainer_id' => ['required', 'string', 'exists:users,id'],
        ];
    }
}
