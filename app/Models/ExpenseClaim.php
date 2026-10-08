<?php

namespace App\Models;

use App\Enums\ExpenseClaimStatus;
use Database\Factories\ExpenseClaimFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ExpenseClaim extends Model
{
    /** @use HasFactory<ExpenseClaimFactory> */
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'category',
        'title',
        'description',
        'amount',
        'expense_date',
        'receipt_path',
        'receipt_original_name',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'payslip_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
            'status' => ExpenseClaimStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(Payslip::class);
    }

    public function hasReceipt(): bool
    {
        return filled($this->receipt_path);
    }

    public function deleteReceipt(): void
    {
        if (filled($this->receipt_path)) {
            Storage::disk('local')->delete($this->receipt_path);
        }
    }
}
