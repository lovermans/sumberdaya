<?php

namespace App\Http\Controllers;

use App\Interaksi\Umum;

class SumberDaya
{
    public function komponen()
    {
        extract(Umum::obyekLaravel());

        return $reqs->filled('komponen') && $reqs->pjax() ?
            $respon->make(
                $view->make($reqs->komponen)->fragmentIf($reqs->filled('fragment'), $reqs->fragment)
            )->withHeaders(['Vary' => 'Accept']) : '';
    }

    public function mulai()
    {
        extract(Umum::obyekLaravel());

        $HtmlPenuh = $view->make('rangka');
        return $respon->make($HtmlPenuh)->withHeaders(['Vary' => 'Accept']);
    }

    public function mulaiAplikasi()
    {
        extract(Umum::obyekLaravel());

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

        $HtmlPenuh = $view->make('mulai');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $respon->make($HtmlIsi)->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']) : $HtmlPenuh;
    }

    public function tentangAplikasi()
    {
        extract(Umum::obyekLaravel());

        $HtmlPenuh = $view->make('tentang-aplikasi');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $respon->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }

    public function unduh($berkas = null)
    {
        extract(Umum::obyekLaravel());

        abort_unless($berkas && $app->filesystem->exists("unduh/{$berkas}"), 404, 'Berkas Tidak Ditemukan.');
        return $respon->download($app->storagePath("app/unduh/{$berkas}"));
    }

    public function unduhPanduan($berkas)
    {
        extract(Umum::obyekLaravel());

        Umum::hapusBerkasUnduhanLama();

        abort_unless($berkas && $app->filesystem->exists("{$berkas}"), 404, 'Berkas Tidak Ditemukan.');

        $jalur = $app->storagePath("app/{$berkas}");

        return $respon->file($jalur, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Expires' => '0',
            'Pragma' => 'no-cache',
            'Content-Disposition' => 'inline',
            'Content-Type' => $app->files->mimeType($jalur),
        ]);
    }

    public function periksaPengguna()
    {
        extract(Umum::obyekLaravel());

        $reqs->user() ? $respon->make('true')->withHeaders(['Vary' => 'Accept']) : $respon->make('false')->withHeaders(['Vary' => 'Accept']);
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
        extract(Umum::obyekLaravel());

        $HtmlPenuh = $view->make('pwa-manifest');
        return $respon->make($HtmlPenuh)->withHeaders(['Content-Type' => 'application/json']);
    }

    public function serviceWorker()
    {
        extract(Umum::obyekLaravel());

        $HtmlPenuh = $view->make('service-worker');
        return $respon->make($HtmlPenuh)->withHeaders(['Content-Type' => 'application/javascript', 'Cache-Control' => 'no-cache']);
    }
}
