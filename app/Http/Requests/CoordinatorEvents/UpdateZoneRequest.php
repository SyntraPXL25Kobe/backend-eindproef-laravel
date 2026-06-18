<?php

namespace App\Http\Requests\CoordinatorEvents;

use App\Models\Zone;
use Illuminate\Foundation\Http\FormRequest;

class UpdateZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        $zone = $this->route('zone');

        return $zone instanceof Zone && ($this->user()?->can('update', $zone) ?? false);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ];
    }
}