<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('sdm-akses', function ($user) {
            return str($user->sdm_hak_akses)->contains('SDM');
        });
        Gate::define('sdm-manajemen', function ($user) {
            return str($user->sdm_hak_akses)->contains('SDM-MANAJEMEN');
        });
        Gate::define('sdm-pengurus', function ($user) {
            return str($user->sdm_hak_akses)->contains('SDM-PENGURUS');
        });
        Gate::define('sdm-pengguna', function ($user) {
            return str($user->sdm_hak_akses)->contains('SDM-PENGGUNA');
        });
        Gate::define('sdm-bukan-pengguna', function ($user) {
            return !str($user->sdm_hak_akses)->contains('SDM-PENGGUNA');
        });
    }
}
