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
    private const SHIFT_ID_COLUMN = 'shift_id';

    private const USER_ID_COLUMN = 'user_id';

    public function store(User $user, Shift $shift): bool
    {
        return $user->can(Permission::ApplyForShift->value)
            && $shift->status === ShiftStatus::Open
            && ! Application::query()
                ->where(self::SHIFT_ID_COLUMN, $shift->id)
                ->where(self::USER_ID_COLUMN, $user->id)
                ->whereIn('status', [
                    ApplicationStatus::Pending->value,
                    ApplicationStatus::Approved->value,
                    ApplicationStatus::Rejected->value,
                ])
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
