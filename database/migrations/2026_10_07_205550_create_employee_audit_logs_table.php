<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 40);
            $table->string('outcome', 20);
            $table->string('ip_address', 45)->nullable();
            $table->boolean('network_allowed')->default(false);
            $table->boolean('network_enforced')->default(false);
            $table->string('message', 255)->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['created_at']);
            $table->index(['employee_id', 'created_at']);
            $table->index(['action', 'outcome']);
            $table->index(['network_allowed', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_audit_logs');
    }
};
