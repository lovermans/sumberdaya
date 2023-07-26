<?php

namespace App\Interaksi;

class Cache
{
    public static function ambilCacheAtur()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->cache->rememberForever('SetelanAtur', function () {
            return DBQuery::ambilDatabasePengaturan()->get();
        });
    }

    public static function hapusCacheAtur()
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->cache->forget('SetelanAtur');
    }
}
