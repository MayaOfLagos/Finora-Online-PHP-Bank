<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictAdminFromUserDashboard
{
    /**
     * Handle an incoming request.
     *
     * Redirect admin/super_admin users to the admin panel.
     * They should not access the user dashboard.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Allow access if admin is impersonating a user
        if (session()->has('impersonator_id')) {
            return $next($request);
        }

        // If user is admin or super_admin, redirect to admin panel
        if ($user && $user->role?->isAdminOrHigher()) {
            return redirect('/admin');
        }

        return $next($request);
    }
}
