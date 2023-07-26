<?php

namespace App\Interaksi;

class DBQuery
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

    public static function saringDatabasePengaturan($reqs, $uruts)
    {
        return static::ambilDatabasePengaturan()
            ->addSelect('atur_uuid')
            ->when($reqs->atur_status, function ($query) use ($reqs) {
                $query->whereIn('atur_status', $reqs->atur_status);
            })
            ->when($reqs->atur_jenis, function ($query) use ($reqs) {
                $query->whereIn('atur_jenis', $reqs->atur_jenis);
            })
            ->when($reqs->atur_butir, function ($query) use ($reqs) {
                $query->whereIn('atur_butir', $reqs->atur_butir);
            })
            ->when($reqs->kata_kunci, function ($query) use ($reqs) {
                $query->where(function ($group) use ($reqs) {
                    $group->where('atur_jenis', 'like', '%' . $reqs->kata_kunci . '%')
                        ->orWhere('atur_butir', 'like', '%' . $reqs->kata_kunci . '%')
                        ->orWhere('atur_detail', 'like', '%' . $reqs->kata_kunci . '%');
                });
            })
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->orderBy('aturs.id', 'desc');
                }
            );
    }
}
