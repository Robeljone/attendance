<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->json('excluded_employee_ids')->nullable()->after('status');
            $table->timestamp('submitted_at')->nullable()->after('finalized_at');
            $table->timestamp('paid_at')->nullable()->after('submitted_at');
            $table->foreignId('approved_by')->nullable()->after('generated_by')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['excluded_employee_ids', 'submitted_at', 'paid_at']);
        });
    }
};
