<?php

namespace App\Http\Controllers;

use App\EventStatus;
use App\EventVisibility;
use App\Http\Requests\CoordinatorEvents\PublishCoordinatorEventRequest;
use App\Http\Requests\CoordinatorEvents\StoreCoordinatorEventRequest;
use App\Http\Requests\CoordinatorEvents\UpdateCoordinatorEventRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CoordinatorEventController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('create', Event::class);

        $events = $request->user()
            ->coordinatorProfile
            ->events()
            ->latest('start_date')
            ->get()
            ->map(fn (Event $event) => $this->eventListItem($event));

        return Inertia::render('app/events/index', [
            'events' => $events,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Event::class);

        return Inertia::render('app/events/create', [
            'visibilityOptions' => $this->visibilityOptions(),
        ]);
    }

    public function store(StoreCoordinatorEventRequest $request): RedirectResponse
    {
        $event = $request->user()
            ->coordinatorProfile
            ->events()
            ->create([
                ...$request->validated(),
                'status' => EventStatus::Draft,
            ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Event opgeslagen als concept.',
        ]);

        return to_route('coordinator.events.edit', ['event' => $event->getKey()]);
    }

    public function edit(Event $event): Response
    {
        $this->authorize('update', $event);

        return Inertia::render('app/events/edit', [
            'event' => $this->eventDetail($event->fresh()),
            'visibilityOptions' => $this->visibilityOptions(),
        ]);
    }

    public function update(UpdateCoordinatorEventRequest $request, Event $event): RedirectResponse
    {
        $event->fill($request->validated());
        $event->syncPublicationAccess();
        $event->save();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Event bijgewerkt.',
        ]);

        return to_route('coordinator.events.edit', ['event' => $event->getKey()]);
    }

    public function publish(PublishCoordinatorEventRequest $request, Event $event): RedirectResponse
    {
        $event->publish(EventVisibility::from($request->validated('publication_visibility')));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $event->publication_visibility === EventVisibility::Public
                ? 'Event publiek gepubliceerd.'
                : 'Event gepubliceerd met een unieke crew-uitnodigingslink.',
        ]);

        return to_route('coordinator.events.edit', ['event' => $event->getKey()]);
    }

    /**
     * @return array<string, mixed>
     */
    private function eventListItem(Event $event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'location' => $event->location,
            'start_date' => $event->start_date?->toDateString(),
            'end_date' => $event->end_date?->toDateString(),
            'status' => $event->status->value,
            'publication_visibility' => $event->publication_visibility->value,
            'published_at' => $event->published_at?->toIso8601String(),
            'edit_url' => route('coordinator.events.edit', ['event' => $event->getKey()]),
            'public_url' => $event->isPublished() && $event->publication_visibility === EventVisibility::Public
                ? route('events.public.show', ['event' => $event->getKey()])
                : null,
            'invite_url' => $event->isPublished() && $event->publication_visibility === EventVisibility::InviteOnly && filled($event->invite_token)
                ? route('events.invite.show', ['token' => $event->invite_token])
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function eventDetail(Event $event): array
    {
        return [
            ...$this->eventListItem($event),
            'description' => $event->description,
            'max_crew_members' => $event->max_crew_members,
            'cover_image_url' => $event->cover_image_url,
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function visibilityOptions(): array
    {
        return [
            [
                'value' => EventVisibility::Public->value,
                'label' => 'Publiek',
                'description' => 'Iedereen met de publieke eventpagina kan het event bekijken.',
            ],
            [
                'value' => EventVisibility::InviteOnly->value,
                'label' => 'Invite-only',
                'description' => 'Alleen crew members met de unieke uitnodigingslink krijgen toegang.',
            ],
        ];
    }
}
