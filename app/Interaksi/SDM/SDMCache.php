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
        $cache->forget('SDMUlangTahun - '.$hariIni->format('Y-m'));
        $cache->forget('PengingatKontrak - '.$hariIni->format('Y-m-d'));
        $cache->forget('PengingatNon-Aktif - '.$hariIni->format('Y-m-d'));
        $cache->forget('PengingatAkunBaru - '.$hariIni->format('Y-m-d'));
        $cache->forget('PerubahanStatus - '.$hariIni->format('Y-m-d'));
        $cache->forget('PengingatPelanggaran');
        $cache->forget('PengingatSanksi - '.$hariIni->format('Y-m-d'));
        $cache->forget('PengingatNilai - '.$hariIni->format('Y'));
    }

    public static function hapusCacheSDMUltah()
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->cache->forget('SDMUlangTahun - '.$app->date->today()->subMonth()->format('Y-m'));
    }

    public static function ambilCacheSDMUltah()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->cache->rememberForever('SDMUlangTahun - '.$app->date->today()->format('Y-m'), function () {
            return SDMDBQuery::ambilDBSDMUltah()->get();
        });
    }

    public static function hapusCachePKWTHabis()
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->cache->forget('PengingatKontrak - '.$app->date->today()->subDay()->format('Y-m-d'));
    }

    public static function ambilCachePKWTHabis()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->cache->rememberForever('PengingatKontrak - '.$app->date->today()->format('Y-m-d'), function () {
            return SDMDBQuery::ambilPKWTAkanHabis()->get();
        });
    }

    public static function hapusCachePerubahanStatusKontrakTerbaru()
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->cache->forget('PerubahanStatus - '.$app->date->today()->subDay()->format('Y-m-d'));
    }

    public static function ambilCachePerubahanStatusKontrakTerbaru()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->cache->rememberForever('PerubahanStatus - '.$app->date->today()->format('Y-m-d'), function () {
            return SDMDBQuery::ambilPerubahanStatusKontrakTerbaru()->get();
        });
    }

    public static function hapusCacheSDMBaru()
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->cache->forget('PengingatAkunBaru - '.$app->date->today()->subDay()->format('Y-m-d'));
    }

    public static function ambilCacheSDMBaru()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->cache->rememberForever('PengingatAkunBaru - '.$app->date->today()->format('Y-m-d'), function () {
            return SDMDBQuery::ambilSDMBaruGabung()->get();
        });
    }

    public static function hapusCacheSDMKeluarTerkini()
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->cache->forget('PengingatNon-Aktif - '.$app->date->today()->subDay()->format('Y-m-d'));
    }

    public static function ambilCacheSDMKeluarTerkini()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->cache->rememberForever('PengingatNon-Aktif - '.$app->date->today()->format('Y-m-d'), function () {
            return SDMDBQuery::ambilSDMKeluarTerkini()->get();
        });
    }

    public static function hapusCachePelanggaranSDMTerkini()
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->cache->forget('PengingatPelanggaran');
    }

    public static function ambilCachePelanggaranSDMTerkini()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->cache->rememberForever('PengingatPelanggaran', function () {
            return SDMDBQuery::ambilPelanggaranSDMTerkini()->get();
        });
    }

    public static function hapusCacheSanksiSDMTerkini()
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->cache->forget('PengingatSanksi - '.$app->date->today()->subDay()->format('Y-m-d'));
    }

    public static function ambilCacheSanksiSDMTerkini()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->cache->rememberForever('PengingatSanksi - '.$app->date->today()->format('Y-m-d'), function () {
            return SDMDBQuery::ambilPengingatSanksiSDMTerkini()->get();
        });
    }

    public static function hapusCachePenilaianSDMTerkini()
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->cache->forget('PengingatNilai - '.$app->date->today()->subYear()->format('Y'));
    }

    public static function hapusCacheKepuasanSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->cache->forget('PengingatKepuasan - '.$app->date->today()->format('Y'));
    }

    public static function hapusCacheKepuasanSDMTerkini()
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->cache->forget('PengingatKepuasan - '.$app->date->today()->subYear()->format('Y'));
    }

    public static function ambilCachePenilaianSDMTerkini()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->cache->rememberForever('PengingatNilai - '.$app->date->today()->format('Y'), function () {
            return SDMDBQuery::ambilPengingatPenilaianSDMTerkini()->get();
        });
    }

    public static function ambilCacheKepuasanSDMTerkini()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->cache->rememberForever('PengingatKepuasan - '.$app->date->today()->format('Y'), function () {
            return SDMDBQuery::ambilPengingatKepuasanSDMTerkini()->get();
        });
    }

    public static function ambilCachePosisiSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->cache->rememberForever('PosisiSDM', function () {
            return SDMDBQuery::ambilDBPosisiSDM()->orderBy('posisi_nama')->get();
        });
    }

    public static function hapusCachePelanggaranSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->cache->forget('PengingatPelanggaran');
    }

    public static function hapusCacheSanksiSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->cache->forget('PengingatSanksi - '.$app->date->today()->format('Y-m-d'));
    }

    public static function hapusCachePelanggaran_SanksiSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        $cache = $app->cache;

        $cache->forget('PengingatSanksi - '.$app->date->today()->format('Y-m-d'));
        $cache->forget('PengingatPelanggaran');
    }

    public static function hapusCacheNilaiSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->cache->forget('PengingatNilai - '.$app->date->today()->format('Y'));
    }
}
