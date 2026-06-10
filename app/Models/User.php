<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\CoordinatorRegistrationStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Boost\Install\Skill;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name',
    'email',
    'password',
    'phone',
    'address',
    'postal_code',
    'city',
    'country',
    'is_active',
    'coordinator_registration_status',
    'coordinator_rejected_reason',
])]
#[Hidden([
    'password',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'remember_token',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'coordinator_registration_status' => CoordinatorRegistrationStatus::class,

        ];
    }

    public function coordinatorProfile(): HasOne
    {
        return $this->hasOne(CoordinatorProfile::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'user_skill');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function coordinatorEvents(): HasManyThrough
    {
        return $this->hasManyThrough(
            Event::class,
            CoordinatorProfile::class,
        );
    }

    // -------------------------------------------------------------------------
    // Helper methodes
    // -------------------------------------------------------------------------

    public function isCoordinator(): bool
    {
        return $this->coordinatorProfile()->exists();
    }

    public function isPendingCoordinator(): bool
    {
        return $this->coordinator_registration_status === CoordinatorRegistrationStatus::Pending;
    }

    public function isRejectedCoordinator(): bool
    {
        return $this->coordinator_registration_status === CoordinatorRegistrationStatus::Rejected;
    }

    public function hasAddress(): bool
    {
        return filled($this->address)
            && filled($this->postal_code)
            && filled($this->city)
            && filled($this->country);
    }
}
