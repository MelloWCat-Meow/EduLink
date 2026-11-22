<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Simulated outside services (payment gateway, webhook) exist only while
 * config edulink.mode_demo is on; otherwise the route answers 404.
 */
class PastikanModeDemo
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('edulink.mode_demo'), Response::HTTP_NOT_FOUND);

        return $next($request);
    }
}
