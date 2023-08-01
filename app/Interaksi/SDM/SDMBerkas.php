<?php

namespace App\Interaksi\SDM;

use App\Interaksi\Rangka;

class SDMBerkas
{
    public static function simpanFotoSDM($foto, $no_absen)
    {
        extract(Rangka::obyekPermintaanRangka());

        $foto->storeAs('sdm/foto-profil', $no_absen . '.webp');
    }

    public static function simpanBerkasSDM($berkas, $no_absen)
    {
        extract(Rangka::obyekPermintaanRangka());

        $berkas->storeAs('sdm/berkas', $no_absen . '.pdf');
    }

    public static function ambilFotoSDM($berkas_foto_profil)
    {
        extract(Rangka::obyekPermintaanRangka());

        abort_unless($berkas_foto_profil && $app->filesystem->exists("sdm/foto-profil/{$berkas_foto_profil}"), 404, 'Foto Profil tidak ditemukan.');

        $jalur = $app->storagePath("app/sdm/foto-profil/{$berkas_foto_profil}");

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->file($jalur, [
            'Cache-Control' => 'max-age=31536000',
            'Content-Disposition' => 'inline',
            'Content-Type' => $app->files->mimeType($jalur),
        ]);
    }
}
