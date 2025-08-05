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
        Schema::create('pasiens', function (Blueprint $table) {
            $table->id();
            $table->string('no_rm')->unique();
            $table->string('nama_pasien');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->dateTime('tgl_masuk');
            $table->enum('asal_pasien', ['igd', 'poli', 'pindahan']);

            // Cukup simpan ID tempat tidurnya. Info ruangan & kelas bisa didapat dari sini.
            $table->foreignId('tempat_tidur_id')->constrained('tempat_tidurs');

            $table->string('status')->default('aktif');
            $table->dateTime('tgl_keluar')->nullable();
            $table->string('keadaan_keluar')->nullable();
            $table->integer('lama_dirawat')->nullable();
            $table->timestamps();
        });
    }
};
