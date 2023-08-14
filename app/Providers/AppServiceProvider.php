<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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

        // $appRangka = app();
        // $urlRangka = $appRangka->url;

        // $data = [
        //     'mixRangka' => $appRangka->make('Illuminate\Foundation\Mix'),
        //     'strRangka' => str(),
        //     // 'sesiRangka' => $appRangka->session
        // ];

        // $appRangka->view->composer('*', function ($view) use ($data) {
        //     $view->with($data)
        //         // ->with('userRangka', $rekRangka->user())
        //         // ->with('sesiRangka', $appRangka->session)
        //     ;
        // });

        $this->app->filesystem->disk('local')->buildTemporaryUrlsUsing(function ($berkas, $expiration, $options) {
            return app('url')->temporarySignedRoute(
                'unduh.panduan',
                $expiration,
                array_merge($options, ['berkas' => $berkas])
            );
        });
    }
}
