<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBatchTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $batchType = $this->route('batch_type');

        return $batchType ? ($this->user()?->can('update', $batchType) ?? false) : false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
        ];
    }
}
