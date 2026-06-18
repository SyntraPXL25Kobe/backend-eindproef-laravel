<?php

namespace App\Http\Controllers;

use App\Http\Requests\CoordinatorEvents\StoreShiftRequest;
use App\Http\Requests\CoordinatorEvents\UpdateShiftRequest;
use App\Models\Shift;
use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class CoordinatorShiftController extends Controller
{
    public function store(StoreShiftRequest $request, Zone $zone): RedirectResponse
    {
        $zone->shifts()->create($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Shift toegevoegd.',
        ]);

        return to_route('coordinator.events.edit', ['event' => $zone->event_id]);
    }

    public function update(UpdateShiftRequest $request, Shift $shift): RedirectResponse
    {
        $shift->update($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Shift bijgewerkt.',
        ]);

        return to_route('coordinator.events.edit', ['event' => $shift->zone->event_id]);
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        $this->authorize('delete', $shift);

        $eventId = $shift->zone->event_id;
        $shift->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Shift verwijderd.',
        ]);

        return to_route('coordinator.events.edit', ['event' => $eventId]);
    }
}