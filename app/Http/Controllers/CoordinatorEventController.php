<?php

namespace App\Http\Controllers;

use App\EventStatus;
use App\EventVisibility;
use App\ApplicationStatus;
use App\Http\Requests\CoordinatorEvents\PublishCoordinatorEventRequest;
use App\Http\Requests\CoordinatorEvents\StoreCoordinatorEventRequest;
use App\Http\Requests\CoordinatorEvents\UpdateCoordinatorEventRequest;
use App\Models\Application;
use App\Models\Event;
use App\Models\Skill;
use App\ShiftStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CoordinatorEventController extends Controller
{
    private const EVENT_START_DATE_COLUMN = 'start_date';

    private const EVENT_ID_COLUMN = 'event_id';

    private const SKILL_NAME_COLUMN = 'name';

    private const STATUS_COLUMN = 'status';

    private const SHIFT_ID_COLUMN = 'shift_id';

    private const SHIFT_STARTS_AT_COLUMN = 'starts_at';

    public function index(Request $request): Response
    {
        $this->authorize('create', Event::class);

        $events = $request->user()
            ->coordinatorProfile
            ->events()
            ->latest(self::EVENT_START_DATE_COLUMN)
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

        $event->load(['zones.shifts.requiredSkill']);

        return Inertia::render('app/events/edit', [
            'event' => $this->eventDetail($event->fresh()),
            'pendingApplications' => $this->pendingApplications($event),
            'visibilityOptions' => $this->visibilityOptions(),
            'skillOptions' => Skill::query()
                ->orderBy(self::SKILL_NAME_COLUMN)
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
                    ->sortBy(self::SHIFT_STARTS_AT_COLUMN)
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
     * @return array<int, array<string, mixed>>
     */
    private function pendingApplications(Event $event): array
    {
        $applications = Application::query()
            ->where(self::STATUS_COLUMN, ApplicationStatus::Pending->value)
            ->whereHas('shift.zone', fn ($query) => $query->where(self::EVENT_ID_COLUMN, $event->id))
            ->with([
                'user:id,name,email,phone',
                'shift.zone:id,event_id,name',
            ])
            ->latest()
            ->get();

        $approvedCountsByShift = Application::query()
            ->where(self::STATUS_COLUMN, ApplicationStatus::Approved->value)
            ->whereIn(self::SHIFT_ID_COLUMN, $applications->pluck(self::SHIFT_ID_COLUMN)->unique()->values())
            ->selectRaw('shift_id, COUNT(*) as aggregate')
            ->groupBy(self::SHIFT_ID_COLUMN)
            ->pluck('aggregate', self::SHIFT_ID_COLUMN);

        return $applications
            ->map(fn (Application $application) => [
                'id' => $application->id,
                'status' => $application->status->value,
                'motivation' => $application->motivation,
                'created_at' => $application->created_at?->toIso8601String(),
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
}
