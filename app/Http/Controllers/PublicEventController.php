<?php

namespace App\Http\Controllers;

use App\EventStatus;
use App\EventVisibility;
use App\Models\Event;
use Inertia\Inertia;
use Inertia\Response;

class PublicEventController extends Controller
{
    public function show(Event $event): Response
    {
        abort_unless(
            $event->status === EventStatus::Published
                && $event->publication_visibility === EventVisibility::Public,
            404,
        );

        return Inertia::render('events/show', [
            'event' => $this->payload($event),
            'isInvitation' => false,
        ]);
    }

    public function showInvite(string $token): Response
    {
        $event = Event::query()
            ->where(['invite_token' => $token])
            ->firstOrFail();

        abort_unless(
            $event->status === EventStatus::Published
                && $event->publication_visibility === EventVisibility::InviteOnly,
            404,
        );

        return Inertia::render('events/show', [
            'event' => $this->payload($event),
            'isInvitation' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Event $event): array
    {
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
        ];
    }
}