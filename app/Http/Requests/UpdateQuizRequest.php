<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        $quiz = $this->route('quiz');

        return $quiz ? ($this->user()?->can('update', $quiz) ?? false) : false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'training_id' => ['sometimes', 'required', 'string', 'exists:trainings,id'],
            'mark' => ['sometimes', 'required', 'integer', 'min:1'],
        ];
    }
}
