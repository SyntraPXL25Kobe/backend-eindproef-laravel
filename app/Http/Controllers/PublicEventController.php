<?php

namespace App\Http\Controllers;

use App\ApplicationStatus;
use App\EventStatus;
use App\EventVisibility;
use App\Models\Application;
use App\Models\Event;
use App\Models\Shift;
use App\Models\User;
use App\ShiftStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class PublicEventController extends Controller
{
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
                            'cannot_apply_reason' => $this->cannotApplyReason($shift, $application, $user),
                            'can_cancel' => $application ? ($user?->can('cancel', $application) ?? false) : false,
                        ];
                    })->values(),
                ];
            })->values(),
        ];
    }

    /**
     * @return 'shift_closed'|'already_applied'|'rejected'|'overlap'|null
     */
    private function cannotApplyReason(Shift $shift, ?Application $application, ?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        if ($shift->status !== ShiftStatus::Open) {
            return 'shift_closed';
        }

        if ($application) {
            if ($application->status === ApplicationStatus::Rejected) {
                return 'rejected';
            }

            if (in_array($application->status->value, [
                ApplicationStatus::Pending->value,
                ApplicationStatus::Approved->value,
            ], true)) {
                return 'already_applied';
            }
        }

        if ($shift->starts_at && $shift->ends_at) {
            $hasOverlap = $user->applications()
                ->whereIn('status', [
                    ApplicationStatus::Pending->value,
                    ApplicationStatus::Approved->value,
                ])
                ->whereHas('shift', function ($query) use ($shift) {
                    $query
                        ->whereKeyNot($shift->id)
                        ->whereNotNull('starts_at')
                        ->whereNotNull('ends_at')
                        ->where('starts_at', '<', $shift->ends_at)
                        ->where('ends_at', '>', $shift->starts_at);
                })
                ->exists();

            if ($hasOverlap) {
                return 'overlap';
            }
        }

        return null;
    }

    private function applicationsByShift(Event $event, ?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        $shiftIds = $event->zones
            ->flatMap(fn ($zone) => $zone->shifts->pluck('id'))
            ->values();

        if ($shiftIds->isEmpty()) {
            return collect();
        }

        return $user->applications()
            ->whereIn('shift_id', $shiftIds)
            ->whereIn('status', [
                ApplicationStatus::Pending,
                ApplicationStatus::Approved,
                ApplicationStatus::Rejected,
            ])
            ->get()
            ->keyBy('shift_id');
    }
}
