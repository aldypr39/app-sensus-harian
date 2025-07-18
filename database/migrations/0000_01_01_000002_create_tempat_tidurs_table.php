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
        Schema::create('tempat_tidurs', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_tt'); // <-- PASTIKAN BARIS INI ADA
            $table->unsignedBigInteger('ruangan_id');
            $table->string('kelas');
            $table->enum('status', ['tersedia', 'terisi'])->default('tersedia');
            $table->timestamps();

            $table->foreign('ruangan_id')->references('id')->on('ruangans')->onDelete('cascade');
        });
    }
};
