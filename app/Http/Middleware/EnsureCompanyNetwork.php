<?php

namespace App\Http\Middleware;

use App\Services\NetworkAllowlist;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyNetwork
{
    public function __construct(private NetworkAllowlist $networkAllowlist) {}

    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip() ?? '';

        if (! $this->networkAllowlist->allows($ip)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You must be connected to the company Wi-Fi / network to use attendance.',
                ], 403);
            }

            abort(403, 'You must be connected to the company Wi-Fi / network to clock in or scan QR codes.');
        }

        return $next($request);
    }
}
