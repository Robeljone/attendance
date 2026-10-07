<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Redirect users who must change their temporary password before using the app.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password && ! $request->routeIs(
            'password.change-required',
            'password.update',
            'logout',
        )) {
            return redirect()->route('password.change-required');
        }

        return $next($request);
    }
}
