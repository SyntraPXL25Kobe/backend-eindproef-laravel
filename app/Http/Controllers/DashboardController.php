<?php

namespace App\Http\Controllers;

use App\EventStatus;
use App\EventVisibility;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    private const STATUS_COLUMN = 'status';

    private const VISIBILITY_COLUMN = 'publication_visibility';

    private const START_DATE_COLUMN = 'start_date';

    public function __invoke(Request $request): Response
    {
        return Inertia::render('dashboard', [
            'publicEvents' => $request->user()?->hasRole('coordinator')
                ? []
                : Event::query()
                    ->with('coordinatorProfile')
                    ->where(self::STATUS_COLUMN, EventStatus::Published)
                    ->where(self::VISIBILITY_COLUMN, EventVisibility::Public)
                    ->orderBy(self::START_DATE_COLUMN)
                    ->limit(12)
                    ->get()
                    ->map(fn (Event $event) => [
                        'id' => $event->id,
                        'title' => $event->title,
                        'description' => $event->description,
                        'location' => $event->location,
                        'start_date' => $event->start_date?->toDateString(),
                        'end_date' => $event->end_date?->toDateString(),
                        'max_crew_members' => $event->max_crew_members,
                        'cover_image_url' => $event->cover_image_url,
                        'coordinator_name' => $event->coordinatorProfile?->organisation_name,
                        'show_url' => route('events.public.show', ['event' => $event->getKey()]),
                    ]),
        ]);
    }
}
