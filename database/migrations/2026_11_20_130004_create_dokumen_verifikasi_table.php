<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dokumen_Verifikasi from the ERD. An external tutor is listed in search
     * only when every one of their documents is "valid".
     */
    public function up(): void
    {
        Schema::create('dokumen_verifikasi', function (Blueprint $table) {
            // ERD columns.
            $table->string('id_dokumen', 10)->primary();
            $table->string('id_tutor', 10);
            $table->string('jenis', 20);
            $table->string('berkas');
            $table->string('status_verifikasi', 20)->default('menunggu')->index();

            // Outside the ERD: the admin's reason when a document is rejected,
            // and when it was reviewed.
            $table->string('alasan_penolakan')->nullable();
            $table->dateTime('diperiksa_pada')->nullable();

            // Outside the ERD: record timestamps.
            $table->timestamps();

            $table->foreign('id_tutor')->references('id_tutor')->on('tutor')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_verifikasi');
    }
};
