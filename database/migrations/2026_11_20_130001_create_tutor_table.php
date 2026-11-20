<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tutor from the EduLink ERD. Internal tutors may have no sign-in account
     * yet, so id_akun is nullable but still unique (one account, one tutor).
     */
    public function up(): void
    {
        Schema::create('tutor', function (Blueprint $table) {
            // ERD columns.
            $table->string('id_tutor', 10)->primary();
            $table->string('id_akun', 10)->nullable()->unique();
            $table->string('nama', 100);
            $table->string('status', 20);
            $table->string('kontak', 50);

            // Outside the ERD: record timestamps.
            $table->timestamps();

            $table->foreign('id_akun')->references('id_akun')->on('akun_pengguna')
                ->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutor');
    }
};
