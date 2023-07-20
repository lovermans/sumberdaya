<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ShareAppClass extends ShareErrorsFromSession
{
    public function handle($request, Closure $next)
    {
        $appRangka = app();

        $this->view->share([
            'appRangka' => $appRangka,
            'rekRangka' => $request,
            'confRangka' => $appRangka->config,
            'urlRangka' => $appRangka->url,
            'storageRangka' => $appRangka->filesystem,
            'mixRangka' => $appRangka->make('Illuminate\Foundation\Mix'),
            'dateRangka' => $appRangka->date,
            'strRangka' => str(),
            // 'userRangka' => $request->user()
        ]);

        return $next($request);
    }
}
