<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('tagline')->nullable()->after('company_name');
            $table->string('logo_path')->nullable()->after('tagline');
            $table->string('favicon_path')->nullable()->after('logo_path');
            $table->string('primary_color', 7)->nullable()->after('favicon_path');
            $table->string('support_email')->nullable()->after('primary_color');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'tagline',
                'logo_path',
                'favicon_path',
                'primary_color',
                'support_email',
            ]);
        });
    }
};
