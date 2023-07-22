<?php

namespace App\Interaksi;

class Cache
{
    public static function ambilCacheAtur()
    {
        extract(Umum::obyekPermintaanUmum());

        return $app->cache->rememberForever('SetelanAtur', function () {
            return DatabaseQuery::ambilDatabasePengaturan()->get();
        });
    }
}
