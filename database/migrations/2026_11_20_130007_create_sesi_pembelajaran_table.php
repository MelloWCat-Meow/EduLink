<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sesi_Pembelajaran from the ERD. One order has many sessions, created
     * when the order is paid. kehadiran stays null until the session is logged.
     */
    public function up(): void
    {
        Schema::create('sesi_pembelajaran', function (Blueprint $table) {
            // ERD columns.
            $table->string('id_sesi', 10)->primary();
            $table->string('id_pesanan', 15);
            $table->dateTime('waktu')->index();
            $table->string('kehadiran', 20)->nullable();
            $table->text('catatan')->nullable();

            // Outside the ERD: meeting link for online sessions (a dummy link
            // until a video meeting service is connected).
            $table->string('tautan_meeting')->nullable();

            // Outside the ERD: record timestamps.
            $table->timestamps();

            $table->foreign('id_pesanan')->references('id_pesanan')->on('pesanan')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesi_pembelajaran');
    }
};
