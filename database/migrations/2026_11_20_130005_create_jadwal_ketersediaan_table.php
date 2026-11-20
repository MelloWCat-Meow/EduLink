<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jadwal_Ketersediaan from the ERD: a weekly slot (day + start time).
     * status is tersedia, ditahan (held by an unpaid order) or terisi.
     */
    public function up(): void
    {
        Schema::create('jadwal_ketersediaan', function (Blueprint $table) {
            // ERD columns.
            $table->string('id_jadwal', 10)->primary();
            $table->string('id_tutor', 10);
            $table->string('hari', 10);
            $table->time('jam');
            $table->string('status', 20)->default('tersedia');

            // Outside the ERD: record timestamps.
            $table->timestamps();

            $table->unique(['id_tutor', 'hari', 'jam']);
            $table->foreign('id_tutor')->references('id_tutor')->on('tutor')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_ketersediaan');
    }
};
