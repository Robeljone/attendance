<?php

namespace App\Models;

use App\Enums\EmployeeDocumentType;
use Database\Factories\EmployeeDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EmployeeDocument extends Model
{
    /** @use HasFactory<EmployeeDocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'type',
        'title',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'document_number',
        'issued_on',
        'expires_on',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => EmployeeDocumentType::class,
            'file_size' => 'integer',
            'issued_on' => 'date',
            'expires_on' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_on !== null && $this->expires_on->isPast();
    }

    public function deleteFile(): void
    {
        if (filled($this->file_path)) {
            Storage::disk('local')->delete($this->file_path);
        }
    }
}
