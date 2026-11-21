<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mode demo
    |--------------------------------------------------------------------------
    |
    | While true, outside services are simulated so the whole flow can be shown
    | on any host, even with APP_ENV=production: the payment gateway page and
    | its webhook, the tutor and session simulation commands, and activation
    | and password reset links shown on screen.
    | Defaults to on for local and testing, off everywhere else.
    |
    */

    /*
    | Version of the Terms (/syarat-ketentuan) and Privacy Policy
    | (/kebijakan-privasi) a new account agrees to. Change it whenever either
    | document changes; it is stored in akun_pengguna.versi_kebijakan.
    */

    'versi_kebijakan' => '2026-11',

    /*
    | Where account holders send data protection requests (access, correction,
    | deletion). Shown on the Privacy Policy; set the real address before launch.
    */

    /*
    | Where tutors ask the academic team for changes only an admin may make,
    | such as their subjects and levels.
    */

    'email_admin' => env('EDULINK_EMAIL_ADMIN', 'akademik@edulink.test'),

    'email_privasi' => env('EDULINK_EMAIL_PRIVASI', 'privasi@edulink.test'),

    'mode_demo' => (bool) env('EDULINK_MODE_DEMO', in_array(env('APP_ENV', 'production'), ['local', 'testing'], true)),

];
