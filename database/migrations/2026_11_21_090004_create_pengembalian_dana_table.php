<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahan: money owed back to the parent when the admin cancels a paid
        // order, for the sessions that did not take place. Created automatically
        // (perlu_diproses); the admin transfers by hand and marks it selesai.
        Schema::create('pengembalian_dana', function (Blueprint $table) {
            $table->string('id_pengembalian', 10)->primary();
            $table->string('id_pesanan', 15)->unique();
            $table->decimal('nominal', 12, 2);
            // Sessions already held when the order was cancelled; the tutor keeps
            // the income (minus commission) for these.
            $table->unsignedTinyInteger('sesi_terlaksana');
            $table->string('status', 20)->default('perlu_diproses')->index();
            $table->string('catatan')->nullable();
            $table->string('diproses_oleh', 10)->nullable();
            $table->dateTime('diproses_pada')->nullable();
            $table->timestamps();

            $table->foreign('id_pesanan')->references('id_pesanan')->on('pesanan')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('diproses_oleh')->references('id_akun')->on('akun_pengguna')->cascadeOnUpdate()->nullOnDelete();
        });

        Schema::table('komisi_biaya_layanan', function (Blueprint $table) {
            // Tambahan: the commission as first charged, kept when a refund lowers
            // "nominal" to the sessions actually held (same percentage).
            $table->decimal('nominal_sebelum_pengembalian', 12, 2)->nullable()->after('nominal');
        });
    }

    public function down(): void
    {
        Schema::table('komisi_biaya_layanan', function (Blueprint $table) {
            $table->dropColumn('nominal_sebelum_pengembalian');
        });

        Schema::dropIfExists('pengembalian_dana');
    }
};
