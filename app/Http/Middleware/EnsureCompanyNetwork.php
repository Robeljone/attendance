<?php

namespace App\Http\Middleware;

use App\Actions\RecordEmployeeAuditLogAction;
use App\Enums\EmployeeAuditAction;
use App\Enums\EmployeeAuditOutcome;
use App\Services\NetworkAllowlist;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyNetwork
{
    public function __construct(
        private NetworkAllowlist $networkAllowlist,
        private RecordEmployeeAuditLogAction $recordEmployeeAuditLog,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip() ?? '';

        if (! $this->networkAllowlist->allows($ip)) {
            $user = $request->user();

            $this->recordEmployeeAuditLog->handle(
                action: EmployeeAuditAction::AttendanceBlocked,
                outcome: EmployeeAuditOutcome::Blocked,
                employee: $user?->employee,
                user: $user,
                ip: $ip,
                message: 'Blocked: not on company network.',
                context: [
                    'route' => $request->route()?->getName(),
                ],
                request: $request,
            );

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
