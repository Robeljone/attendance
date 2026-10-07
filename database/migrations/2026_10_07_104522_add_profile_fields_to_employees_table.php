<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('notes');
            $table->string('gender')->nullable()->after('photo_path');
            $table->string('marital_status')->nullable()->after('gender');
            $table->string('nationality')->nullable()->after('marital_status');
            $table->string('national_id')->nullable()->after('nationality');
            $table->string('tax_id')->nullable()->after('national_id');
            $table->string('personal_email')->nullable()->after('tax_id');
            $table->string('blood_group')->nullable()->after('personal_email');
            $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_phone');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'photo_path',
                'gender',
                'marital_status',
                'nationality',
                'national_id',
                'tax_id',
                'personal_email',
                'blood_group',
                'emergency_contact_relationship',
            ]);
        });
    }
};
