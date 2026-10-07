<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->timestamp('finalized_at')->nullable()->after('processed_at');
        });

        DB::table('payroll_periods')
            ->whereIn('status', ['processed', 'paid', 'completed'])
            ->update([
                'status' => 'finalized',
                'finalized_at' => DB::raw('COALESCE(processed_at, CURRENT_TIMESTAMP)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->dropColumn('finalized_at');
        });
    }
};
