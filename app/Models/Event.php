<?php

namespace App\Models;

use App\EventStatus;
use App\EventVisibility;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'coordinator_profile_id',
    'title',
    'description',
    'location',
    'start_date',
    'end_date',
    'status',
    'publication_visibility',
    'max_crew_members',
    'cover_image_url',
    'published_at',
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
            'publication_visibility' => EventVisibility::class,
            'published_at' => 'datetime',
        ];
    }

    public function isPublished(): bool
    {
        return $this->status === EventStatus::Published;
    }

    public function ensureInvitationToken(): void
    {
        if ($this->publication_visibility !== EventVisibility::InviteOnly || filled($this->invite_token)) {
            return;
        }

        $this->invite_token = (string) Str::uuid();
    }

    public function syncPublicationAccess(): void
    {
        if ($this->isPublished()) {
            $this->published_at ??= now();
        }

        $this->ensureInvitationToken();
    }

    public function publish(?EventVisibility $visibility = null): void
    {
        if ($visibility) {
            $this->publication_visibility = $visibility;
        }

        $this->status = EventStatus::Published;
        $this->published_at = now();
        $this->ensureInvitationToken();
        $this->save();
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
