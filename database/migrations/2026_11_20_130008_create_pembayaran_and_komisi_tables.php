<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pembayaran and Komisi_Biaya_Layanan from the ERD. An order may have several
     * payment attempts but at most one successful one, and exactly one commission.
     */
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            // ERD columns.
            $table->string('id_pembayaran', 10)->primary();
            $table->string('id_pesanan', 15);
            $table->string('metode', 20);
            $table->decimal('jumlah', 12, 2);
            $table->string('status', 20)->default('pending')->index();

            // Outside the ERD: when the payment gateway reported the final result.
            $table->dateTime('diproses_pada')->nullable()->index();

            // Outside the ERD: record timestamps.
            $table->timestamps();

            $table->foreign('id_pesanan')->references('id_pesanan')->on('pesanan')->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('komisi_biaya_layanan', function (Blueprint $table) {
            // ERD columns.
            $table->string('id_komisi', 10)->primary();
            $table->string('id_pesanan', 15)->unique();
            $table->decimal('persentase', 5, 2);
            $table->decimal('nominal', 12, 2);

            // Outside the ERD: record timestamps.
            $table->timestamps();

            $table->foreign('id_pesanan')->references('id_pesanan')->on('pesanan')->cascadeOnUpdate()->restrictOnDelete();
        });

        // Outside the ERD: platform settings, e.g. the commission percentage.
        Schema::create('pengaturan', function (Blueprint $table) {
            $table->string('kunci', 50)->primary();
            $table->string('nilai');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan');
        Schema::dropIfExists('komisi_biaya_layanan');
        Schema::dropIfExists('pembayaran');
    }
};
