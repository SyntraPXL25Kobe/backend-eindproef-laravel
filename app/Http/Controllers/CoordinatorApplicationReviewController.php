<?php

namespace App\Http\Controllers;

use App\ApplicationStatus;
use App\Models\Application;
use App\Models\Assignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CoordinatorApplicationReviewController extends Controller
{
    private const APPLICATION_ID_COLUMN = 'application_id';

    private const SHIFT_ID_COLUMN = 'shift_id';

    private const STATUS_COLUMN = 'status';

    /**
     * @var array<int, string>
     */
    private const REVIEWABLE_STATUSES = [
        ApplicationStatus::Pending->value,
        ApplicationStatus::Approved->value,
        ApplicationStatus::Rejected->value,
    ];

    public function update(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('review', $application);

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                ApplicationStatus::Approved->value,
                ApplicationStatus::Rejected->value,
            ])],
        ]);

        if (! in_array($application->status->value, self::REVIEWABLE_STATUSES, true)) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Deze aanvraag kan niet meer aangepast worden.',
            ]);

            return back();
        }

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
            Assignment::query()->updateOrCreate([
                'application_id' => $application->id,
            ], [
                'shift_id' => $application->shift_id,
                'user_id' => $application->user_id,
                'confirmed_at' => now(),
            ]);
        } else {
            Assignment::query()
                ->where(self::APPLICATION_ID_COLUMN, $application->id)
                ->delete();
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $application->status === ApplicationStatus::Approved
                ? 'Aanvraag goedgekeurd.'
                : 'Aanvraag afgewezen.',
        ]);

        return back();
    }

    private function shiftCapacityReached(Application $application): bool
    {
        $approvedCount = Application::query()
            ->where(self::SHIFT_ID_COLUMN, $application->shift_id)
            ->where(self::STATUS_COLUMN, ApplicationStatus::Approved->value)
            ->count();

        return $approvedCount >= $application->shift->capacity;
    }
}
