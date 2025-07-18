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
            $table->foreignId('ruangan_id')->constrained('ruangans')->onDelete('cascade');
            $table->string('kelas');
            $table->string('no_tt');
            $table->enum('status', ['aktif', 'keluar']);
            $table->dateTime('tgl_keluar')->nullable();
            $table->enum('keadaan_keluar', ['pulang', 'aps', 'pindah', 'dirujuk', 'meninggal'])->nullable();
            $table->integer('lama_dirawat')->nullable();
            $table->timestamps();

            
        });
    }
};
