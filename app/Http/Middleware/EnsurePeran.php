<?php

namespace App\Http\Middleware;

use App\Enums\Peran;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets a signed-in account through only when it has one of the given roles,
 * e.g. `peran:admin`. Guests are handled earlier by the auth middleware.
 */
class EnsurePeran
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $allowed = array_map(fn (string $value): Peran => Peran::from($value), $peran);

        abort_unless(in_array($request->user()?->peran, $allowed, true), Response::HTTP_FORBIDDEN);

        return $next($request);
    }
}
