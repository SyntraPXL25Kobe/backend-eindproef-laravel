<?php

namespace App\Http\Controllers;

use App\ApplicationStatus;
use App\EventStatus;
use App\EventVisibility;
use App\Models\Application;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class PublicEventController extends Controller
{
    private const SHIFT_ID_COLUMN = 'shift_id';

    private const MODEL_ID_COLUMN = 'id';

    public function show(Request $request, Event $event): Response
    {
        $event->load(['coordinatorProfile', 'zones.shifts.requiredSkill']);

        abort_unless(
            $event->status === EventStatus::Published
                && $event->publication_visibility === EventVisibility::Public,
            404,
        );

        return Inertia::render('events/show', [
            'event' => $this->payload($event, $request->user()),
            'isInvitation' => false,
        ]);
    }

    public function showInvite(Request $request, string $token): Response
    {
        $event = Event::query()
            ->where(['invite_token' => $token])
            ->firstOrFail();

        $event->load(['coordinatorProfile', 'zones.shifts.requiredSkill']);

        abort_unless(
            $event->status === EventStatus::Published
                && $event->publication_visibility === EventVisibility::InviteOnly,
            404,
        );

        return Inertia::render('events/show', [
            'event' => $this->payload($event, $request->user()),
            'isInvitation' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Event $event, ?User $user): array
    {
        $applicationsByShift = $this->applicationsByShift($event, $user);

        return [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'location' => $event->location,
            'start_date' => $event->start_date?->toDateString(),
            'end_date' => $event->end_date?->toDateString(),
            'max_crew_members' => $event->max_crew_members,
            'cover_image_url' => $event->cover_image_url,
            'coordinator_name' => $event->coordinatorProfile?->organisation_name,
            'zones' => $event->zones->map(function ($zone) use ($applicationsByShift, $user) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'description' => $zone->description,
                    'shifts' => $zone->shifts->map(function ($shift) use ($applicationsByShift, $user) {
                        $application = $applicationsByShift->get($shift->id);

                        return [
                            'id' => $shift->id,
                            'title' => $shift->title,
                            'description' => $shift->description,
                            'starts_at' => $shift->starts_at?->toIso8601String(),
                            'ends_at' => $shift->ends_at?->toIso8601String(),
                            'capacity' => $shift->capacity,
                            'status' => $shift->status->value,
                            'required_skill_name' => $shift->requiredSkill?->name,
                            'application' => $application ? [
                                'id' => $application->id,
                                'status' => $application->status->value,
                            ] : null,
                            'can_apply' => $user?->can('store', [Application::class, $shift]) ?? false,
                            'can_cancel' => $application ? ($user?->can('cancel', $application) ?? false) : false,
                        ];
                    })->values(),
                ];
            })->values(),
        ];
    }

    private function applicationsByShift(Event $event, ?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        $shiftIds = $event->zones
            ->flatMap(fn ($zone) => $zone->shifts->pluck(self::MODEL_ID_COLUMN))
            ->values();

        if ($shiftIds->isEmpty()) {
            return collect();
        }

        return $user->applications()
            ->whereIn(self::SHIFT_ID_COLUMN, $shiftIds)
            ->whereIn('status', [
                ApplicationStatus::Pending,
                ApplicationStatus::Approved,
                ApplicationStatus::Rejected,
                ApplicationStatus::Cancelled,
            ])
            ->get()
            ->keyBy(self::SHIFT_ID_COLUMN);
    }
}
