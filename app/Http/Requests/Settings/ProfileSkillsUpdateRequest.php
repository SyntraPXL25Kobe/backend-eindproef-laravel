<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class ProfileSkillsUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'skills' => ['nullable', 'array'],
            'skills.*' => ['integer', 'distinct', 'exists:skills,id'],
        ];
    }
}
