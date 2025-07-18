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
        Schema::create('kelas_perawatans', function (Blueprint $table) {
            $table->id();
            // Foreign key yang terhubung ke tabel ruangan
            $table->foreignId('ruangan_id')->constrained('ruangans')->onDelete('cascade');
            $table->string('nama_kelas');   // Kolom untuk nama kelas (misal: "VIP")
            $table->integer('jumlah_tt');   // Kolom untuk jumlah tempat tidur
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas_perawatans');
    }
};
