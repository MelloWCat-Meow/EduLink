<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mata_Pelajaran and the many-to-many Tutor_Mata_Pelajaran from the ERD.
     * kode_mapel is a readable natural key, e.g. MTK-SMP.
     */
    public function up(): void
    {
        Schema::create('mata_pelajaran', function (Blueprint $table) {
            $table->string('kode_mapel', 10)->primary();
            $table->string('nama', 50);
            $table->string('jenjang', 3);

            $table->unique(['nama', 'jenjang']);
        });

        Schema::create('tutor_mata_pelajaran', function (Blueprint $table) {
            $table->string('id_tutor', 10);
            $table->string('kode_mapel', 10);

            $table->primary(['id_tutor', 'kode_mapel']);

            $table->foreign('id_tutor')->references('id_tutor')->on('tutor')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('kode_mapel')->references('kode_mapel')->on('mata_pelajaran')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutor_mata_pelajaran');
        Schema::dropIfExists('mata_pelajaran');
    }
};
