<?php

namespace App\Http\Requests\CoordinatorEvents;

use App\Models\Shift;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        $shift = $this->route('shift');

        return $shift instanceof Shift && ($this->user()?->can('update', $shift) ?? false);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'capacity' => ['required', 'integer', 'min:1'],
            'required_skill_id' => ['nullable', 'integer', Rule::exists('skills', 'id')],
            'status' => ['required', Rule::in(['open', 'full', 'closed', 'cancelled'])],
        ];
    }
}