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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_term_id')->constrained('course_terms')->cascadeOnDelete()->comment('Reference to course term');
            $table->foreignId('lecturer_id')->constrained('lecturers')->cascadeOnDelete()->comment('Assigned lecturer for this session');
            $table->datetime('starts_at')->comment('Date and start time of the lesson');
            $table->datetime('ends_at')->comment('Date and end time of the lesson');
            $table->unsignedSmallInteger('duration_hours')->comment('Duration in academic hours');
            $table->string('topic', 255)->comment('Lesson topic / curriculum unit');
            $table->timestamps();

            $table->index(['course_term_id', 'starts_at']);
            $table->index(['lecturer_id', 'starts_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
