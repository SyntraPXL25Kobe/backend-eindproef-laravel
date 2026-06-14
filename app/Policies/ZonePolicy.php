<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Event;
use App\Models\User;
use App\Models\Zone;

class ZonePolicy
{
    public function create(User $user, Event $event): bool
    {
        return $user->can(Permission::ManageZones->value)
            && $user->coordinatorProfile?->id === $event->coordinator_profile_id;
    }

    public function update(User $user, Zone $zone): bool
    {
        return $user->can(Permission::ManageZones->value)
            && $user->coordinatorProfile?->id === $zone->event->coordinator_profile_id;
    }

    public function delete(User $user, Zone $zone): bool
    {
        return $this->update($user, $zone);
    }
}
