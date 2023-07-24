<?php

namespace App\Interaksi\SDM;

use App\Interaksi\Rangka;

trait SDMCache
{
    public static function ambilCachePermintaanTambahSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->cache->rememberForever('PermintaanTambahSDM', function () {
            return SDMDBQuery::ambilDBPengingatPermintaanTambahSDM()->get();
        });
    }

    public static function ambilCacheSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->cache->rememberForever('NamaNIKSDM', function () {
            return SDMDBQuery::ambilDBSDMTerkini()->get();
        });
    }

    public static function hapusCacheSDMUmum()
    {
        extract(Rangka::obyekPermintaanRangka());

        $cache = $app->cache;
        $hariIni = $app->date->today();
        $cache->forget('NamaNIKSDM');
        $cache->forget('PermintaanTambahSDM');
        $cache->forget('PosisiSDM');
        $cache->forget('SDMUlangTahun - ' . $hariIni->format('Y-m'));
        $cache->forget('PengingatKontrak - ' . $hariIni->format('Y-m-d'));
        $cache->forget('PengingatNon-Aktif - ' . $hariIni->format('Y-m-d'));
        $cache->forget('PengingatAkunBaru - ' . $hariIni->format('Y-m-d'));
        $cache->forget('PerubahanStatus - ' . $hariIni->format('Y-m-d'));
        $cache->forget('PengingatPelanggaran');
        $cache->forget('PengingatSanksi - ' . $hariIni->format('Y-m-d'));
        $cache->forget('PengingatNilai - ' . $hariIni->format('Y'));
    }
}
