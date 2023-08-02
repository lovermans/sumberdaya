<?php

namespace App\Interaksi\SDM;

use App\Interaksi\EksporWord;
use App\Interaksi\Rangka;

class SDMWord
{
    public static function formulirSerahTerimaSDMBaru($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid, 401);

        $akun = SDMDBQuery::ambilDataAkun($uuid)->first();

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $ijin_akses));
        $no_absen_sdm = $pengguna->sdm_no_absen;
        $lingkup_lokasi = collect($akun?->penempatan_lokasi);
        $lingkup_akses = $lingkup_lokasi->intersect($lingkup)->count();
        $no_absen_akun = $akun->sdm_no_absen;

        abort_unless((blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0)) && ($no_absen_sdm !== $no_absen_akun), 403, 'Akses pengguna dibatasi.');


        $data = [
            'sdm_nama' => str($akun->sdm_nama)->limit(30),
            'sdm_no_absen' => $no_absen_akun,
            'sdm_tgl_gabung' => strtoupper($app->date->make($akun->sdm_tgl_gabung)?->translatedFormat('d F Y'))
        ];

        $argumen = [
            'contoh' => 'serah-terima-sdm-baru.docx',
            'data' => $data,
            'filename' => 'serah-terima-sdm-baru-' . $no_absen_akun . '.docx'
        ];

        return EksporWord::eksporWordStream(...$argumen);
    }
}
