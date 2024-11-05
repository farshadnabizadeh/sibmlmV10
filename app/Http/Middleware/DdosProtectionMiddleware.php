<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class DdosProtectionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Generate a unique key for the client based on IP address
        $key = 'ddos-protection:' . $request->ip();

        // Define rate limits: 60 requests per minute in this example
        $maxAttempts = 60;
        $decayMinutes = 1;

        // Check if the request limit has been exceeded
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'Too many requests. Please slow down.'
            ], 429); // HTTP status 429 (Too Many Requests)
        }

        // Increment the request count
        RateLimiter::hit($key, $decayMinutes * 60);

        return $next($request);
    }
}
