<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Interaksi\Websoket;
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
        $reqs = $app->request;

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $HtmlPenuh = $app->view->make('mulai');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $HtmlPenuh;
    }

    /**
     * Handle an incoming authentication request.
     *
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

            $request->session()->now('pesan', 'Selamat datang '.$pengguna->sdm_nama.'.');

            $pesanSoket = $pengguna?->sdm_nama.' telah berhasil masuk aplikasi pada '.strtoupper($app->date->now()->translatedFormat('d F Y H:i:s'));

            Websoket::siaranUmum($pesanSoket);
        }

        $HtmlPenuh = $app->view->make('mulai');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $request->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    /**
     * Destroy an authenticated session.
     *
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
