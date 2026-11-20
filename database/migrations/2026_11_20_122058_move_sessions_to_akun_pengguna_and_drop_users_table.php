<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Akun_Pengguna replaces Laravel's default users table for login.
     *
     * Sessions store the authenticated account's id_akun (e.g. "AK0001"),
     * so user_id becomes a string. The users and password_reset_tokens
     * tables are dropped: nothing reads them, and password reset keyed
     * by users.email does not exist in this application.
     */
    public function up(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->string('user_id', 10)->nullable()->change();
        });

        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }

    /**
     * Restores the empty default tables. Accounts created in akun_pengguna are not copied back.
     */
    public function down(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }
};
