<?php

namespace App\Interaksi;

class DatabaseQuery
{
    public static function ambilDatabasePengaturan()
    {
        extract(Umum::obyekPermintaanUmum());

        return $app->db->query()
            ->select(
                'atur_jenis',
                'atur_butir',
                'atur_status',
                'atur_detail'
            )
            ->from('aturs');
    }
}
