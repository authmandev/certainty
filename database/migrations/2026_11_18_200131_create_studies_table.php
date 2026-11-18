<?php

use App\Enums\StudyStatus;
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
        Schema::create('studies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('reference')->nullable()->unique();
            $table->string('title');
            $table->string('project_name');
            $table->string('status')->default(StudyStatus::Draft->value)->index();
            $table->timestamp('expires_at');
            $table->timestamp('finalized_at')->nullable();
            $table->unsignedInteger('current_version')->default(0);
            $table->string('calculation_version');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('studies');
    }
};
