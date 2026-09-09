<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountNotSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->suspended_at) {
            auth()->guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'Account sospeso.');
        }

        return $next($request);
    }
}
