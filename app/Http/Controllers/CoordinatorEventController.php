<?php

namespace App\Http\Controllers;

use App\ApplicationStatus;
use App\EventStatus;
use App\EventVisibility;
use App\Http\Requests\CoordinatorEvents\PublishCoordinatorEventRequest;
use App\Http\Requests\CoordinatorEvents\StoreCoordinatorEventRequest;
use App\Http\Requests\CoordinatorEvents\UpdateCoordinatorEventRequest;
use App\Models\Application;
use App\Models\Event;
use App\Models\Skill;
use App\ShiftStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

        // Load all relationships once — do NOT call ->fresh() afterwards (that drops eager loads)
        $event->load(['zones.shifts.requiredSkill']);

        // Fetch all reviewable applications once; crew overview is derived from this collection
        $applications = $this->loadApplications($event);

        return Inertia::render('app/events/edit', [
            'event' => $this->eventDetail($event),
            'applications' => $this->formatApplications($applications),
            'crewMembers' => $this->formatCrewMembers($applications),
            'visibilityOptions' => $this->visibilityOptions(),
            'skillOptions' => Skill::query()
                ->orderBy('name')
                ->get()
                ->map(fn (Skill $skill) => [
                    'value' => $skill->id,
                    'label' => $skill->name,
                ])->values(),
            'shiftStatusOptions' => collect(ShiftStatus::cases())
                ->map(fn (ShiftStatus $status) => [
                    'value' => $status->value,
                    'label' => ucfirst($status->value),
                ])->values(),
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
            'dashboard_url' => route('coordinator.events.dashboard', ['event' => $event->getKey()]),
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
            'zones' => $event->zones->map(fn ($zone) => [
                'id' => $zone->id,
                'name' => $zone->name,
                'description' => $zone->description,
                'shifts' => $zone->shifts
                    ->sortBy('starts_at')
                    ->values()
                    ->map(fn ($shift) => [
                        'id' => $shift->id,
                        'title' => $shift->title,
                        'description' => $shift->description,
                        'starts_at' => $shift->starts_at?->format('Y-m-d\TH:i'),
                        'ends_at' => $shift->ends_at?->format('Y-m-d\TH:i'),
                        'capacity' => $shift->capacity,
                        'status' => $shift->status->value,
                        'required_skill_id' => $shift->required_skill_id,
                        'required_skill_name' => $shift->requiredSkill?->name,
                    ]),
            ])->values(),
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

    /**
     * Fetch all reviewable applications for an event in a single query.
     *
     * @return Collection<int, Application>
     */
    private function loadApplications(Event $event): Collection
    {
        return Application::query()
            ->whereIn('status', [
                ApplicationStatus::Pending->value,
                ApplicationStatus::Approved->value,
                ApplicationStatus::Rejected->value,
            ])
            ->whereHas('shift.zone', fn ($query) => $query->where('event_id', $event->id))
            ->with([
                'user:id,name,email,phone',
                'shift.zone:id,event_id,name',
            ])
            ->latest()
            ->get();
    }

    /**
     * @param  Collection<int, Application>  $applications
     * @return array<int, array<string, mixed>>
     */
    private function formatApplications(Collection $applications): array
    {
        // Compute approved counts from the in-memory collection — avoids a second DB query
        $approvedCountsByShift = $applications
            ->where('status', ApplicationStatus::Approved)
            ->groupBy('shift_id')
            ->map->count();

        return $applications
            ->map(fn (Application $application) => [
                'id' => $application->id,
                'status' => $application->status->value,
                'motivation' => $application->motivation,
                'created_at' => $application->created_at?->toIso8601String(),
                'reviewed_at' => $application->reviewed_at?->toIso8601String(),
                'user' => [
                    'id' => $application->user->id,
                    'name' => $application->user->name,
                    'email' => $application->user->email,
                    'phone' => $application->user->phone,
                ],
                'zone' => [
                    'id' => $application->shift->zone->id,
                    'name' => $application->shift->zone->name,
                ],
                'shift' => [
                    'id' => $application->shift->id,
                    'title' => $application->shift->title,
                    'starts_at' => $application->shift->starts_at?->toIso8601String(),
                    'ends_at' => $application->shift->ends_at?->toIso8601String(),
                    'capacity' => $application->shift->capacity,
                    'approved_count' => (int) ($approvedCountsByShift[$application->shift_id] ?? 0),
                ],
            ])
            ->values()
            ->all();
    }

    /**
     * Derive crew overview from already-loaded applications — no extra DB query.
     *
     * @param  Collection<int, Application>  $applications
     * @return array<int, array<string, mixed>>
     */
    private function formatCrewMembers(Collection $applications): array
    {
        return $applications
            ->where('status', ApplicationStatus::Approved)
            ->groupBy('user_id')
            ->map(function ($userApplications) {
                $user = $userApplications->first()->user;

                $shifts = $userApplications
                    ->sortBy(fn (Application $application) => $application->shift->starts_at?->getTimestamp() ?? 0)
                    ->values()
                    ->map(fn (Application $application) => [
                        'application_id' => $application->id,
                        'shift_id' => $application->shift->id,
                        'title' => $application->shift->title,
                        'zone_name' => $application->shift->zone->name,
                        'starts_at' => $application->shift->starts_at?->toIso8601String(),
                        'ends_at' => $application->shift->ends_at?->toIso8601String(),
                    ]);

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'approved_shifts_count' => $shifts->count(),
                    'shifts' => $shifts,
                ];
            })
            ->sortBy(fn (array $crewMember) => $crewMember['name'])
            ->values()
            ->all();
    }
}
