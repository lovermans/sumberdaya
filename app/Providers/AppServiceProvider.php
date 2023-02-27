<?php

namespace App\Providers;

use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) || !in_array(request()->getHost(), ['localhost', '192.168.113.222'])) {URL::forceScheme('https');}

        $appRangka = app();
        $rekRangka = $appRangka->request;
        $urlRangka = $appRangka->url;
        $storageRangka = $appRangka->filesystem;

        $data = [
            'appRangka' => $appRangka,
            'rekRangka' => $rekRangka,
            'confRangka' => $appRangka->config,
            'urlRangka' => $urlRangka,
            'storageRangka' => $storageRangka,
            'mixRangka' => $appRangka->make('Illuminate\Foundation\Mix'),
            'dateRangka' => $appRangka->date,
            'strRangka' => str(),
        ];

        $appRangka->view->composer('*', function ($view) use ($data, $rekRangka) {
            $view->with($data)
            ->with('userRangka', $rekRangka->user())
            ;
        });

        $storageRangka->disk('local')->buildTemporaryUrlsUsing(function ($berkas, $expiration, $options) use ($urlRangka) {
            return $urlRangka->temporarySignedRoute(
                'unduh.panduan',
                $expiration,
                array_merge($options, ['berkas' => $berkas])
            );
        });
    }
}
