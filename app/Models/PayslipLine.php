<?php

namespace App\Models;

use App\Enums\PayslipLineType;
use Database\Factories\PayslipLineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayslipLine extends Model
{
    /** @use HasFactory<PayslipLineFactory> */
    use HasFactory;

    protected $fillable = [
        'payslip_id',
        'type',
        'code',
        'label',
        'amount',
        'is_manual',
        'is_taxable',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => PayslipLineType::class,
            'amount' => 'decimal:2',
            'is_manual' => 'boolean',
            'is_taxable' => 'boolean',
        ];
    }

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(Payslip::class);
    }
}
