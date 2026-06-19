<?php

namespace App\Models;

use App\ShiftStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function requiredSkill(): BelongsTo
    {
        return $this->belongsTo(Skill::class, 'required_skill_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }
}
