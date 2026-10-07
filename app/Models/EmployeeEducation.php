<?php

namespace App\Models;

use App\Enums\EducationLevel;
use Database\Factories\EmployeeEducationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeEducation extends Model
{
    /** @use HasFactory<EmployeeEducationFactory> */
    use HasFactory;

    protected $table = 'employee_educations';

    protected $fillable = [
        'employee_id',
        'institution',
        'level',
        'field_of_study',
        'degree_title',
        'start_year',
        'end_year',
        'grade',
        'is_highest',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'level' => EducationLevel::class,
            'start_year' => 'integer',
            'end_year' => 'integer',
            'is_highest' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
