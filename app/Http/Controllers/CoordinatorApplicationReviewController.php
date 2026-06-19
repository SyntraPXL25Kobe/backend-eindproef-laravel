<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Assignment;
use App\Notifications\ShiftApplicationApprovedNotification;
use App\Notifications\ShiftApplicationCancelledNotification;
use App\Notifications\ShiftApplicationRejectedNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CoordinatorApplicationReviewController extends Controller
{
    public function update(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('review', $application);

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                ApplicationStatus::Approved->value,
                ApplicationStatus::Rejected->value,
                ApplicationStatus::Cancelled->value,
            ])],
        ]);

        $previousStatus = null;

        $application = DB::transaction(function () use ($application, $validated, $request, &$previousStatus): Application {
            $lockedApplication = Application::query()
                ->whereKey($application->getKey())
                ->lockForUpdate()
                ->with(['shift', 'user'])
                ->firstOrFail();

            $previousStatus = $lockedApplication->status;

            if (
                $validated['status'] === ApplicationStatus::Approved->value
                && $lockedApplication->status !== ApplicationStatus::Approved
                && $this->shiftCapacityReached($lockedApplication)
            ) {
                return $lockedApplication;
            }

            $lockedApplication->update([
                'status' => $validated['status'],
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            if ($lockedApplication->status === ApplicationStatus::Approved) {
                Assignment::query()->updateOrCreate(
                    ['application_id' => $lockedApplication->id],
                    [
                        'shift_id' => $lockedApplication->shift_id,
                        'user_id' => $lockedApplication->user_id,
                        'confirmed_at' => now(),
                    ]
                );
            } else {
                Assignment::query()
                    ->where('application_id', $lockedApplication->id)
                    ->delete();
            }

            return $lockedApplication;
        });

        if (
            $validated['status'] === ApplicationStatus::Approved->value
            && $application->status !== ApplicationStatus::Approved
        ) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Deze shift heeft al het maximum aantal goedgekeurde crewleden bereikt.',
            ]);

            return back();
        }

        $application->loadMissing('user', 'shift.zone.event');

        if ($previousStatus !== $application->status) {
            match ($application->status) {
                ApplicationStatus::Approved => $application->user->notify(
                    new ShiftApplicationApprovedNotification($application)
                ),
                ApplicationStatus::Rejected => $application->user->notify(
                    new ShiftApplicationRejectedNotification($application)
                ),
                ApplicationStatus::Cancelled => $application->user->notify(
                    new ShiftApplicationCancelledNotification($application)
                ),
                default => null,
            };
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => match ($application->status) {
                ApplicationStatus::Approved => 'Aanvraag goedgekeurd.',
                ApplicationStatus::Rejected => 'Aanvraag afgewezen.',
                ApplicationStatus::Cancelled => 'Aanvraag geannuleerd en opnieuw opengezet.',
                default => 'Aanvraag bijgewerkt.',
            },
        ]);

        return back();
    }

    private function shiftCapacityReached(Application $application): bool
    {
        $approvedCount = Application::query()
            ->where('shift_id', $application->shift_id)
            ->where('status', ApplicationStatus::Approved->value)
            ->where(fn (Builder $query) => $query->whereKeyNot($application->getKey()))
            ->lockForUpdate()
            ->count();

        return $approvedCount >= $application->shift->capacity;
    }
}
