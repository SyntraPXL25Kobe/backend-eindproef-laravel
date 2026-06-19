<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CoordinatorEventDashboardController extends Controller
{
    public function show(Request $request, Event $event): Response
    {
        $this->authorize('update', $event);

        $assignments = Assignment::query()
            ->whereHas('shift.zone', fn ($query) => $query->where('event_id', $event->id))
            ->with([
                'user:id,name,email,phone',
                'shift.zone:id,event_id,name',
            ])
            ->orderBy('check_in_at')
            ->orderBy('created_at')
            ->get();

        $checkedInCount = $assignments->filter(fn (Assignment $assignment) => $assignment->check_in_at !== null)->count();
        $noShowCount = $assignments->where('no_show', true)->count();
        $pendingCount = $assignments->count() - $checkedInCount - $noShowCount;

        return Inertia::render('app/events/dashboard', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'location' => $event->location,
                'start_date' => $event->start_date?->toDateString(),
                'end_date' => $event->end_date?->toDateString(),
                'is_live_today' => $event->isHappeningToday(),
            ],
            'stats' => [
                'total_assigned' => $assignments->count(),
                'checked_in' => $checkedInCount,
                'pending' => $pendingCount,
                'no_shows' => $noShowCount,
                'check_in_rate' => $assignments->count() > 0
                    ? round(($checkedInCount / $assignments->count()) * 100)
                    : 0,
            ],
            'assignments' => $assignments->map(fn (Assignment $assignment) => [
                'id' => $assignment->id,
                'application_id' => $assignment->application_id,
                'confirmed_at' => $assignment->confirmed_at?->toIso8601String(),
                'check_in_at' => $assignment->check_in_at?->toIso8601String(),
                'check_out_at' => $assignment->check_out_at?->toIso8601String(),
                'no_show' => $assignment->no_show,
                'no_show_reason' => $assignment->no_show_reason,
                'user' => [
                    'id' => $assignment->user->id,
                    'name' => $assignment->user->name,
                    'email' => $assignment->user->email,
                    'phone' => $assignment->user->phone,
                ],
                'shift' => [
                    'id' => $assignment->shift->id,
                    'title' => $assignment->shift->title,
                    'starts_at' => $assignment->shift->starts_at?->toIso8601String(),
                    'ends_at' => $assignment->shift->ends_at?->toIso8601String(),
                    'zone_name' => $assignment->shift->zone->name,
                ],
                'can_check_in' => $request->user()?->can('manageCheckIn', $assignment) ?? false,
                'can_mark_no_show' => $request->user()?->can('markNoShow', $assignment) ?? false,
            ])->values(),
            'last_updated_at' => now()->toIso8601String(),
            'scan_endpoint' => route('coordinator.events.check-ins.scan', ['event' => $event->id]),
            'manual_check_in_endpoint' => route('coordinator.assignments.check-in', ['assignment' => '__ASSIGNMENT__']),
            'manual_check_out_endpoint' => route('coordinator.assignments.check-out', ['assignment' => '__ASSIGNMENT__']),
            'no_show_endpoint' => route('coordinator.assignments.no-show', ['assignment' => '__ASSIGNMENT__']),
        ]);
    }
}
