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

            $ringkasHTML = [
                '/<!--[^\[](.*?)[^\]]-->/s' => '',
                "/<\?php/"  => '<?php ',
            ];

            if (strpos($html, '<pre>') !== false) {
                $replace = [
                    ...$ringkasHTML,
                    "/\r/" => '',
                    "/>\n</" => '><',
                    "/>\s+\n</" => '><',
                    "/>\n\s+</" => '><',
                ];
            } else {
                $replace = [
                    ...$ringkasHTML,
                    "/\n([\S])/" => '$1',
                    "/\r/" => '',
                    "/\n/" => '',
                    "/\t/" => '',
                    "/ +/" => ' ',
                ];
            }

            $new_buffer = preg_replace(array_keys($replace), array_values($replace), $html);

            $response->setContent($new_buffer);
        }

        return $response;
    }
}
