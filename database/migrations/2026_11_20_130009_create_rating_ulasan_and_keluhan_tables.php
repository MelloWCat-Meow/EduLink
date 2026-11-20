<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rating_Ulasan and Keluhan from the ERD.
     */
    public function up(): void
    {
        Schema::create('rating_ulasan', function (Blueprint $table) {
            // ERD columns. One review per order at most.
            $table->string('id_ulasan', 10)->primary();
            $table->string('id_pesanan', 15)->unique();
            $table->unsignedTinyInteger('nilai');
            $table->text('komentar')->nullable();

            // Outside the ERD: admin moderation. Hidden reviews do not count
            // toward the tutor's average rating.
            $table->boolean('ditampilkan')->default(true);
            $table->string('alasan_moderasi')->nullable();

            // Outside the ERD: record timestamps.
            $table->timestamps();

            $table->foreign('id_pesanan')->references('id_pesanan')->on('pesanan')->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('keluhan', function (Blueprint $table) {
            // ERD columns.
            $table->string('id_keluhan', 10)->primary();
            $table->string('id_pesanan', 15);
            $table->string('jenis', 30);
            $table->string('status', 20)->default('baru')->index();

            // Outside the ERD: who filed it, what happened, and the admin's reply
            // that the parent can read.
            $table->string('id_akun', 10);
            $table->text('deskripsi');
            $table->text('tanggapan_admin')->nullable();

            // Outside the ERD: record timestamps.
            $table->timestamps();

            $table->foreign('id_pesanan')->references('id_pesanan')->on('pesanan')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_akun')->references('id_akun')->on('akun_pengguna')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keluhan');
        Schema::dropIfExists('rating_ulasan');
    }
};
