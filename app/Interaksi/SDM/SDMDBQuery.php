<?php

namespace App\Interaksi\SDM;

use App\Interaksi\Rangka;

trait SDMDBQuery
{
    public static function ambilDBPermintaanTambahSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        $database = $app->db;

        return $database->query()
            ->select(
                'tambahsdm_uuid',
                'tambahsdm_no',
                'tambahsdm_sdm_id',
                'tambahsdm_penempatan',
                'tambahsdm_posisi',
                'tambahsdm_jumlah',
                $database->raw('COUNT(a.sdm_no_permintaan) as tambahsdm_terpenuhi, MAX(a.sdm_tgl_gabung) as pemenuhan_terkini'),
                'tambahsdm_status',
                'b.sdm_uuid',
                'b.sdm_nama',
                'tambahsdm_tgl_diusulkan',
                'tambahsdm_tgl_dibutuhkan',
                'tambahsdm_alasan',
                'tambahsdm_keterangan'
            )
            ->from('tambahsdms')
            ->leftJoin('sdms as a', 'tambahsdm_no', '=', 'a.sdm_no_permintaan')
            ->join('sdms as b', 'tambahsdm_sdm_id', '=', 'b.sdm_no_absen')
            ->groupBy('tambahsdm_no');
    }

    public static function ambilDBPengingatPermintaanTambahSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->addSelect('tsdm.*')
            ->fromSub(static::ambilDBPermintaanTambahSDM(), 'tsdm')
            ->where('tambahsdm_status', 'DISETUJUI')
            ->whereColumn('tambahsdm_jumlah', '>', 'tambahsdm_terpenuhi')
            ->latest('tambahsdm_tgl_diusulkan');
    }

    public static function ambilDBPenempatanSDMTerkini()
    {
        extract(Rangka::obyekPermintaanRangka());

        $database = $app->db;

        return $database->query()
            ->select(
                'penempatan_no_absen',
                'penempatan_lokasi',
                'penempatan_posisi',
                'penempatan_kontrak'
            )
            ->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
                $query->select($database->raw('MAX(penempatan_mulai)'))
                    ->from('penempatans as p2')
                    ->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
            });
    }

    public static function ambilDBSDMTerkini()
    {
        extract(Rangka::obyekPermintaanRangka());

        $database = $app->db;

        return $database->query()
            ->select(
                'sdm_uuid',
                'sdm_no_absen',
                'sdm_tgl_berhenti',
                'sdm_nama',
                'penempatan_lokasi',
                'penempatan_posisi',
                'penempatan_kontrak',
                'sdm_id_atasan'
            )
            ->from('sdms')
            ->joinSub(static::ambilDBPenempatanSDMTerkini(), 'kontrak', function ($join) {
                $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->whereNull('sdm_tgl_berhenti')
            ->orderBy('sdm_no_absen');
    }

    public static function tambahDataSDM($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('sdms')->insert($data);
    }
}
