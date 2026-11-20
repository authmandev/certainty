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
        Schema::create('study_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained()->restrictOnDelete();
            $table->timestamp('previous_expires_at');
            $table->unsignedSmallInteger('extension_days');
            $table->timestamp('new_expires_at');
            $table->text('reason');
            $table->foreignId('approved_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_extensions');
    }
};
