<?php

namespace App\Http\Controllers;

use App\ApplicationStatus;
use App\Models\Application;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShiftApplicationController extends Controller
{
    public function store(Request $request, Shift $shift): RedirectResponse
    {
        $this->authorize('store', [Application::class, $shift]);

        $request->validate([
            'motivation' => ['nullable', 'string'],
        ]);

        $request->user()->applications()->create([
            'shift_id' => $shift->id,
            'status' => ApplicationStatus::Pending,
            'motivation' => $request->string('motivation')->toString() ?: null,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Je inschrijving voor deze shift werd verstuurd.',
        ]);

        return back();
    }

    public function destroy(Application $application): RedirectResponse
    {
        $this->authorize('cancel', $application);

        $application->update([
            'status' => ApplicationStatus::Cancelled,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Je inschrijving werd geannuleerd.',
        ]);

        return back();
    }
}