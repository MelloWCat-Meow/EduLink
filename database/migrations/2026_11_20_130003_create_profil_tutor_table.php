<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Profil_Tutor from the ERD, one profile per tutor. tarif is per hour.
     */
    public function up(): void
    {
        Schema::create('profil_tutor', function (Blueprint $table) {
            // ERD columns.
            $table->string('id_profil', 10)->primary();
            $table->string('id_tutor', 10)->unique();
            $table->text('kompetensi');
            $table->text('pengalaman');
            $table->decimal('tarif', 12, 2);
            $table->string('lokasi', 50)->index();
            $table->string('foto')->nullable();

            // Outside the ERD: which learning modes the tutor offers, so a parent
            // cannot book a home visit from an online-only tutor.
            $table->boolean('melayani_online')->default(true);
            $table->boolean('melayani_kunjungan')->default(true);

            // Outside the ERD: record timestamps.
            $table->timestamps();

            $table->foreign('id_tutor')->references('id_tutor')->on('tutor')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profil_tutor');
    }
};
