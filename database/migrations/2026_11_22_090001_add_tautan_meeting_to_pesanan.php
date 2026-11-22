<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            // Tambahan: the tutor's own meeting link (Google Meet, Zoom, ...) for an
            // online order, used by every session unless a session has its own
            // (sesi_pembelajaran.tautan_meeting). EduLink runs no video service.
            $table->string('tautan_meeting')->nullable()->after('ditahan_hingga');
        });

        // Links used to be generated as placeholders; tutors fill them in now.
        DB::table('sesi_pembelajaran')->where('tautan_meeting', 'like', 'https://meet.edulink.test/%')->update(['tautan_meeting' => null]);
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn('tautan_meeting');
        });
    }
};
