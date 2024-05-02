<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckForwardedHost
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $forwardedHost = $request->header('X-Forwarded-Host');
        $gatewayUrl = config('app.gateway_url');

        if ($forwardedHost === null || $forwardedHost !== 'http://host.docker.internal:81') {
            return response()->json([
                'message' => 'Unauthorized Access',
                'details' => 'Unauthorized Access, you should pass through the API gateway'
            ], 401);
        }

        return $next($request);
    }
}
