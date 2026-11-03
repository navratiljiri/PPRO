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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete()->comment('Enrolled student registration');
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete()->comment('Course term lesson');
            $table->boolean('is_present')->default(false)->comment('Student presence boolean flag');
            $table->string('note', 255)->nullable()->comment('Lecturer note or justification');
            $table->timestamps();

            $table->unique(['registration_id', 'lesson_id']);
            $table->index(['lesson_id', 'is_present']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
