<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->decimal('income_tax_percent', 5, 2)->default(0)->after('currency');
            $table->decimal('pension_percent', 5, 2)->default(0)->after('income_tax_percent');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->date('termination_date')->nullable()->after('hire_date');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['income_tax_percent', 'pension_percent']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('termination_date');
        });
    }
};
