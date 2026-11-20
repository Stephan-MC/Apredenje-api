<?php

namespace App\Http\Requests;

use App\Rules\TrainerExists;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $training = $this->route('training');
        $trainingId = is_object($training) ? $training->id : $training;

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('trainings', 'code')->ignore($trainingId)],
            'trainer_id' => ['sometimes', 'nullable', 'string', new TrainerExists],
            'fee' => ['sometimes', 'required', 'numeric', 'min:0'],
        ];
    }
}
