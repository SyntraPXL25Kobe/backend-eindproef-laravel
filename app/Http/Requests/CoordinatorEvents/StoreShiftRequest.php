<?php

namespace App\Http\Requests\CoordinatorEvents;

use App\Models\Zone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        $zone = $this->route('zone');

        return $zone instanceof Zone && ($this->user()?->can('create', [\App\Models\Shift::class, $zone]) ?? false);
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