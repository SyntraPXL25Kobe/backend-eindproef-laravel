<?php

namespace App\Models;

use App\ApplicationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'shift_id',
    'user_id',
    'status',
    'motivation',
    'reviewed_by',
    'reviewed_at',
])]
class Application extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'reviewed_at' => 'datetime',

        ];
    }
}
