<?php

namespace App\Support;

/**
 * Whether a password reset or tutor activation link may be shown on screen
 * besides being emailed: only in mode demo with EDULINK_TAUTAN_DI_LAYAR on.
 * Admin reset links are kept off screen by KataSandi itself.
 */
final class TautanDiLayar
{
    public static function aktif(): bool
    {
        return config('edulink.mode_demo') && config('edulink.tautan_di_layar');
    }
}
