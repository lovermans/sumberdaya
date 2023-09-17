<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RingkasHTML
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ((strtolower(strtok($response->headers->get('Content-Type'), ';')) === 'text/html')) {
            $html = $response->getContent();

            $cspHeader = "default-src 'self';style-src 'self' 'sha256-dqBsqjGdRxpoUKczu4jMO60qqbU00ssW14SzomoBs78=';script-src 'self' 'unsafe-hashes' 'unsafe-eval' 'sha256-18gd7wHgIeacD0TOmyzrY/Ixn+v2aTVEe7Qf+knExWw=' 'nonce-{$request->session()->get('sesiNonce')}' 'strict-dynamic';img-src * data:";

            if (strpos($html, '<pre>') !== false) {
                $replace = [
                    "/<\?php/" => '<?php ',
                    "/\r/" => '',
                    "/>\n</" => '><',
                    "/>\s+\n</" => '><',
                    "/>\n\s+</" => '><',
                ];
            } else {
                $response->headers->set('Content-Security-Policy', $cspHeader);
                $replace = [
                    '/<!--[^\[](.*?)[^\]]-->/s' => '',
                    "/<\?php/" => '<?php ',
                    "/\n([\S])/" => '$1',
                    "/\r/" => '',
                    "/\n/" => '',
                    "/\t/" => '',
                    '/ +/' => ' ',
                ];
            }

            $new_buffer = preg_replace(array_keys($replace), array_values($replace), $html);

            $response->setContent($new_buffer);

            $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Access-Control-Allow-Origin', $request->host());

        return $response;
    }
}
