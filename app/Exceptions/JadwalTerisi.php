<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Thrown when a parent tries to book a weekly slot that another order already holds.
 */
class JadwalTerisi extends RuntimeException
{
    public const KODE = 'JADWAL_TERISI';

    public function __construct()
    {
        parent::__construct('Jadwal sudah terisi. Pilih jam lain yang masih tersedia.');
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['kode' => self::KODE, 'pesan' => $this->getMessage()], 409);
        }

        return back()
            ->withInput()
            ->withErrors(['id_jadwal' => $this->getMessage()])
            ->with('kode_galat', self::KODE);
    }
}
