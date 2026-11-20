<?php

namespace App\Http\Middleware;

use App\Enums\StatusVerifikasiTutor;
use App\Services\PendaftaranTutor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets a tutor account through only while its application has one of the
 * given statuses, e.g. `status-tutor:draf,perlu_perbaikan`. Anyone else is
 * sent to the page that fits their status. Runs after `peran:tutor`.
 */
class EnsureStatusTutor
{
    public function __construct(private PendaftaranTutor $pendaftaran) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$status): Response
    {
        $tutor = $request->user()?->tutor;

        abort_if($tutor === null, Response::HTTP_FORBIDDEN);

        $diizinkan = array_map(fn (string $value): StatusVerifikasiTutor => StatusVerifikasiTutor::from($value), $status);

        if (! in_array($tutor->status_verifikasi, $diizinkan, true)) {
            return redirect()->to($this->pendaftaran->tujuan($tutor));
        }

        return $next($request);
    }
}
