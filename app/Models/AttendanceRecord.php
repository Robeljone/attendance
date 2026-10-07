<?php

namespace App\Models;

use App\Enums\AttendanceMethod;
use Database\Factories\AttendanceRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    /** @use HasFactory<AttendanceRecordFactory> */
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'work_date',
        'clock_in_at',
        'clock_out_at',
        'clock_in_method',
        'clock_out_method',
        'clock_in_ip',
        'clock_out_ip',
        'worked_minutes',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'clock_in_at' => 'datetime',
            'clock_out_at' => 'datetime',
            'clock_in_method' => AttendanceMethod::class,
            'clock_out_method' => AttendanceMethod::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function isOpen(): bool
    {
        return $this->clock_in_at !== null && $this->clock_out_at === null;
    }
}
