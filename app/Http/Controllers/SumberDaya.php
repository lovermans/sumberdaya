<?php

namespace App\Http\Controllers;

use App\Tambahan\FungsiStatis;

class SumberDaya
{
    public function mulai()
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();

        if ($pengguna) {
            $sandi = $pengguna->password;
            $hash = $app->hash;
            $sandiKtp = $hash->check($pengguna->sdm_no_ktp, $sandi);
            $sandiBawaan = $hash->check('penggunaportalsdm', $sandi);
            
            if ($sandiKtp || $sandiBawaan) {
                $reqs->session()->put(['spanduk' => 'Sandi Anda kurang aman.']);
            }
        }

        $HtmlPenuh = $app->view->make('mulai');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']) : $HtmlPenuh;
    }

    public function tentangAplikasi()
    {
        $app = app();
        $HtmlPenuh = $app->view->make('tentang-aplikasi');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $app->request->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }

    public function unduh($berkas = null)
    {
        $app = app();
        abort_unless($berkas && $app->filesystem->exists("unduh/{$berkas}"), 404, 'Berkas Tidak Ditemukan.');
        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->download($app->storagePath("app/unduh/{$berkas}"));
    }

    public function unduhPanduan(FungsiStatis $fungsiStatis, $berkas)
    {
        $fungsiStatis->hapusBerkasLama();
        
        $app = app();

        abort_unless($berkas && $app->filesystem->exists("{$berkas}"), 404, 'Berkas Tidak Ditemukan.');

        $jalur = $app->storagePath("app/{$berkas}");
        
        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->file($jalur, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Expires' => '0',
            'Pragma' => 'no-cache',
            'Content-Disposition' => 'inline',
            'Content-Type' => $app->files->mimeType($jalur),
        ]);
    }

    public function formatFoto()
    {
        // foreach (Storage::files('sdm/foto-profil/backup') as $path) {
        //     Storage::copy($path, 'sdm/foto-profil/'.substr($path, -13));
        // }

        // return 'selesai';
    }

}
