<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lecturers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100)->comment('Lecturer first name');
            $table->string('last_name', 100)->comment('Lecturer surname');
            $table->string('email', 191)->unique()->comment('Contact email');
            $table->string('phone', 50)->nullable()->comment('Phone number');
            $table->string('specialization', 255)->nullable()->comment('Field of expertise / specialization');
            $table->text('bio')->nullable()->comment('Short professional biography');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecturers');
    }
};
