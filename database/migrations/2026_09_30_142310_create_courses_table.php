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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Unique course code, e.g. REK-01');
            $table->string('name', 255)->comment('Course title/name');
            $table->text('annotation')->comment('Course detailed annotation and syllabus');
            $table->unsignedInteger('duration_hours')->comment('Total duration in academic hours');
            $table->decimal('price', 10, 2)->default(0)->comment('Standard price in CZK');
            $table->boolean('is_accredited')->default(false)->comment('Whether the course has official accreditation');
            $table->string('status', 30)->default('active')->comment('active | archived');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
