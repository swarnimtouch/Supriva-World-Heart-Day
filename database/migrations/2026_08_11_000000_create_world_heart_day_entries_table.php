<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_heart_day_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('source_row')->nullable();
            $table->string('employee_name')->nullable();
            $table->string('employee_code')->nullable()->index();
            $table->string('doctor_name');
            $table->string('msl_code')->unique();
            $table->string('speciality')->nullable()->index();
            $table->text('photo_url')->nullable();
            $table->string('photo_path')->nullable();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_heart_day_entries');
    }
};
