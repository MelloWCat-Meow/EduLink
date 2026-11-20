<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pencairan_Dana_Tutor from the ERD: a tutor's payout request for a month.
     */
    public function up(): void
    {
        Schema::create('pencairan_dana_tutor', function (Blueprint $table) {
            // ERD columns.
            $table->string('id_pencairan', 10)->primary();
            $table->string('id_tutor', 10);
            $table->char('periode', 7)->comment('YYYY-MM');
            $table->decimal('nominal', 12, 2);
            $table->string('status', 20)->default('menunggu')->index();

            // Outside the ERD: record timestamps.
            $table->timestamps();

            $table->foreign('id_tutor')->references('id_tutor')->on('tutor')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pencairan_dana_tutor');
    }
};
