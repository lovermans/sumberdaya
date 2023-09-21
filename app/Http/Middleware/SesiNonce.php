<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SesiNonce
{
    public function handle(Request $request, Closure $next)
    {
        $request->session()->put('sesiNonce', Str::random(32));

        return $next($request);
    }
}
