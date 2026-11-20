<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInternshipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $internship = $this->route('internship');

        return $internship ? ($this->user()?->can('update', $internship) ?? false) : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'required', 'string', 'exists:users,id'],
            'start_time' => ['sometimes', 'required', 'date'],
            'duration' => ['sometimes', 'required', 'integer', 'min:1'],
        ];
    }
}
