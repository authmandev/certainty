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
        Schema::create('study_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('version');
            $table->json('input_snapshot');
            $table->json('calculation_snapshot');
            $table->string('calculation_version');
            $table->foreignId('finalized_by')->constrained('users')->restrictOnDelete();
            $table->string('disk');
            $table->string('pdf_path')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['study_id', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_reports');
    }
};
