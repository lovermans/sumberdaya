<?php

namespace App\Http\Controllers\SDM;

use App\Interaksi\Rangka;
use App\Interaksi\SDM\SDMDBQuery;
use App\Interaksi\SDM\SDMExcel;
use App\Interaksi\SDM\SDMValidasi;

class Kepuasan
{
    public function index($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        $aksesAkun = $uuid ? SDMDBQuery::aksesAkun($uuid) : null;

        $str = str();

        abort_unless(
            $pengguna && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN'])
                || ($pengguna?->sdm_uuid == $uuid && $pengguna?->sdm_uuid !== null)
                || ($pengguna?->sdm_id_atasan == $aksesAkun?->sdm_no_absen)
                || ($pengguna?->sdm_id_atasan == $aksesAkun?->sdm_id_atasan),
            403,
            'Akses dibatasi hanya untuk Pemangku SDM.'
        );

        $validator = SDMValidasi::validasiPencarianKepuasanSDM([$reqs->all()]);

        if ($validator->fails()) {
            return $app->redirect->route('sdm.kepuasan.data')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        }

        $lingkupIjin = array_filter(explode(',', $pengguna?->sdm_ijin_akses));

        $urutArray = $reqs->urut;
        $kunciUrut = array_filter((array) $urutArray);
        $uruts = $urutArray ? implode(',', $kunciUrut) : null;

        $cari = SDMDBQuery::cariKepuasanSDM($reqs, $reqs->kata_kunci, $uuid, $lingkupIjin, $uruts);

        if ($reqs->unduh == 'excel') {
            return SDMExcel::eksporExcelPencarianKepuasanSDM($cari);
        }
    }
}
