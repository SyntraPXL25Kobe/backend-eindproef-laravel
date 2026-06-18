<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectAdminToFilament
{
    /**
     * Keep admins inside Filament by redirecting non-admin paths.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isAdmin = $request->user()?->hasRole('admin');
        $isFilamentPath = $request->is('admin') || $request->is('admin/*');
        $isNavigationRequest = $request->isMethodSafe()
            && ! $request->expectsJson()
            && ! $request->ajax()
            && ! $request->hasHeader('X-Inertia')
            && ! $request->hasHeader('X-Livewire');

        if ($isAdmin && ! $isFilamentPath && $isNavigationRequest) {
            return redirect()->route('filament.admin.pages.dashboard');
        }

        return $next($request);
    }
}
