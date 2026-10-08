<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pay_component_salary_structure', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_structure_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pay_component_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['salary_structure_id', 'pay_component_id'], 'structure_component_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pay_component_salary_structure');
    }
};
