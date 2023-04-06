<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $app = app();

        $HtmlPenuh = $app->view->make('mulai');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $app->request->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')
                                ->make($HtmlIsi)->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']) : $HtmlIsi;
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        $app = app();
        $pengguna = $request->user();

        if ($pengguna) {
            $sandi = $pengguna->password;
            $hash = $app->hash;
            $sandiKtp = $hash->check($pengguna->sdm_no_ktp, $sandi);
            $sandiBawaan = $hash->check('penggunaportalsdm', $sandi);
            
            if ($sandiKtp || $sandiBawaan) {
                $request->session()->put(['spanduk' => 'Sandi Anda kurang aman.']);
            }
        }

        $HtmlPenuh = $app->view->make('mulai');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $request->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')
                                ->make($HtmlIsi)->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']) : $HtmlIsi;
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return app()->redirect->route('mulai');
    }
}
