<?php

namespace App\Http\Controllers;

use App\Http\Requests\CoordinatorEvents\StoreZoneRequest;
use App\Http\Requests\CoordinatorEvents\UpdateZoneRequest;
use App\Models\Event;
use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class CoordinatorZoneController extends Controller
{
    public function store(StoreZoneRequest $request, Event $event): RedirectResponse
    {
        $event->zones()->create($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Zone toegevoegd.',
        ]);

        return to_route('coordinator.events.edit', ['event' => $event->getKey()]);
    }

    public function update(UpdateZoneRequest $request, Zone $zone): RedirectResponse
    {
        $zone->update($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Zone bijgewerkt.',
        ]);

        return to_route('coordinator.events.edit', ['event' => $zone->event_id]);
    }

    public function destroy(Zone $zone): RedirectResponse
    {
        $this->authorize('delete', $zone);

        $eventId = $zone->event_id;
        $zone->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Zone verwijderd.',
        ]);

        return to_route('coordinator.events.edit', ['event' => $eventId]);
    }
}