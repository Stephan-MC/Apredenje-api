<?php

namespace App\Http\Requests;

use App\Models\Training;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Training::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50'],
            'trainer_id' => ['nullable', 'string', 'exists:users,id'],
            'fee' => ['required', 'integer', 'min:0'],
        ];
    }
}
