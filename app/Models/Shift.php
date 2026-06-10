<?php

namespace App\Models;

use App\ShiftStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'zone_id',
    'title',
    'description',
    'starts_at',
    'ends_at',
    'capacity',
    'required_skill_id',
    'status',
])]
class Shift extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'status' => ShiftStatus::class,

        ];
    }
}
