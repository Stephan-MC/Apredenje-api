<?php

namespace App\Http\Requests;

use App\Models\Session;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Session::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'training_id' => ['required', 'string', 'exists:trainings,id'],
            'time' => ['required', 'date'],
            'duration' => ['required', 'integer', 'min:1'],
        ];
    }
}
