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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_term_id')->constrained('course_terms')->cascadeOnDelete()->comment('Target course term');
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete()->comment('Enrolled student');
            $table->string('status', 30)->default('registered')->comment('Status: registered, waiting_list, offered, cancelled, completed');
            $table->unsignedInteger('waiting_order')->nullable()->comment('FIFO queue sequence number for waiting list');
            $table->datetime('offer_expires_at')->nullable()->comment('Offer acceptance deadline for promoted waitlister');
            $table->text('cancellation_reason')->nullable()->comment('Audit reason if cancelled/rejected');
            $table->timestamps();

            $table->index(['course_term_id', 'status']);
            $table->index(['student_id', 'status']);
            $table->index(['course_term_id', 'waiting_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
