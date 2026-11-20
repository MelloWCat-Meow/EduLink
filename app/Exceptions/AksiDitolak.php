<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * An admin action the record's current state does not allow. Shown as a flash message.
 */
class AksiDitolak extends RuntimeException
{
    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['pesan' => $this->getMessage()], 409);
        }

        return back()->with('galat', $this->getMessage());
    }
}
