<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class KonfigurasiSoket
{
    public function handle(Request $request, Closure $next)
    {
        $con = config();

        return $next($request);
    }
}
