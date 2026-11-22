<?php

/*
| An empty entry in .env (e.g. "EDULINK_MODE_DEMO=") counts as not set, so
| the documented default applies instead of an empty string.
*/
$isi = fn (string $kunci): mixed => in_array(env($kunci), [null, ''], true) ? null : env($kunci);

/*
| A true/false switch: true/false, 1/0, on/off or yes/no. Empty, missing or
| anything else gives $bawaan.
*/
$saklar = fn (string $kunci, bool $bawaan): bool => ($isi($kunci) === null ? null : filter_var($isi($kunci), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)) ?? $bawaan;

return [

    /*
    |--------------------------------------------------------------------------
    | Mode demo
    |--------------------------------------------------------------------------
    |
    | While true, outside services are simulated so the whole flow can be shown
    | on any host, even with APP_ENV=production: the payment gateway page and
    | its webhook, the tutor and session simulation commands, the shortcut for
    | automatic session confirmation, and activation and password reset links
    | shown on screen.
    |
    | Accepts true/false, 1/0, on/off and yes/no. Empty, missing or anything
    | else falls back to the default: on for local and testing, off elsewhere.
    |
    */

    'mode_demo' => $saklar('EDULINK_MODE_DEMO', in_array(env('APP_ENV', 'production'), ['local', 'testing'], true)),

    /*
    |--------------------------------------------------------------------------
    | Tautan di layar
    |--------------------------------------------------------------------------
    |
    | With mode demo also on, the password reset and tutor activation links
    | are shown on screen as well as emailed, for demos without a real inbox.
    | Off by default (on only for testing): on a public URL such as ngrok,
    | anyone could otherwise take over an account. A reset link for an admin
    | account is never shown. When off, the links are still in the mail log
    | (storage/logs/laravel.log with MAIL_MAILER=log).
    |
    */

    'tautan_di_layar' => $saklar('EDULINK_TAUTAN_DI_LAYAR', env('APP_ENV') === 'testing'),

    /*
    | Shown in the landing and dashboard footers, so viewers know this is a
    | preview build.
    */

    'versi' => 'v0.9 Beta',

    /*
    | Version of the Terms (/syarat-ketentuan) and Privacy Policy
    | (/kebijakan-privasi) a new account agrees to. Change it whenever either
    | document changes; it is stored in akun_pengguna.versi_kebijakan.
    */

    'versi_kebijakan' => '2026-11',

    /*
    | Where tutors ask the academic team for changes only an admin may make,
    | such as their subjects and levels.
    */

    'email_admin' => $isi('EDULINK_EMAIL_ADMIN') ?? 'akademik@edulink.test',

    /*
    | Where account holders send data protection requests (access, correction,
    | deletion). Shown on the Privacy Policy; set the real address before launch.
    */

    'email_privasi' => $isi('EDULINK_EMAIL_PRIVASI') ?? 'privasi@edulink.test',

];
