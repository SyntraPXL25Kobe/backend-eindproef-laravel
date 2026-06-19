<?php

namespace App\Policies;

use App\Enums\ApplicationStatus;
use App\Enums\Permission;
use App\Enums\ShiftStatus;
use App\Models\Application;
use App\Models\Shift;
use App\Models\User;

class ApplicationPolicy
{
    public function store(User $user, Shift $shift): bool
    {
        if (! $user->can(Permission::ApplyForShift->value)) {
            return false;
        }

        if ($shift->status !== ShiftStatus::Open) {
            return false;
        }

        if (Application::query()
            ->where('shift_id', $shift->id)
            ->where('user_id', $user->id)
            ->whereIn('status', [
                ApplicationStatus::Pending->value,
                ApplicationStatus::Approved->value,
                ApplicationStatus::Rejected->value,
            ])
            ->exists()
        ) {
            return false;
        }

        // Block applying when the user already has a pending or approved application
        // for a shift that overlaps in time with this one
        if ($shift->starts_at && $shift->ends_at) {
            $hasOverlap = Application::query()
                ->where('user_id', $user->id)
                ->whereIn('status', [
                    ApplicationStatus::Pending->value,
                    ApplicationStatus::Approved->value,
                ])
                ->whereHas('shift', function ($query) use ($shift) {
                    $query
                        ->whereKeyNot($shift->id)
                        ->whereNotNull('starts_at')
                        ->whereNotNull('ends_at')
                        ->where('starts_at', '<', $shift->ends_at)
                        ->where('ends_at', '>', $shift->starts_at);
                })
                ->exists();

            if ($hasOverlap) {
                return false;
            }
        }

        return true;
    }

    public function cancel(User $user, Application $application): bool
    {
        return $user->id === $application->user_id
            && $application->status === ApplicationStatus::Pending;
    }

    public function review(User $user, Application $application): bool
    {
        // Cancelled applications cannot be reviewed; only pending/approved/rejected can change
        if ($application->status === ApplicationStatus::Cancelled) {
            return false;
        }

        return $user->can(Permission::ReviewApplications->value)
            && $user->coordinatorProfile?->id === $application->shift->zone->event->coordinator_profile_id;
    }
}
