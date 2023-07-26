<?php

namespace App\Http\Controllers;

use App\Interaksi\Berkas;
use App\Interaksi\Rangka;

class SumberDaya
{
    public function komponen()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $reqs->filled('komponen') && $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($app->view->make($reqs->komponen)->fragmentIf($reqs->filled('fragment'), $reqs->fragment))->withHeaders(['Vary' => 'Accept'])
            : '';
    }

    public function mulai()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($app->view->make('rangka'))->withHeaders(['Vary' => 'Accept']);
    }

    public function mulaiAplikasi()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        if ($pengguna) {
            $sandi = $pengguna->password;
            $hash = $app->hash;
            $sandiKtp = $hash->check($pengguna->sdm_no_ktp, $sandi);
            $sandiBawaan = $hash->check('penggunaportalsdm', $sandi);
            $sesi = $reqs->session();

            if ($sandiKtp || $sandiBawaan) {
                $sesi->put(['spanduk' => 'Sandi Anda kurang aman.']);
            }

            $sesi->now('pesan', 'Berhasil masuk aplikasi.');
        }

        $HtmlPenuh = $app->view->make('mulai');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $HtmlPenuh;
    }

    public function tentangAplikasi()
    {
        extract(Rangka::obyekPermintaanRangka());

        $HtmlPenuh = $app->view->make('tentang-aplikasi');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function unduh($berkas = null)
    {
        return Berkas::unduhBerkasUmum($berkas);
    }

    public function unduhPanduan($berkas = null)
    {
        return Berkas::unduhBerkasTerbatas($berkas);
    }

    public function periksaPengguna()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        $respon = $app->make('Illuminate\Contracts\Routing\ResponseFactory');

        return $pengguna
            ? $respon->make('true')->withHeaders(['Vary' => 'Accept'])
            : $respon->make('false')->withHeaders(['Vary' => 'Accept']);
    }

    public function formatFoto()
    {
        // foreach (Storage::files('sdm/foto-profil/backup') as $path) {
        //     Storage::copy($path, 'sdm/foto-profil/'.substr($path, -13));
        // }

        // return 'selesai';
    }

    public function pwaManifest()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($app->view->make('pwa-manifest'))->withHeaders(['Content-Type' => 'application/json']);
    }

    public function serviceWorker()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($app->view->make('service-worker'))->withHeaders(['Content-Type' => 'application/javascript', 'Cache-Control' => 'no-cache']);
    }
}
