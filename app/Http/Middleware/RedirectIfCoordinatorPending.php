<?php

namespace App\Http\Middleware;

use App\CoordinatorRegistrationStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfCoordinatorPending
{
    /**
     * Redirect authenticated users with pending coordinator status.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->coordinator_registration_status !== CoordinatorRegistrationStatus::Pending) {
            return $next($request);
        }

        if ($request->routeIs('register.coordinator.pending')) {
            return $next($request);
        }

        return redirect()->route('register.coordinator.pending');
    }
}
