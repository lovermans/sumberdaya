<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SesiNonce
{
    public function handle(Request $request, Closure $next)
    {
        $request->session()->put('sesiNonce', '1234');

        return $next($request);
    }
}
