<?php

namespace App\Policies;

use App\ApplicationStatus;
use App\Enums\Permission;
use App\Models\Application;
use App\Models\Shift;
use App\Models\User;
use App\ShiftStatus;

class ApplicationPolicy
{
    public function store(User $user, Shift $shift): bool
    {
        return $user->can(Permission::ApplyForShift->value)
            && $shift->status === ShiftStatus::Open
            && ! Application::query()
                ->where('shift_id', $shift->id)
                ->where('user_id', $user->id)
                ->exists();
    }

    public function cancel(User $user, Application $application): bool
    {
        return $user->id === $application->user_id
            && $application->status === ApplicationStatus::Pending;
    }

    public function review(User $user, Application $application): bool
    {
        return $user->can(Permission::ReviewApplications->value)
            && $user->coordinatorProfile?->id === $application->shift->zone->event->coordinator_profile_id;
    }
}
