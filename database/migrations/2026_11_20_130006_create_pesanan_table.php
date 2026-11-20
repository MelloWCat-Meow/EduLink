<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pesanan from the ERD. id_pesanan uses the report's format ORD-2026-000045.
     */
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            // ERD columns.
            $table->string('id_pesanan', 15)->primary();
            $table->string('id_siswa', 10);
            $table->string('id_tutor', 10);
            $table->string('kode_mapel', 10);
            $table->string('id_jadwal', 10);
            $table->unsignedSmallInteger('durasi')->comment('menit');
            $table->string('mode', 20);
            $table->string('status', 30)->index();

            // Outside the ERD: a slot is only "day + time" every week, but a session
            // needs a real date. Sessions are created weekly from tanggal_mulai,
            // jumlah_pertemuan times.
            $table->date('tanggal_mulai');
            $table->unsignedTinyInteger('jumlah_pertemuan');

            // Outside the ERD: the hourly rate and total at booking time, so a later
            // rate change by the tutor does not change what the parent pays.
            // total_biaya = tarif_per_jam x (durasi / 60) x jumlah_pertemuan.
            $table->decimal('tarif_per_jam', 12, 2);
            $table->decimal('total_biaya', 12, 2);

            // Outside the ERD: deadline of the slot hold while waiting for the tutor
            // or for payment. Null once the order no longer holds the slot.
            $table->dateTime('ditahan_hingga')->nullable()->index();

            // Outside the ERD: record timestamps.
            $table->timestamps();

            $table->foreign('id_siswa')->references('id_siswa')->on('siswa')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_tutor')->references('id_tutor')->on('tutor')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('kode_mapel')->references('kode_mapel')->on('mata_pelajaran')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_jadwal')->references('id_jadwal')->on('jadwal_ketersediaan')->cascadeOnUpdate()->restrictOnDelete();
        });

        // Outside the ERD: every status change with its time, who made it and why.
        // Feeds the order timeline and keeps the admin's cancellation reason.
        Schema::create('riwayat_status_pesanan', function (Blueprint $table) {
            $table->id();
            $table->string('id_pesanan', 15);
            $table->string('status', 30);
            $table->string('catatan')->nullable();
            $table->string('id_akun', 10)->nullable();
            $table->dateTime('dicatat_pada');

            $table->foreign('id_pesanan')->references('id_pesanan')->on('pesanan')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('id_akun')->references('id_akun')->on('akun_pengguna')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_status_pesanan');
        Schema::dropIfExists('pesanan');
    }
};
