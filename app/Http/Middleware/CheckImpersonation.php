<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckImpersonation
{
    public function handle(Request $request, Closure $next): Response
    {
        // Add impersonation indicator to view
        if (session()->has('impersonator_id')) {
            view()->share('isImpersonating', true);
            view()->share('impersonatorId', session('impersonator_id'));
        }

        return $next($request);
    }
}
