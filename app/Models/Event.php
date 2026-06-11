<?php

namespace App\Models;

use App\EventStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'coordinator_profile_id',
    'title',
    'description',
    'location',
    'start_date',
    'end_date',
    'status',
    'max_crew_members',
    'cover_image_url',
])]
class Event extends Model
{
    use SoftDeletes;

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

    public function coordinatorProfile(): BelongsTo
    {
        return $this->belongsTo(CoordinatorProfile::class);
    }

    public function coordinator(): HasOneThrough
    {
        return $this->hasOneThrough(User::class, CoordinatorProfile::class);
    }

    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class);
    }

    public function shifts(): HasManyThrough
    {
        return $this->hasManyThrough(
            Shift::class,
            Zone::class,
            'event_id',
            'zone_id'
        );
    }
}
