<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CrewPerformanceController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can(Permission::ViewOwnSchedule->value), 403);

        $assignments = Assignment::query()
            ->where('user_id', $request->user()->id)
            ->with([
                'shift.zone.event:id,title,location,start_date,end_date',
            ])
            ->get();

        $events = $assignments
            ->groupBy(fn (Assignment $assignment) => $assignment->shift->zone->event->id)
            ->map(function ($eventAssignments) {
                /** @var Assignment $first */
                $first = $eventAssignments->first();
                $event = $first->shift->zone->event;

                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'location' => $event->location,
                    'start_date' => $event->start_date?->toDateString(),
                    'end_date' => $event->end_date?->toDateString(),
                    'shifts' => $eventAssignments
                        ->sortBy(fn (Assignment $assignment) => $assignment->shift->starts_at?->timestamp ?? PHP_INT_MAX)
                        ->values()
                        ->map(fn (Assignment $assignment) => [
                            'id' => $assignment->shift->id,
                            'title' => $assignment->shift->title,
                            'zone_name' => $assignment->shift->zone->name,
                            'starts_at' => $assignment->shift->starts_at?->toIso8601String(),
                            'ends_at' => $assignment->shift->ends_at?->toIso8601String(),
                            'check_in_at' => $assignment->check_in_at?->toIso8601String(),
                            'check_out_at' => $assignment->check_out_at?->toIso8601String(),
                            'no_show' => $assignment->no_show,
                        ]),
                    'shifts_total' => $eventAssignments->count(),
                    'check_ins' => $eventAssignments->filter(fn (Assignment $assignment) => $assignment->check_in_at !== null)->count(),
                    'no_shows' => $eventAssignments->where('no_show', true)->count(),
                    'last_check_in_at' => $eventAssignments
                        ->pluck('check_in_at')
                        ->filter()
                        ->max()?->toIso8601String(),
                    'last_check_out_at' => $eventAssignments
                        ->pluck('check_out_at')
                        ->filter()
                        ->max()?->toIso8601String(),
                ];
            })
            ->sortByDesc('start_date')
            ->values();

        $stats = [
            'events_total' => $events->count(),
            'events_checked_in' => $events->filter(fn ($event) => $event['check_ins'] > 0)->count(),
            'total_no_shows' => $assignments->where('no_show', true)->count(),
            'total_check_ins' => $assignments->filter(fn (Assignment $assignment) => $assignment->check_in_at !== null)->count(),
        ];

        return Inertia::render('app/performance/index', [
            'stats' => $stats,
            'events' => $events,
        ]);
    }
}
