<?php

namespace App\Http\Middleware;

use App\Services\AlurPesanan;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Frees expired slot holds before a page reads orders or slots, so nobody sees
 * a stale "ditahan" between two runs of the scheduled command.
 */
class LepasPenahananKedaluwarsa
{
    public function __construct(private AlurPesanan $alurPesanan) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->alurPesanan->lepasPenahananKedaluwarsa();

        return $next($request);
    }
}
