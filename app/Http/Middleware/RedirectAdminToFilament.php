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
        if ($request->user()?->hasRole('admin') && ! $request->is('admin*')) {
            return redirect('/admin');
        }

        return $next($request);
    }
}
