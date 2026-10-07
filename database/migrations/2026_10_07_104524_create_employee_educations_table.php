<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('institution');
            $table->string('level');
            $table->string('field_of_study')->nullable();
            $table->string('degree_title')->nullable();
            $table->unsignedSmallInteger('start_year')->nullable();
            $table->unsignedSmallInteger('end_year')->nullable();
            $table->string('grade')->nullable();
            $table->boolean('is_highest')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'is_highest']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_educations');
    }
};
