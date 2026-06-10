<?php

namespace App\Models;

use App\EventStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'coordinator_profile_id',
    'title',
    'description',
    'location',
    'start_date',
    'end_date',
    'status',
    'max_volunteers',
    'cover_image_url',
])]
class Event extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => EventStatus::class,

        ];
    }
}
