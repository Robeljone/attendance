<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslip_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payslip_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('code');
            $table->string('label');
            $table->decimal('amount', 12, 2)->default(0);
            $table->boolean('is_manual')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['payslip_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslip_lines');
    }
};
