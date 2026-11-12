<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTrainingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $training = $this->route('training');

        return $training ? ($this->user()?->can('update', $training) ?? false) : false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:50'],
            'trainer_id' => ['sometimes', 'nullable', 'string', 'exists:\App\Models\Trainer,id'],
            'fee' => ['sometimes', 'required', 'integer', 'min:0'],
        ];
    }
}
