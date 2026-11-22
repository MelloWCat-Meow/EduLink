<?php

/*
| An empty entry in .env (e.g. "EDULINK_MODE_DEMO=") counts as not set, so
| the documented default applies instead of an empty string.
*/
$isi = fn (string $kunci): mixed => in_array(env($kunci), [null, ''], true) ? null : env($kunci);

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

    'mode_demo' => ($isi('EDULINK_MODE_DEMO') === null ? null : filter_var($isi('EDULINK_MODE_DEMO'), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE))
        ?? in_array(env('APP_ENV', 'production'), ['local', 'testing'], true),

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
