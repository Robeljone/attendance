<?php

namespace App\Actions;

use App\Enums\EmployeeAuditAction;
use App\Enums\EmployeeAuditOutcome;
use App\Models\Employee;
use App\Models\EmployeeAuditLog;
use App\Models\User;
use App\Services\NetworkAllowlist;
use Illuminate\Http\Request;

class RecordEmployeeAuditLogAction
{
    public function __construct(private NetworkAllowlist $networkAllowlist) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function handle(
        EmployeeAuditAction $action,
        EmployeeAuditOutcome $outcome,
        ?Employee $employee = null,
        ?User $user = null,
        ?string $ip = null,
        ?string $message = null,
        array $context = [],
        ?Request $request = null,
    ): EmployeeAuditLog {
        $ipAddress = $ip ?? $request?->ip() ?? '';
        $evaluation = $this->networkAllowlist->evaluate($ipAddress);

        if ($request !== null) {
            $context = array_merge([
                'user_agent' => $request->userAgent(),
                'path' => '/'.$request->path(),
                'method' => $request->method(),
            ], $context);
        }

        return EmployeeAuditLog::query()->create([
            'employee_id' => $employee?->id ?? $user?->employee?->id,
            'user_id' => $user?->id ?? $employee?->user_id,
            'action' => $action,
            'outcome' => $outcome,
            'ip_address' => $ipAddress !== '' ? $ipAddress : null,
            'network_allowed' => $evaluation['on_allowlist'],
            'network_enforced' => $evaluation['enforced'],
            'message' => $message,
            'context' => $context === [] ? null : $context,
        ]);
    }
}
