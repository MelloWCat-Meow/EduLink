<?php

namespace App\Exceptions;

use App\Enums\StatusPesanan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Thrown when an order is asked to move to a status its current status does not allow.
 */
class TransisiStatusTidakSah extends RuntimeException
{
    public const KODE = 'TRANSISI_TIDAK_SAH';

    public static function dari(StatusPesanan $dari, StatusPesanan $ke): self
    {
        return new self("Pesanan berstatus \"{$dari->label()}\" tidak bisa diubah menjadi \"{$ke->label()}\".");
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['kode' => self::KODE, 'pesan' => $this->getMessage()], 409);
        }

        return back()->with('galat', $this->getMessage())->with('kode_galat', self::KODE);
    }
}
