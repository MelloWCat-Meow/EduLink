<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Akun_Pengguna from the EduLink TOGAF report (Tabel 3.11 no. 8).
     *
     * Columns outside the ERD are marked below; they exist for the web
     * application only and carry no business meaning in the report.
     */
    public function up(): void
    {
        Schema::create('akun_pengguna', function (Blueprint $table) {
            // ERD columns.
            $table->string('id_akun', 10)->primary();
            $table->string('peran', 20);
            $table->string('kontak', 50)->unique();
            $table->string('kredensial', 255);

            // Outside the ERD: display name so pages can greet the user.
            $table->string('nama', 100);

            // Outside the ERD: Laravel's "remember me" token and record timestamps.
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akun_pengguna');
    }
};
