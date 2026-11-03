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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->unique()->constrained('registrations')->restrictOnDelete()->comment('Completed registration');
            $table->string('certificate_number', 50)->unique()->comment('Unique immutable certificate number (e.g. AT-2026-00001)');
            $table->date('issue_date')->comment('Date of certificate issuance');
            $table->boolean('lecturer_approved')->default(true)->comment('Formal pedagogical approval by lecturer');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
