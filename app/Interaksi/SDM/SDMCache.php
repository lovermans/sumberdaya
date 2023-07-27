<?php

namespace App\Interaksi\SDM;

use App\Interaksi\Rangka;

class SDMCache
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

    public static function hapusCacheSDMUltah()
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->cache->forget('SDMUlangTahun - ' . $app->date->today()->subMonth()->format('Y-m'));
    }

    public static function ambilCacheSDMUltah()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->cache->rememberForever('SDMUlangTahun - ' . $app->date->today()->format('Y-m'), function () {
            return SDMDBQuery::ambilDBSDMUltah()->get();
        });
    }

    public static function hapusCachePKWTHabis()
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->cache->forget('PengingatKontrak - ' . $app->date->today()->subDay()->format('Y-m-d'));
    }

    public static function ambilCachePKWTHabis()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->cache->rememberForever('PengingatKontrak - ' . $app->date->today()->format('Y-m-d'), function () {
            return SDMDBQuery::ambilPKWTAkanHabis()->get();
        });
    }

    public static function hapusCachePerubahanStatusKontrakTerbaru()
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->cache->forget('PerubahanStatus - ' . $app->date->today()->subDay()->format('Y-m-d'));
    }

    public static function ambilCachePerubahanStatusKontrakTerbaru()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->cache->rememberForever('PerubahanStatus - ' . $app->date->today()->subDay()->format('Y-m-d'), function () {
            return SDMDBQuery::ambilPerubahanStatusKontrakTerbaru()->get();
        });
    }
}
