<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Siswa from the EduLink TOGAF report (Tabel 3.11 no. 4).
     * One guardian account (Akun_Pengguna) has many students (Tabel 3.12).
     */
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->string('id_siswa', 10)->primary();
            $table->string('id_akun', 10);
            $table->string('nama', 100);
            $table->string('jenjang', 20);
            $table->string('kontak_wali', 50);

            // Outside the ERD: record timestamps.
            $table->timestamps();

            // Deleting a guardian account must not silently remove student records.
            $table->foreign('id_akun')
                ->references('id_akun')
                ->on('akun_pengguna')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
