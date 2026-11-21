<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('akun_pengguna', function (Blueprint $table) {
            // Tambahan: an admin can switch a parent or tutor account off. An
            // inactive account cannot sign in, is signed out on its next request,
            // and an inactive tutor disappears from search.
            $table->boolean('aktif')->default(true)->after('peran');
            $table->dateTime('dinonaktifkan_pada')->nullable()->after('aktif');
            $table->string('alasan_nonaktif')->nullable()->after('dinonaktifkan_pada');
        });

        // Tambahan: every switch off and on, with who did it, when and why.
        // The account row only keeps the current state.
        Schema::create('riwayat_status_akun', function (Blueprint $table) {
            $table->id();
            $table->string('id_akun', 10);
            $table->boolean('aktif');
            $table->string('alasan');
            $table->string('oleh', 10)->nullable();
            $table->dateTime('dicatat_pada');

            $table->foreign('id_akun')->references('id_akun')->on('akun_pengguna')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('oleh')->references('id_akun')->on('akun_pengguna')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_status_akun');

        Schema::table('akun_pengguna', function (Blueprint $table) {
            $table->dropColumn(['aktif', 'dinonaktifkan_pada', 'alasan_nonaktif']);
        });
    }
};
