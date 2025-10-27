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
        Schema::create('symptoms', function (Blueprint $table) {
            $table->id('symptom_id'); // Auto increment
            $table->string('code', 4)->unique();   // G1..G9
            $table->string('label');               // Nama gejala
            $table->text('description')->nullable();     // Deskripsi pertanyaan
            $table->boolean('is_core')->default(false);     // Gejala inti (G1, G2)
            $table->boolean('is_sensitive')->default(false); // Gejala sensitif (G9)
            $table->enum('type', ['scale', 'boolean'])->default('scale'); // tipe pertanyaan
            $table->integer('order')->default(0);  // Urutan tampilan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('symptoms');
    }
};
