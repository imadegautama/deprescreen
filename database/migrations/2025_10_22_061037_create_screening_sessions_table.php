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
        Schema::create('screening_sessions', function (Blueprint $table) {
            $table->id('screening_session_id');
            $table->tinyInteger('score'); // total G1–G8
            $table->string('level'); // rendah/sedang/tinggi
            $table->boolean('has_core')->default(false); // G1&G2?
            $table->boolean('crisis_flag')->default(false); // G9?
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
