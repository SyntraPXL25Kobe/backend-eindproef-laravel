<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Shift;
use App\Models\User;
use App\Models\Zone;

class ShiftPolicy
{
    public function create(User $user, Zone $zone): bool
    {
        return $user->can(Permission::ManageShifts->value)
            && $user->coordinatorProfile?->id === $zone->event->coordinator_profile_id;
    }

    public function update(User $user, Shift $shift): bool
    {
        return $user->can(Permission::ManageShifts->value)
            && $user->coordinatorProfile?->id === $shift->zone->event->coordinator_profile_id;
    }

    public function delete(User $user, Shift $shift): bool
    {
        return $this->update($user, $shift);
    }
}
