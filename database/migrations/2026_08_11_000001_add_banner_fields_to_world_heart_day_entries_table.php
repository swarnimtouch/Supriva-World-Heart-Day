<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('world_heart_day_entries', function (Blueprint $table) {
            $table->string('gender', 20)->nullable()->after('speciality');
            $table->string('banner_path')->nullable()->after('photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('world_heart_day_entries', function (Blueprint $table) {
            $table->dropColumn(['gender', 'banner_path']);
        });
    }
};
