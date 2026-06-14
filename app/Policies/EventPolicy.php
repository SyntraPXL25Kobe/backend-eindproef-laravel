<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Event $event): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::CreateEvents->value)
            && $user->coordinatorProfile()->exists();
    }

    public function update(User $user, Event $event): bool
    {
        return $user->can(Permission::EditEvents->value)
            && $user->coordinatorProfile?->id === $event->coordinator_profile_id;
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->can(Permission::DeleteEvents->value)
            && $user->coordinatorProfile?->id === $event->coordinator_profile_id;
    }

    public function publish(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }

    public function archive(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }
}
