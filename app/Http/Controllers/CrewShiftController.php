<?php

namespace App\Http\Controllers;

use App\ApplicationStatus;
use App\Enums\Permission;
use App\EventStatus;
use App\EventVisibility;
use App\Models\Application;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CrewShiftController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()?->can(Permission::ViewOwnSchedule->value), 403);

        $applications = $request->user()
            ->applications()
            ->whereIn('status', [
                ApplicationStatus::Pending,
                ApplicationStatus::Approved,
                ApplicationStatus::Rejected,
            ])
            ->with('shift.zone.event')
            ->get()
            ->sortBy(fn (Application $application) => $application->shift?->starts_at?->getTimestamp() ?? PHP_INT_MAX)
            ->values()
            ->map(fn (Application $application) => [
                'id' => $application->id,
                'status' => $application->status->value,
                'motivation' => $application->motivation,
                'created_at' => $application->created_at?->toIso8601String(),
                'reviewed_at' => $application->reviewed_at?->toIso8601String(),
                'can_cancel' => $request->user()?->can('cancel', $application) ?? false,
                'shift' => [
                    'id' => $application->shift?->id,
                    'title' => $application->shift?->title,
                    'starts_at' => $application->shift?->starts_at?->toIso8601String(),
                    'ends_at' => $application->shift?->ends_at?->toIso8601String(),
                    'status' => $application->shift?->status?->value,
                    'capacity' => $application->shift?->capacity,
                    'zone_name' => $application->shift?->zone?->name,
                    'event_title' => $application->shift?->zone?->event?->title,
                    'event_location' => $application->shift?->zone?->event?->location,
                    'event_show_url' => $this->eventShowUrl($application),
                ],
            ])
            ->all();

        return Inertia::render('app/shifts/index', [
            'applications' => $applications,
        ]);
    }

    private function eventShowUrl(Application $application): ?string
    {
        $event = $application->shift?->zone?->event;

        if (! $event || $event->status !== EventStatus::Published) {
            return null;
        }

        if ($event->publication_visibility === EventVisibility::Public) {
            return route('events.public.show', ['event' => $event->id]);
        }

        if ($event->publication_visibility === EventVisibility::InviteOnly && filled($event->invite_token)) {
            return route('events.invite.show', ['token' => $event->invite_token]);
        }

        return null;
    }
}
