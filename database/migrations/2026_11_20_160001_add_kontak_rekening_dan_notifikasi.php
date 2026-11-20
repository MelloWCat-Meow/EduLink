<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tutor dashboard, notifications and WhatsApp contact. Every column here
     * is "tambahan" (outside the ERD); the reason sits next to each one.
     *
     * Not added: pesanan.alasan_penolakan and a tutor cancellation reason.
     * riwayat_status_pesanan already keeps every status change with its note
     * (the reason) and the account that made it.
     */
    public function up(): void
    {
        Schema::table('akun_pengguna', function (Blueprint $table) {
            // Tambahan: the parent's phone, for the tutor's WhatsApp button on a
            // paid order. Nullable because older accounts signed up without it.
            $table->string('no_hp', 20)->nullable()->after('kontak');
        });

        Schema::table('tutor', function (Blueprint $table) {
            // Tambahan: where payouts go. The account number is stored encrypted
            // (hence text) and shown masked everywhere except the admin payout page.
            $table->string('rekening_bank', 50)->nullable()->after('diajukan_pada');
            $table->text('rekening_nomor')->nullable()->after('rekening_bank');
            $table->string('rekening_atas_nama', 100)->nullable()->after('rekening_nomor');
        });

        Schema::table('sesi_pembelajaran', function (Blueprint $table) {
            // Tambahan: when the day-before reminder went out, so the daily
            // command never sends it twice for the same session.
            $table->dateTime('pengingat_dikirim_pada')->nullable()->after('tautan_meeting');
        });

        // Tambahan: Laravel's database notifications. notifiable_id is a string
        // because account keys look like AK0001, not numbers.
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->string('notifiable_type');
            $table->string('notifiable_id', 10);
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');

        Schema::table('sesi_pembelajaran', function (Blueprint $table) {
            $table->dropColumn('pengingat_dikirim_pada');
        });

        Schema::table('tutor', function (Blueprint $table) {
            $table->dropColumn(['rekening_bank', 'rekening_nomor', 'rekening_atas_nama']);
        });

        Schema::table('akun_pengguna', function (Blueprint $table) {
            $table->dropColumn('no_hp');
        });
    }
};
