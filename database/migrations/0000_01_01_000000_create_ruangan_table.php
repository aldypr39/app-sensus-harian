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
    Schema::create('ruangans', function (Blueprint $table) {
        $table->id();                   // Membuat kolom ID otomatis
        $table->string('gedung');       // Membuat kolom untuk nama gedung
        $table->string('lantai');       // Membuat kolom untuk nama lantai
        $table->string('nama_ruangan'); // Membuat kolom untuk nama ruangan
        $table->timestamps();           // Membuat kolom created_at & updated_at
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ruangan');
    }
};
