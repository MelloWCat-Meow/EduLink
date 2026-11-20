<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tutor sign-up and verification. Every column here is "tambahan" (outside
     * the ERD); the reason sits next to each one.
     */
    public function up(): void
    {
        Schema::table('tutor', function (Blueprint $table) {
            // Tambahan: tutor.kontak holds the email the tutor signs in with, so the
            // phone number needs its own column.
            $table->string('no_hp', 20)->nullable()->after('kontak');

            // Tambahan: where the whole application stands
            // (draf / menunggu / perlu_perbaikan / ditolak / terverifikasi).
            // dokumen_verifikasi.status_verifikasi stays per document; search only
            // lists tutors whose application is terverifikasi.
            $table->string('status_verifikasi', 20)->default('draf')->index()->after('status');

            // Tambahan: the admin's note the tutor can read (reason for rejection or
            // what to fix).
            $table->text('catatan_verifikasi')->nullable()->after('status_verifikasi');

            // Tambahan: sections the admin asked the tutor to fix, as
            // {"profil": "catatan", "jadwal": "catatan"}. A section drops out once the
            // tutor saves it again, so the form knows what is still waiting.
            $table->json('bagian_perbaikan')->nullable()->after('catatan_verifikasi');

            // Tambahan: when the application was last sent, so the admin queue is
            // oldest first.
            $table->dateTime('diajukan_pada')->nullable()->after('bagian_perbaikan');
        });

        Schema::table('profil_tutor', function (Blueprint $table) {
            // Tambahan: highest education, kept apart from kompetensi because the
            // form asks for it separately: {"pendidikan": "S1", "institusi": "...", "jurusan": "..."}.
            $table->json('riwayat_pendidikan')->nullable()->after('id_tutor');

            // ERD columns made nullable: an internal tutor's profile is created from
            // the company's data before the tutor fills in a rate and district.
            $table->decimal('tarif', 12, 2)->nullable()->change();
            $table->string('lokasi', 50)->nullable()->change();
        });

        // Tambahan: every step of a verification (sent, approved, fix requested,
        // rejected) with who, when and the note. The tutor row only keeps the latest.
        Schema::create('riwayat_verifikasi_tutor', function (Blueprint $table) {
            $table->id();
            $table->string('id_tutor', 10);
            $table->string('status', 20);
            $table->text('catatan')->nullable();
            $table->json('bagian')->nullable();
            $table->string('id_akun', 10)->nullable();
            $table->dateTime('dicatat_pada');

            $table->foreign('id_tutor')->references('id_tutor')->on('tutor')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('id_akun')->references('id_akun')->on('akun_pengguna')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_verifikasi_tutor');

        Schema::table('profil_tutor', function (Blueprint $table) {
            $table->dropColumn('riwayat_pendidikan');
            $table->decimal('tarif', 12, 2)->nullable(false)->change();
            $table->string('lokasi', 50)->nullable(false)->change();
        });

        Schema::table('tutor', function (Blueprint $table) {
            $table->dropIndex(['status_verifikasi']);
            $table->dropColumn(['no_hp', 'status_verifikasi', 'catatan_verifikasi', 'bagian_perbaikan', 'diajukan_pada']);
        });
    }
};
