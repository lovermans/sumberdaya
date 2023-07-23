<?php

namespace App\Interaksi;

trait DBQuery
{
    public static function ambilDatabasePengaturan()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->select(
                'atur_jenis',
                'atur_butir',
                'atur_status',
                'atur_detail'
            )
            ->from('aturs');
    }

    public static function tambahDatabasePengaturan($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('aturs')->insert($data);
    }

    public static function ubahDatabasePengaturan($uuid, $data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('aturs')->where('atur_uuid', $uuid)->update($data);
    }

    public static function imporDatabasePengaturan($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('aturs')->upsert(
            $data,
            ['atur_jenis', 'atur_butir'],
            ['atur_detail', 'atur_status', 'atur_id_pengunggah', 'atur_diunggah', 'atur_id_pengubah']
        );
    }
}
