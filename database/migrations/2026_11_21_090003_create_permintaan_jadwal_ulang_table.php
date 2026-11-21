<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahan: requests to move one session to another date and time, from
     * the parent or the tutor of the order. The other side approves or
     * rejects; unanswered requests expire when the old time arrives. Approved
     * rows also count the reschedules of a session (at most two).
     */
    public function up(): void
    {
        Schema::create('permintaan_jadwal_ulang', function (Blueprint $table) {
            $table->id();
            $table->string('id_sesi', 10);
            $table->string('id_akun', 10);
            $table->dateTime('waktu_lama');
            $table->dateTime('waktu_baru');
            $table->string('alasan');
            $table->string('status', 20)->default('menunggu')->index();
            $table->dateTime('dijawab_pada')->nullable();
            $table->string('alasan_penolakan')->nullable();
            $table->timestamps();

            $table->foreign('id_sesi')->references('id_sesi')->on('sesi_pembelajaran')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('id_akun')->references('id_akun')->on('akun_pengguna')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_jadwal_ulang');
    }
};
