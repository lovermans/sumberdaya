<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SubFolder
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->path() === '/') {
            $newURI = $request->server->get('REQUEST_URI') . 'index.php';
            $request->server->set('REQUEST_URI', $newURI);
        }

        return $next($request);
    }
}
