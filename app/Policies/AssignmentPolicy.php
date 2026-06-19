<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Assignment;
use App\Models\User;

class AssignmentPolicy
{
    public function viewCheckInQr(User $user, Assignment $assignment): bool
    {
        return $user->can(Permission::ViewOwnSchedule->value)
            && $assignment->user_id === $user->id;
    }

    public function manageCheckIn(User $user, Assignment $assignment): bool
    {
        return $user->can(Permission::ManageCheckIns->value)
            && $user->coordinatorProfile?->id === $assignment->shift->zone->event->coordinator_profile_id;
    }

    public function markNoShow(User $user, Assignment $assignment): bool
    {
        return $user->can(Permission::MarkNoShows->value)
            && $user->coordinatorProfile?->id === $assignment->shift->zone->event->coordinator_profile_id;
    }
}
