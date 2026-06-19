<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Enums\EventVisibility;
use App\Enums\ShiftStatus;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $isCoordinator = $request->user()?->can('create', Event::class) ?? false;

        $publicEvents = Event::query()
            ->with('coordinatorProfile')
            ->where([
                ['status', '=', EventStatus::Published->value],
                ['publication_visibility', '=', EventVisibility::Public->value],
            ])
            ->where(function ($query) {
                $query
                    ->whereDate('end_date', '>=', today())
                    ->orWhereHas('shifts', function ($shiftQuery) {
                        $shiftQuery->whereNotIn('status', [
                            ShiftStatus::Closed->value,
                            ShiftStatus::Full->value,
                        ]);
                    });
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhereHas('coordinatorProfile', function ($profileQuery) use ($search) {
                            $profileQuery->where('organisation_name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('start_date')
            ->limit(24)
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
            ]);

        return Inertia::render('dashboard', [
            'publicEvents' => $isCoordinator
                ? []
                : $publicEvents,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }
}
