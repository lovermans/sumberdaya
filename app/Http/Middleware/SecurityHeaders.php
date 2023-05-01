<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ((strtolower(strtok($response->headers->get('Content-Type'), ';')) === 'text/html')
            //  || (is_object($response) && $response instanceof Response)
        ) {
            $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('Content-Security-Policy', "default-src 'self' 'unsafe-inline';img-src 'self' * data:");
            // $response->headers->remove('X-Powered-By');
            // $response->headers->remove('Server');
        }

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Access-Control-Allow-Origin', $request->host());

        return $response;
    }
}
