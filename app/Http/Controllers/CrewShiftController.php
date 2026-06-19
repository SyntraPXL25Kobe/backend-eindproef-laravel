<?php

namespace App\Http\Controllers;

use App\ApplicationStatus;
use App\Enums\Permission;
use App\EventStatus;
use App\EventVisibility;
use App\Models\Application;
use chillerlan\QRCode\QRCode;
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
            ->with(['shift.zone.event', 'assignment'])
            ->get()
            ->sortBy(fn (Application $application) => $application->shift?->starts_at?->getTimestamp() ?? PHP_INT_MAX)
            ->values()
            ->map(fn (Application $application) => [
                'assignment_id' => $application->assignment?->id,
                'id' => $application->id,
                'status' => $application->status->value,
                'motivation' => $application->motivation,
                'created_at' => $application->created_at?->toIso8601String(),
                'reviewed_at' => $application->reviewed_at?->toIso8601String(),
                'can_cancel' => $request->user()?->can('cancel', $application) ?? false,
                'check_in' => $this->checkInPayload($request, $application),
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

    /**
     * @return array<string, mixed>|null
     */
    private function checkInPayload(Request $request, Application $application): ?array
    {
        $event = $application->shift?->zone?->event;
        $assignment = $application->assignment;

        if (! $event || ! $assignment || $application->status !== ApplicationStatus::Approved) {
            return null;
        }

        return [
            'is_available_today' => $event->isHappeningToday() && ! $assignment->no_show,
            'checked_in_at' => $assignment->check_in_at?->toIso8601String(),
            'no_show' => $assignment->no_show,
            'no_show_reason' => $assignment->no_show_reason,
            'qr_svg_src' => $event->isHappeningToday() && $request->user()?->can('viewCheckInQr', $assignment)
                ? (new QRCode)->render($assignment->check_in_token)
                : null,
        ];
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
