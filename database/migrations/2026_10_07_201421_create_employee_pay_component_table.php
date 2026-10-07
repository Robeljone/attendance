<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_pay_component', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pay_component_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['employee_id', 'pay_component_id']);
        });

        $now = now();

        $housingId = DB::table('pay_components')->insertGetId([
            'name' => 'Housing allowance',
            'code' => 'housing_allowance',
            'type' => 'earning',
            'calculation' => 'fixed',
            'default_amount' => 0,
            'is_taxable' => true,
            'is_active' => true,
            'sort_order' => 10,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $transportId = DB::table('pay_components')->insertGetId([
            'name' => 'Transport allowance',
            'code' => 'transport_allowance',
            'type' => 'earning',
            'calculation' => 'fixed',
            'default_amount' => 0,
            'is_taxable' => true,
            'is_active' => true,
            'sort_order' => 20,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $employees = DB::table('employees')->select('id', 'housing_allowance', 'transport_allowance')->get();

        foreach ($employees as $employee) {
            if ((float) $employee->housing_allowance > 0) {
                DB::table('employee_pay_component')->insert([
                    'employee_id' => $employee->id,
                    'pay_component_id' => $housingId,
                    'amount' => $employee->housing_allowance,
                    'is_enabled' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            if ((float) $employee->transport_allowance > 0) {
                DB::table('employee_pay_component')->insert([
                    'employee_id' => $employee->id,
                    'pay_component_id' => $transportId,
                    'amount' => $employee->transport_allowance,
                    'is_enabled' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_pay_component');
    }
};
