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
        Schema::create('answers', function (Blueprint $table) {
            $table->id('answers_id');
            $table->foreignId('symptom_id')->constrained('symptoms', 'symptom_id')->cascadeOnDelete();
            $table->foreignId('screening_session_id')->constrained('screening_sessions', 'screening_session_id')->cascadeOnDelete();
            $table->tinyInteger('value'); // 0/1/2 atau 0/1
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
