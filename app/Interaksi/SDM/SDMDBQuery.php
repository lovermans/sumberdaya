<?php

namespace App\Interaksi\SDM;

use App\Interaksi\Rangka;

class SDMDBQuery
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
                'penempatan_uuid',
                'penempatan_no_absen',
                'penempatan_lokasi',
                'penempatan_posisi',
                'penempatan_kontrak',
                'penempatan_mulai',
                'penempatan_selesai',
                'penempatan_ke'
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

    public static function ubahDataSDM($uuid, $data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('sdms')->where('sdm_uuid', $uuid)->update($data);
    }

    public static function ambilIDPengguna($idPenguna)
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()->select('id')->from('sdms')->where('id', $idPenguna);
    }

    public static function ubahSandiPengguna($idPenguna, $sandiBaru)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('sdms')->where('id', $idPenguna)->update(['password' => $sandiBaru]);
    }

    public static function ambilDataAkun($uuid)
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->select('sdms.*', 'penempatan_lokasi')
            ->from('sdms')
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini(), 'kontrak', function ($join) {
                $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->where('sdm_uuid', $uuid);
    }

    public static function ambilDataAkunLengkap($uuid)
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->select(
                'a.sdm_uuid',
                'a.sdm_no_permintaan',
                'a.sdm_no_absen',
                'a.sdm_tgl_gabung',
                'a.sdm_warganegara',
                'a.sdm_no_ktp',
                'a.sdm_nama',
                'a.sdm_tempat_lahir',
                'a.sdm_tgl_lahir',
                'a.sdm_kelamin',
                'a.sdm_gol_darah',
                'a.sdm_alamat',
                'a.sdm_alamat_rt',
                'a.sdm_alamat_rw',
                'a.sdm_alamat_kelurahan',
                'a.sdm_alamat_kecamatan',
                'a.sdm_alamat_kota',
                'a.sdm_alamat_provinsi',
                'a.sdm_alamat_kodepos',
                'a.sdm_agama',
                'a.sdm_no_kk',
                'a.sdm_status_kawin',
                'a.sdm_jml_anak',
                'a.sdm_pendidikan',
                'a.sdm_jurusan',
                'a.sdm_telepon',
                'a.email',
                'a.sdm_disabilitas',
                'a.sdm_no_bpjs',
                'a.sdm_no_jamsostek',
                'a.sdm_no_npwp',
                'a.sdm_nama_bank',
                'a.sdm_cabang_bank',
                'a.sdm_rek_bank',
                'a.sdm_an_rek',
                'a.sdm_nama_dok',
                'a.sdm_nomor_dok',
                'a.sdm_penerbit_dok',
                'a.sdm_an_dok',
                'a.sdm_kadaluarsa_dok',
                'a.sdm_uk_seragam',
                'a.sdm_uk_sepatu',
                'a.sdm_ket_kary',
                'a.sdm_tgl_berhenti',
                'a.sdm_jenis_berhenti',
                'a.sdm_ket_berhenti',
                'a.sdm_id_atasan',
                'a.sdm_hak_akses',
                'a.sdm_ijin_akses',
                'b.sdm_uuid as uuid_atasan',
                'b.sdm_nama as nama_atasan',
                'b.sdm_tgl_berhenti as tgl_berhenti_atasan',
                'kontrakakun.penempatan_lokasi as lokasi_akun',
                'kontrakatasan.penempatan_lokasi as lokasi_atasan',
                'kontrakatasan.penempatan_posisi as posisi_atasan'
            )
            ->from('sdms', 'a')
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini(), 'kontrakakun', function ($join) {
                $join->on('a.sdm_no_absen', '=', 'kontrakakun.penempatan_no_absen');
            })
            ->leftJoin('sdms as b', 'a.sdm_id_atasan', '=', 'b.sdm_no_absen')
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini(), 'kontrakatasan', function ($join) {
                $join->on('b.sdm_no_absen', '=', 'kontrakatasan.penempatan_no_absen');
            })
            ->where('a.sdm_uuid', $uuid);
    }

    public static function imporDatabaseSDM($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('sdms')->upsert(
            $data,
            ['sdm_no_absen'],
            ['sdm_no_permintaan', 'sdm_tgl_gabung', 'sdm_warganegara', 'sdm_no_ktp', 'sdm_nama', 'sdm_tempat_lahir', 'sdm_tgl_lahir', 'sdm_kelamin', 'sdm_gol_darah', 'sdm_alamat', 'sdm_alamat_rt', 'sdm_alamat_rw', 'sdm_alamat_kelurahan', 'sdm_alamat_kecamatan', 'sdm_alamat_kota', 'sdm_alamat_provinsi', 'sdm_alamat_kodepos', 'sdm_agama', 'sdm_no_kk', 'sdm_status_kawin', 'sdm_jml_anak', 'sdm_pendidikan', 'sdm_jurusan', 'sdm_telepon', 'email', 'sdm_disabilitas', 'sdm_no_bpjs', 'sdm_no_jamsostek', 'sdm_no_npwp', 'sdm_nama_bank', 'sdm_cabang_bank', 'sdm_rek_bank', 'sdm_an_rek', 'sdm_nama_dok', 'sdm_nomor_dok', 'sdm_penerbit_dok', 'sdm_an_dok', 'sdm_kadaluarsa_dok', 'sdm_uk_seragam', 'sdm_uk_sepatu', 'sdm_ket_kary', 'sdm_tgl_berhenti', 'sdm_jenis_berhenti', 'sdm_ket_berhenti', 'sdm_id_atasan', 'sdm_id_pengunggah', 'sdm_id_pengubah', 'sdm_diunggah']
        );
    }

    public static function contohImporDatabaseSDM($lingkup)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        return $app->db->query()
            ->select(
                'sdm_no_permintaan',
                'sdm_no_absen',
                'sdm_tgl_gabung',
                'sdm_warganegara',
                'sdm_no_ktp',
                'sdm_nama',
                'sdm_tempat_lahir',
                'sdm_tgl_lahir',
                'sdm_kelamin',
                'sdm_gol_darah',
                'sdm_alamat',
                'sdm_alamat_rt',
                'sdm_alamat_rw',
                'sdm_alamat_kelurahan',
                'sdm_alamat_kecamatan',
                'sdm_alamat_kota',
                'sdm_alamat_provinsi',
                'sdm_alamat_kodepos',
                'sdm_agama',
                'sdm_no_kk',
                'sdm_status_kawin',
                'sdm_jml_anak',
                'sdm_pendidikan',
                'sdm_jurusan',
                'sdm_telepon',
                'email',
                'sdm_disabilitas',
                'sdm_no_bpjs',
                'sdm_no_jamsostek',
                'sdm_no_npwp',
                'sdm_nama_bank',
                'sdm_cabang_bank',
                'sdm_rek_bank',
                'sdm_an_rek',
                'sdm_nama_dok',
                'sdm_nomor_dok',
                'sdm_penerbit_dok',
                'sdm_an_dok',
                'sdm_kadaluarsa_dok',
                'sdm_uk_seragam',
                'sdm_uk_sepatu',
                'sdm_ket_kary',
                'sdm_tgl_berhenti',
                'sdm_jenis_berhenti',
                'sdm_ket_berhenti',
                'sdm_id_atasan'
            )
            ->from('sdms')
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini(), 'kontrak', function ($join) {
                $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->whereNull('sdm_tgl_berhenti')
            ->when($lingkup, function ($c) use ($lingkup) {
                return $c->whereIn('penempatan_lokasi', [null, ...$lingkup]);
            });
    }

    public static function ambilDBSDMUltah()
    {
        extract(Rangka::obyekPermintaanRangka());

        $date = $app->date;

        return $app->db->query()
            ->select(
                'sdm_uuid',
                'sdm_no_absen',
                'sdm_nama',
                'sdm_tgl_lahir',
                'penempatan_lokasi',
                'penempatan_kontrak',
                'penempatan_posisi'
            )
            ->from('sdms')
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini(), 'kontrak', function ($join) {
                $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->whereNull('sdm_tgl_berhenti')
            ->where(function ($query) use ($date) {
                $query->whereMonth('sdm_tgl_lahir', $date->today()->format('m'))
                    ->orWhereMonth('sdm_tgl_lahir', $date->today()->addMonth()->format('m'));
            })->orderByRaw('DAYOFYEAR(sdm_tgl_lahir), sdm_tgl_lahir');
    }

    public static function ambilLokasiPenempatanSDM()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        return $app->db->query()
            ->select('penempatan_lokasi')
            ->from('sdms')
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini(), 'kontrak', function ($join) {
                $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->where('kontrak.penempatan_no_absen', $pengguna->sdm_no_absen);
    }

    public static function ambilPKWTAkanHabis()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->select(
                'sdm_uuid',
                'sdm_no_absen',
                'sdm_nama',
                'penempatan_uuid',
                'penempatan_posisi',
                'penempatan_lokasi',
                'penempatan_kontrak',
                'penempatan_mulai',
                'penempatan_selesai',
                'penempatan_ke'
            )
            ->from('sdms')
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini(), 'kontrak', function ($join) {
                $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->whereNull('sdm_tgl_berhenti')
            ->where('penempatan_kontrak', 'not like', 'OS-%')
            ->where('penempatan_selesai', '<=', $app->date->today()->addDays(40)->toDateString())
            ->latest('penempatan_selesai');
    }

    public static function ambilPerubahanStatusKontrakTerbaru()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->select(
                'penempatan_uuid',
                'penempatan_no_absen',
                'penempatan_mulai',
                'penempatan_selesai',
                'penempatan_ke',
                'penempatan_lokasi',
                'penempatan_posisi',
                'penempatan_kategori',
                'penempatan_kontrak',
                'penempatan_pangkat',
                'penempatan_golongan',
                'penempatan_grup',
                'penempatan_keterangan',
                'sdm_uuid',
                'sdm_no_absen',
                'sdm_nama'
            )
            ->from('penempatans')
            ->join('sdms', 'sdm_no_absen', '=', 'penempatan_no_absen')
            ->where('penempatan_mulai', '>=', $app->date->today()->addDays(40)->toDateString())
            ->latest('penempatan_mulai');
    }
}
