<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bonus_run_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bonus_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('label')->nullable();
            $table->timestamps();

            $table->unique(['bonus_run_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_run_items');
    }
};
