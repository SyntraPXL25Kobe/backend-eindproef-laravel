<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'application_id',
    'shift_id',
    'user_id',
    'confirmed_at',
    'check_in_token',
    'check_in_at',
    'check_out_at',
    'no_show',
    'no_show_reason',
    'no_show_marked_by',
])]
class Assignment extends Model
{
    protected static function booted(): void
    {
        static::creating(function (Assignment $assignment): void {
            $assignment->check_in_token ??= (string) Str::uuid();
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
            'no_show' => 'boolean',

        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function noShowMarker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'no_show_marked_by');
    }

    public function isCheckedIn(): bool
    {
        return $this->check_in_at !== null;
    }
}
