<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('akun_pengguna', function (Blueprint $table) {
            // Tambahan: when the account holder agreed to the Terms and Privacy
            // Policy, and which version (config edulink.versi_kebijakan), as proof
            // of consent under UU No. 27 Tahun 2022 (Pelindungan Data Pribadi).
            $table->timestamp('persetujuan_pada')->nullable()->after('no_hp');
            $table->string('versi_kebijakan', 10)->nullable()->after('persetujuan_pada');
        });
    }

    public function down(): void
    {
        Schema::table('akun_pengguna', function (Blueprint $table) {
            $table->dropColumn(['persetujuan_pada', 'versi_kebijakan']);
        });
    }
};
