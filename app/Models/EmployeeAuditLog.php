<?php

namespace App\Models;

use App\Enums\EmployeeAuditAction;
use App\Enums\EmployeeAuditOutcome;
use Database\Factories\EmployeeAuditLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAuditLog extends Model
{
    /** @use HasFactory<EmployeeAuditLogFactory> */
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'user_id',
        'action',
        'outcome',
        'ip_address',
        'network_allowed',
        'network_enforced',
        'message',
        'context',
    ];

    protected function casts(): array
    {
        return [
            'action' => EmployeeAuditAction::class,
            'outcome' => EmployeeAuditOutcome::class,
            'network_allowed' => 'boolean',
            'network_enforced' => 'boolean',
            'context' => 'array',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
