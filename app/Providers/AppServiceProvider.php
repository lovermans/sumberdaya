<?php

namespace App\Providers;

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
        $appRangka = $this->app;
        $rekRangka = $appRangka->request;
        $confRangka = $appRangka->config;
        $urlRangka = $appRangka->url;
        $storageRangka = $appRangka->filesystem;
        $mixRangka = $appRangka->make('Illuminate\Foundation\Mix');
        $dateRangka = $appRangka->date;
        $strRangka = str();

        $data = [
            'appRangka' => $appRangka,
            'rekRangka' => $rekRangka,
            'confRangka' => $confRangka,
            'urlRangka' => $urlRangka,
            'storageRangka' => $storageRangka,
            'mixRangka' => $mixRangka,
            'dateRangka' => $dateRangka,
            'strRangka' => $strRangka
        ];

        $appRangka->view->composer('*', function ($view) use ($data) {
            $view->with($data)->with('userRangka', auth()->user());
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
