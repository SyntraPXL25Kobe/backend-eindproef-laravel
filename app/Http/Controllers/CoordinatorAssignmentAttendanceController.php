<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
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
            ->with(['user', 'shift.zone.event'])
            ->first();

        if (! $assignment) {
            Inertia::flash('scan_feedback', [
                'status' => 'error',
                'message' => 'Deze QR-code hoort niet bij dit event.',
            ]);

            return back();
        }

        $this->authorize('manageCheckIn', $assignment);

        return $this->performScanAttendance($assignment);
    }

    public function checkIn(Assignment $assignment): RedirectResponse
    {
        $assignment->loadMissing(['user', 'shift.zone.event']);

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
            return back();
        }

        $assignment->update([
            'no_show' => $validated['no_show'],
            'no_show_reason' => $validated['no_show'] ? ($validated['reason'] ?: null) : null,
            'no_show_marked_by' => $validated['no_show'] ? $request->user()->id : null,
        ]);

        return back();
    }

    private function performCheckIn(Assignment $assignment, bool $forScan = false): RedirectResponse
    {
        $event = $assignment->shift->zone->event;
        $hasOpenCheckInForEvent = $this->eventAssignmentsQuery($assignment)
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->exists();

        if (! $event->isHappeningToday()) {
            if ($forScan) {
                Inertia::flash('scan_feedback', [
                    'status' => 'error',
                    'message' => 'Check-in is alleen beschikbaar op de dag van het event.',
                ]);
            }

            return back();
        }

        if ($hasOpenCheckInForEvent) {
            if ($forScan) {
                Inertia::flash('scan_feedback', [
                    'status' => 'error',
                    'message' => 'Crewlid is al ingecheckt voor dit event.',
                    'assignment' => $this->scanAssignmentData($assignment),
                ]);
            }

            return back();
        }

        if ($forScan && $assignment->no_show) {
            Inertia::flash('scan_feedback', [
                'status' => 'error',
                'message' => 'Crewlid staat als no-show gemarkeerd en kan niet via QR ingecheckt worden.',
                'assignment' => $this->scanAssignmentData($assignment),
            ]);

            return back();
        }

        $this->eventAssignmentsQuery($assignment)->update([
            'check_in_at' => now(),
            'check_out_at' => null,
            'no_show' => false,
            'no_show_reason' => null,
            'no_show_marked_by' => null,
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

    private function performScanAttendance(Assignment $assignment): RedirectResponse
    {
        $hasOpenCheckInForEvent = $this->eventAssignmentsQuery($assignment)
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->exists();

        if (! $hasOpenCheckInForEvent) {
            return $this->performCheckIn($assignment, true);
        }

        if ($this->hasActiveShiftNow($assignment)) {
            Inertia::flash('scan_feedback', [
                'status' => 'error',
                'message' => 'Crewlid heeft nog een actieve shift lopen en kan nog niet uitgecheckt worden.',
                'assignment' => $this->scanAssignmentData($assignment),
            ]);

            return back();
        }

        $this->eventAssignmentsQuery($assignment)
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->update([
                'check_out_at' => now(),
            ]);

        Inertia::flash('scan_feedback', [
            'status' => 'success',
            'message' => 'Crewlid succesvol uitgecheckt.',
            'assignment' => $this->scanAssignmentData($assignment->fresh()),
        ]);

        return back();
    }

    private function hasActiveShiftNow(Assignment $assignment): bool
    {
        return $this->eventAssignmentsQuery($assignment)
            ->whereHas('shift', fn (Builder $query) => $query
                ->where('starts_at', '<=', now())
                ->where('ends_at', '>=', now()))
            ->exists();
    }

    private function eventAssignmentsQuery(Assignment $assignment): Builder
    {
        $eventId = $assignment->shift->zone->event_id;

        return Assignment::query()
            ->where('user_id', $assignment->user_id)
            ->whereHas('shift.zone', fn ($query) => $query->where('event_id', $eventId));
    }

    /**
     * @return array<string, mixed>
     */
    private function scanAssignmentData(Assignment $assignment): array
    {
        return [
            'id' => $assignment->id,
            'check_in_at' => $assignment->check_in_at?->toIso8601String(),
            'check_out_at' => $assignment->check_out_at?->toIso8601String(),
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
