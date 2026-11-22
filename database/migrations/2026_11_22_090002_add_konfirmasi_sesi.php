<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sessions are confirmed by the parent before the tutor is paid for them,
     * and refunds are tied to the sessions they cover.
     */
    public function up(): void
    {
        Schema::table('sesi_pembelajaran', function (Blueprint $table) {
            // Tambahan: where the parent's confirmation stands once the tutor logs the
            // session: menunggu_konfirmasi, dikonfirmasi, dikonfirmasi_otomatis,
            // disanggah, tidak_sah. Null while the session is not logged yet.
            $table->string('status_konfirmasi', 30)->nullable()->index()->after('catatan');
            // Tambahan: when the tutor logged it; the 48-hour confirmation window starts here.
            $table->dateTime('dicatat_pada')->nullable()->after('status_konfirmasi');
            // Tambahan: when and by whom it was settled as held (ortu, sistem, admin);
            // the tutor's share becomes available and commission is recognised then.
            $table->dateTime('dikonfirmasi_pada')->nullable()->after('dicatat_pada');
            $table->string('dikonfirmasi_oleh', 10)->nullable()->after('dikonfirmasi_pada');
            // Tambahan: the parent's reason when disputing, and the admin's ruling.
            $table->string('alasan_sanggahan')->nullable()->after('dikonfirmasi_oleh');
            $table->text('keputusan_admin')->nullable()->after('alasan_sanggahan');
            // Tambahan: the refund that pays this session back to the parent (not
            // held before a cancellation, or ruled not valid). One refund may
            // cover several sessions.
            $table->string('id_pengembalian', 10)->nullable()->after('keputusan_admin');

            $table->foreign('id_pengembalian')->references('id_pengembalian')->on('pengembalian_dana')->cascadeOnUpdate()->nullOnDelete();
        });

        Schema::table('keluhan', function (Blueprint $table) {
            // Tambahan: the session a dispute is about; set for complaints the system
            // opens when a parent disputes a session.
            $table->string('id_sesi', 10)->nullable()->after('id_pesanan');

            $table->foreign('id_sesi')->references('id_sesi')->on('sesi_pembelajaran')->cascadeOnUpdate()->nullOnDelete();
        });

        // Sessions logged before this change count as confirmed.
        DB::table('sesi_pembelajaran')->whereNotNull('kehadiran')->update([
            'status_konfirmasi' => 'dikonfirmasi_otomatis',
            'dicatat_pada' => DB::raw('updated_at'),
            'dikonfirmasi_pada' => DB::raw('updated_at'),
            'dikonfirmasi_oleh' => 'sistem',
        ]);

        // Existing refunds covered the sessions not logged when the order was cancelled.
        foreach (DB::table('pengembalian_dana')->get(['id_pengembalian', 'id_pesanan']) as $pengembalian) {
            DB::table('sesi_pembelajaran')->where('id_pesanan', $pengembalian->id_pesanan)->whereNull('kehadiran')
                ->update(['id_pengembalian' => $pengembalian->id_pengembalian]);
        }

        Schema::table('pengembalian_dana', function (Blueprint $table) {
            // An order may now get more than one refund (each ruling, then a cancellation).
            $table->dropForeign(['id_pesanan']);
            $table->dropUnique(['id_pesanan']);
            $table->dropColumn('sesi_terlaksana');
            $table->foreign('id_pesanan')->references('id_pesanan')->on('pesanan')->cascadeOnUpdate()->restrictOnDelete();
        });

        // Commission stays at its paid-time amount; what is recognised is worked
        // out per confirmed session, so the lowered copy is no longer needed.
        DB::table('komisi_biaya_layanan')->whereNotNull('nominal_sebelum_pengembalian')->update(['nominal' => DB::raw('nominal_sebelum_pengembalian')]);

        Schema::table('komisi_biaya_layanan', function (Blueprint $table) {
            $table->dropColumn('nominal_sebelum_pengembalian');
        });
    }

    public function down(): void
    {
        Schema::table('komisi_biaya_layanan', function (Blueprint $table) {
            $table->decimal('nominal_sebelum_pengembalian', 12, 2)->nullable()->after('nominal');
        });

        Schema::table('pengembalian_dana', function (Blueprint $table) {
            $table->unsignedTinyInteger('sesi_terlaksana')->default(0)->after('nominal');
        });

        Schema::table('keluhan', function (Blueprint $table) {
            $table->dropForeign(['id_sesi']);
            $table->dropColumn('id_sesi');
        });

        Schema::table('sesi_pembelajaran', function (Blueprint $table) {
            $table->dropForeign(['id_pengembalian']);
            $table->dropColumn(['status_konfirmasi', 'dicatat_pada', 'dikonfirmasi_pada', 'dikonfirmasi_oleh', 'alasan_sanggahan', 'keputusan_admin', 'id_pengembalian']);
        });
    }
};
