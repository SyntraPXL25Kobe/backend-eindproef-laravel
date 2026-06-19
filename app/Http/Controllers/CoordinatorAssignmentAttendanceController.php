<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CoordinatorAssignmentAttendanceController extends Controller
{
    public function scan(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'scan_result' => ['required', 'string'],
        ]);

        $assignment = Assignment::query()
            ->where('check_in_token', $this->extractToken($validated['scan_result']))
            ->whereHas('shift.zone', fn ($query) => $query->where('event_id', $event->id))
            ->with(['shift.zone.event'])
            ->first();

        if (! $assignment) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Deze QR-code hoort niet bij dit event.',
            ]);
            Inertia::flash('scan_feedback', [
                'status' => 'error',
                'message' => 'Deze QR-code hoort niet bij dit event.',
            ]);

            return back();
        }

        $this->authorize('manageCheckIn', $assignment);

        return $this->performCheckIn($assignment, true);
    }

    public function checkIn(Assignment $assignment): RedirectResponse
    {
        $assignment->loadMissing(['shift.zone.event']);

        $this->authorize('manageCheckIn', $assignment);

        return $this->performCheckIn($assignment);
    }

    public function updateNoShow(Request $request, Assignment $assignment): RedirectResponse
    {
        $assignment->loadMissing(['shift.zone.event']);

        $this->authorize('markNoShow', $assignment);

        $validated = $request->validate([
            'no_show' => ['required', 'boolean'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validated['no_show'] && $assignment->check_in_at !== null) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Een ingecheckt crewlid kan niet als no-show gemarkeerd worden.',
            ]);

            return back();
        }

        $assignment->update([
            'no_show' => $validated['no_show'],
            'no_show_reason' => $validated['no_show'] ? ($validated['reason'] ?: null) : null,
            'no_show_marked_by' => $validated['no_show'] ? $request->user()->id : null,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $validated['no_show']
                ? 'Crewlid gemarkeerd als no-show.'
                : 'No-show markering verwijderd.',
        ]);

        return back();
    }

    private function performCheckIn(Assignment $assignment, bool $forScan = false): RedirectResponse
    {
        $event = $assignment->shift->zone->event;

        if (! $event->isHappeningToday()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Check-in is alleen beschikbaar op de dag van het event.',
            ]);
            if ($forScan) {
                Inertia::flash('scan_feedback', [
                    'status' => 'error',
                    'message' => 'Check-in is alleen beschikbaar op de dag van het event.',
                ]);
            }

            return back();
        }

        if ($assignment->check_in_at !== null) {
            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Crewlid was al ingecheckt.',
            ]);
            if ($forScan) {
                Inertia::flash('scan_feedback', [
                    'status' => 'success',
                    'message' => 'Crewlid was al ingecheckt.',
                    'assignment' => $this->scanAssignmentData($assignment),
                ]);
            }

            return back();
        }

        $assignment->update([
            'check_in_at' => now(),
            'no_show' => false,
            'no_show_reason' => null,
            'no_show_marked_by' => null,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Crewlid succesvol ingecheckt.',
        ]);
        if ($forScan) {
            Inertia::flash('scan_feedback', [
                'status' => 'success',
                'message' => 'Crewlid succesvol ingecheckt.',
                'assignment' => $this->scanAssignmentData($assignment),
            ]);
        }

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function scanAssignmentData(Assignment $assignment): array
    {
        return [
            'id' => $assignment->id,
            'check_in_at' => $assignment->check_in_at?->toIso8601String(),
            'user' => [
                'name' => $assignment->user->name,
                'email' => $assignment->user->email,
                'phone' => $assignment->user->phone,
            ],
            'shift' => [
                'title' => $assignment->shift->title,
                'zone_name' => $assignment->shift->zone->name,
            ],
        ];
    }

    private function extractToken(string $scanResult): string
    {
        return trim(str($scanResult)->afterLast(':')->value());
    }
}