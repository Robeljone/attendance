<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->decimal('standard_work_hours_per_day', 5, 2)->default(8)->after('pension_percent');
            $table->decimal('overtime_weekday_multiplier', 5, 2)->default(1.5)->after('standard_work_hours_per_day');
            $table->decimal('overtime_weekend_multiplier', 5, 2)->default(2)->after('overtime_weekday_multiplier');
            $table->unsignedSmallInteger('late_grace_minutes')->default(15)->after('overtime_weekend_multiplier');
            $table->decimal('late_penalty_per_occurrence', 12, 2)->default(0)->after('late_grace_minutes');
            $table->boolean('deduct_unexcused_absence')->default(true)->after('late_penalty_per_occurrence');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'standard_work_hours_per_day',
                'overtime_weekday_multiplier',
                'overtime_weekend_multiplier',
                'late_grace_minutes',
                'late_penalty_per_occurrence',
                'deduct_unexcused_absence',
            ]);
        });
    }
};
