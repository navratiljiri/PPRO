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
        Schema::create('course_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete()->comment('Reference to parent course');
            $table->datetime('start_date')->comment('Beginning of the term');
            $table->datetime('end_date')->comment('End of the term');
            $table->string('classroom', 100)->comment('Designated classroom / room location');
            $table->unsignedSmallInteger('capacity')->comment('Room / term capacity limit');
            $table->string('status', 30)->default('enrolling')->comment('enrolling | in_progress | completed | cancelled');
            $table->text('note')->nullable()->comment('Organizational note for reception');
            $table->timestamps();

            $table->index(['course_id', 'status']);
            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_terms');
    }
};
