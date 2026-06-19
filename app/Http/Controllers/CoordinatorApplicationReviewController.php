<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Assignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        if (
            $validated['status'] === ApplicationStatus::Approved->value
            && $application->status !== ApplicationStatus::Approved
            && $this->shiftCapacityReached($application)
        ) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Deze shift heeft al het maximum aantal goedgekeurde crewleden bereikt.',
            ]);

            return back();
        }

        $application->update([
            'status' => $validated['status'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        if ($application->status === ApplicationStatus::Approved) {
            Assignment::query()->updateOrCreate(
                ['application_id' => $application->id],
                [
                    'shift_id' => $application->shift_id,
                    'user_id' => $application->user_id,
                    'confirmed_at' => now(),
                ]
            );
        } else {
            Assignment::query()
                ->where('application_id', $application->id)
                ->delete();
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
            ->count();

        return $approvedCount >= $application->shift->capacity;
    }
}
