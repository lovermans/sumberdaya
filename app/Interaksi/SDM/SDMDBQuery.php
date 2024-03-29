<?php

namespace App\Interaksi\SDM;

use App\Interaksi\Rangka;

class SDMDBQuery
{
    public static function ambilKTPTerlamaSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->select(
                'sdm_no_permintaan',
                's1.sdm_tgl_gabung',
                's1.sdm_no_ktp as no_ktp'
            )
            ->from('sdms as s1')
            ->where('sdm_tgl_gabung', '=', function ($query) {
                $query->selectRaw('MIN(sdm_tgl_gabung)')
                    ->from('sdms as s2')
                    ->whereColumn('s1.sdm_no_ktp', 's2.sdm_no_ktp');
            })
            ->groupBy('sdm_no_ktp');
    }

    public static function ambilKTPTerkiniSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->select(
                's3.sdm_uuid as sdm_uuid',
                's3.sdm_no_absen as sdm_no_absen',
                's3.sdm_tgl_lahir as sdm_tgl_lahir',
                's3.sdm_tempat_lahir as sdm_tempat_lahir',
                's3.sdm_no_ktp as sdm_no_ktp',
                's3.sdm_nama as sdm_nama',
                's3.sdm_kelamin as sdm_kelamin',
                's3.sdm_tgl_berhenti as sdm_tgl_berhenti',
                's3.sdm_jenis_berhenti as sdm_jenis_berhenti',
                's3.sdm_ket_berhenti as sdm_ket_berhenti',
                's3.sdm_disabilitas as sdm_disabilitas',
                's3.sdm_agama as sdm_agama',
                's3.sdm_status_kawin as sdm_status_kawin',
                's3.sdm_pendidikan as sdm_pendidikan',
                's3.sdm_warganegara as sdm_warganegara',
                's3.sdm_uk_seragam as sdm_uk_seragam',
                's3.sdm_uk_sepatu as sdm_uk_sepatu',
                's3.sdm_jurusan as sdm_jurusan',
                's3.sdm_telepon as sdm_telepon',
                's3.email as email',
                's3.sdm_id_atasan as sdm_id_atasan',
                's3.sdm_no_bpjs as sdm_no_bpjs',
                's3.sdm_no_jamsostek as sdm_no_jamsostek',
                's3.sdm_jml_anak as sdm_jml_anak',
                's3.sdm_ket_kary as sdm_ket_kary',
                's3.sdm_alamat as sdm_alamat',
                's3.sdm_alamat_rt as sdm_alamat_rt',
                's3.sdm_alamat_rw as sdm_alamat_rw',
                's3.sdm_alamat_kelurahan as sdm_alamat_kelurahan',
                's3.sdm_alamat_kecamatan as sdm_alamat_kecamatan',
                's3.sdm_alamat_kota as sdm_alamat_kota',
                's3.sdm_alamat_provinsi as sdm_alamat_provinsi',
                's3.sdm_alamat_kodepos as sdm_alamat_kodepos',
            )
            ->from('sdms as s3')
            ->where('s3.sdm_tgl_gabung', '=', function ($query) {
                $query->selectRaw('MAX(s4.sdm_tgl_gabung)')
                    ->from('sdms as s4')
                    ->whereColumn('s3.sdm_no_ktp', 's4.sdm_no_ktp');
            });
    }

    public static function ambilPermintaanTambahSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->select(
                'tambahsdm_no',
                'tambahsdm_penempatan',
            )
            ->from('tambahsdms');
    }

    public static function ambilDBPermintaanTambahSDM()
    {
        return static::ambilPermintaanTambahSDM()
            ->addSelect(
                'tambahsdm_uuid',
                'tambahsdm_sdm_id',
                'tambahsdm_posisi',
                'tambahsdm_jumlah',
                'tambahsdm_status',
                'b.sdm_uuid',
                'b.sdm_nama',
                'tambahsdm_tgl_diusulkan',
                'tambahsdm_tgl_dibutuhkan',
                'tambahsdm_alasan',
                'tambahsdm_keterangan',
                'tambahsdm_dibuat',
            )
            ->selectRaw('COUNT(a.sdm_no_permintaan) as tambahsdm_terpenuhi, MAX(a.sdm_tgl_gabung) as pemenuhan_terkini')
            ->leftJoin('sdms as a', 'tambahsdm_no', '=', 'a.sdm_no_permintaan')
            ->join('sdms as b', 'tambahsdm_sdm_id', '=', 'b.sdm_no_absen')
            ->groupBy('tambahsdm_no');
    }

    public static function ambilUrutanPermintaanTambahSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->selectRaw('MAX(tsdm.tambahsdm_no) as maxno, COUNT(tsdm.tambahsdm_no) as countno')
            ->fromSub(static::ambilDBPermintaanTambahSDM(), 'tsdm')
            ->where(function ($group) {
                $group->whereYear('tsdm.tambahsdm_dibuat', date('Y'))
                    ->whereMonth('tsdm.tambahsdm_dibuat', date('m'));
            })
            ->orWhere('tsdm.tambahsdm_no', 'like', date('Y').date('m').'%')
            ->first();
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

    public static function ambilPencarianPermintaanTambahSDM($permintaan, $kataKunci, $uruts, $lingkupIjin)
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->addSelect('tsdm.*')
            ->fromSub(static::ambilDBPermintaanTambahSDM(), 'tsdm')
            ->when($permintaan->tambahsdm_status, function ($query) use ($permintaan) {
                $query->whereIn('tambahsdm_status', (array) $permintaan->tambahsdm_status);
            })
            ->when($permintaan->tambahsdm_penempatan, function ($query) use ($permintaan) {
                $query->whereIn('tambahsdm_penempatan', (array) $permintaan->tambahsdm_penempatan);
            })
            ->when($kataKunci, function ($query) use ($kataKunci) {
                $query->where(function ($group) use ($kataKunci) {
                    $group->where('tambahsdm_no', 'like', '%'.$kataKunci.'%')
                        ->orWhere('tambahsdm_posisi', 'like', '%'.$kataKunci.'%')
                        ->orWhere('tambahsdm_sdm_id', 'like', '%'.$kataKunci.'%')
                        ->orWhere('sdm_nama', 'like', '%'.$kataKunci.'%');
                });
            })
            ->when($permintaan->tgl_diusulkan_mulai && $permintaan->tgl_diusulkan_sampai, function ($query) use ($permintaan) {
                $query->whereBetween('tambahsdm_tgl_diusulkan', [$permintaan->tgl_diusulkan_mulai, $permintaan->tgl_diusulkan_sampai]);
            })
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->whereIn('tambahsdm_penempatan', $lingkupIjin);
            })
            ->when($permintaan->posisi, function ($query) use ($permintaan) {
                $query->whereIn('tambahsdm_posisi', (array) $permintaan->posisi);
            })
            ->when($permintaan->tambahsdm_laju == 'BELUM TERPENUHI', function ($query) {
                $query->whereColumn('tambahsdm_jumlah', '>', 'tambahsdm_terpenuhi');
            })
            ->when($permintaan->tambahsdm_laju == 'SUDAH TERPENUHI', function ($query) {
                $query->whereColumn('tambahsdm_jumlah', 'tambahsdm_terpenuhi');
            })
            ->when($permintaan->tambahsdm_laju == 'KELEBIHAN', function ($query) {
                $query->whereColumn('tambahsdm_jumlah', '<', 'tambahsdm_terpenuhi');
            })
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->orderBy('tambahsdm_no', 'desc');
                }
            );
    }

    public static function tambahDataPermintaanTambahSDM($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('tambahsdms')->insert($data);
    }

    public static function ubahDataPermintaanTambahSDM($data, $uuid)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('tambahsdms')->where('tambahsdm_uuid', $uuid)->update($data);
    }

    public static function tambahhDataLapPelanggaranSDM($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('pelanggaransdms')->insert($data);
    }

    public static function ubahDataLapPelanggaranSDM($data, $uuid)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('pelanggaransdms')->where('langgar_uuid', $uuid)->update($data);
    }

    public static function hapusDataPermintaanTambahSDM($uuid)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('tambahsdms')->where('tambahsdm_uuid', $uuid)->delete();
    }

    public static function hapusDataLapPelanggaranSDM($uuid)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('pelanggaransdms')->where('langgar_uuid', $uuid)->delete();
    }

    public static function hapusDataSanksiSDM($uuid)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('sanksisdms')->where('sanksi_uuid', $uuid)->delete();
    }

    public static function hapusDataPenempatanSDM($uuid)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('penempatans')->where('penempatan_uuid', $uuid)->delete();
    }

    public static function ambilDBPenempatanSDMTerkini()
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
                'penempatan_keterangan'
            )
            ->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) {
                $query->selectRaw('MAX(penempatan_mulai)')
                    ->from('penempatans as p2')
                    ->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
            });
    }

    public static function ambilDBSDMTerkini()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
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

        abort_unless($idPenguna, 404, 'Pengguna Tidak Ditemukan.');

        return $app->db->query()->select('id')->from('sdms')->where('id', $idPenguna);
    }

    public static function ubahSandiPengguna($idPenguna, $sandiBaru)
    {
        extract(Rangka::obyekPermintaanRangka());

        abort_unless($idPenguna, 404, 'Pengguna Tidak Ditemukan.');

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

        $database = $app->db;

        $database->transaction(function () use ($database, $data) {
            $database->table('sdms')->upsert(
                $data,
                ['sdm_no_absen'],
                ['sdm_no_permintaan', 'sdm_tgl_gabung', 'sdm_warganegara', 'sdm_no_ktp', 'sdm_nama', 'sdm_tempat_lahir', 'sdm_tgl_lahir', 'sdm_kelamin', 'sdm_gol_darah', 'sdm_alamat', 'sdm_alamat_rt', 'sdm_alamat_rw', 'sdm_alamat_kelurahan', 'sdm_alamat_kecamatan', 'sdm_alamat_kota', 'sdm_alamat_provinsi', 'sdm_alamat_kodepos', 'sdm_agama', 'sdm_no_kk', 'sdm_status_kawin', 'sdm_jml_anak', 'sdm_pendidikan', 'sdm_jurusan', 'sdm_telepon', 'email', 'sdm_disabilitas', 'sdm_no_bpjs', 'sdm_no_jamsostek', 'sdm_no_npwp', 'sdm_nama_bank', 'sdm_cabang_bank', 'sdm_rek_bank', 'sdm_an_rek', 'sdm_nama_dok', 'sdm_nomor_dok', 'sdm_penerbit_dok', 'sdm_an_dok', 'sdm_kadaluarsa_dok', 'sdm_uk_seragam', 'sdm_uk_sepatu', 'sdm_ket_kary', 'sdm_tgl_berhenti', 'sdm_jenis_berhenti', 'sdm_ket_berhenti', 'sdm_id_atasan', 'sdm_id_pengunggah', 'sdm_id_pengubah', 'sdm_diunggah']
            );
        });
    }

    public static function ambilDBSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->select(
                'sdm_no_absen',
                'sdm_no_permintaan',
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
            ->from('sdms');
    }

    public static function dataSDMKTPTerlama()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->select(
                'sdm_no_permintaan',
                's1.sdm_tgl_gabung',
                's1.sdm_no_ktp'
            )
            ->from('sdms as s1')
            ->where('sdm_tgl_gabung', '=', function ($query) {
                $query->selectRaw('MIN(sdm_tgl_gabung)')
                    ->from('sdms as s2')
                    ->whereColumn('s1.sdm_no_ktp', 's2.sdm_no_ktp');
            })->groupBy('sdm_no_ktp');
    }

    public static function dasarRiwayatPenempatanSDM($fungsi)
    {
        return static::$fungsi()
            ->select(
                'sdms.id',
                'sdm_uuid',
                'sdm_no_absen',
                'sdm_tgl_gabung',
                'sdm_tgl_lahir',
                'sdm_tgl_berhenti',
                'sdm_nama',
                'sdm_no_permintaan',
                'sdm_jenis_berhenti',
                'sdm_ket_berhenti',
                'sdm_no_ktp',
                'sdm_warganegara',
                'sdm_tempat_lahir',
                'sdm_kelamin',
                'sdm_agama',
                'sdm_status_kawin',
                'sdm_pendidikan',
                'sdm_jurusan',
                'sdm_telepon',
                'email',
                'sdm_no_jamsostek',
                'sdm_no_bpjs',
                'sdm_jml_anak',
                'sdm_uk_seragam',
                'sdm_uk_sepatu',
                'sdm_disabilitas',
                'sdm_id_atasan',
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
                'posisi_wlkp',
            )
            ->selectRaw('IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, sdm_tgl_berhenti)) as masa_kerja, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, penempatan_mulai, NOW()),TIMESTAMPDIFF(YEAR, penempatan_mulai, sdm_tgl_berhenti)) as masa_aktif, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, sdm_tgl_berhenti)) as usia');
    }

    public static function dasarSemuaRiwayatPenempatanSDM()
    {
        return static::dasarRiwayatPenempatanSDM('ambilDBPenempatanSDM')
            ->leftJoin('sdms', 'sdm_no_absen', '=', 'penempatan_no_absen')
            ->leftJoin('posisis', 'penempatan_posisi', '=', 'posisi_nama');
    }

    public static function dasarRiwayatPenempatanSDMTerkini()
    {
        return static::dasarRiwayatPenempatanSDM('ambilDBSDM')
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini(), 'penem', function ($join) {
                $join->on('sdm_no_absen', '=', 'penem.penempatan_no_absen');
            })
            ->leftJoin('posisis', 'penempatan_posisi', '=', 'posisi_nama');
    }

    public static function dasarSaringanPenempatanSDM($permintaan, $kataKunci, $lingkupIjin, $fungsiDasar)
    {
        return static::$fungsiDasar()
            ->when($kataKunci, function ($query, $kataKunci) {
                $query->where(function ($group) use ($kataKunci) {
                    $group->where('sdm_no_absen', 'like', '%'.$kataKunci.'%')
                        ->orWhere('penempatan_no_absen', 'like', '%'.$kataKunci.'%')
                        ->orWhere('sdm_no_permintaan', $kataKunci)
                        ->orWhere('sdm_no_ktp', 'like', '%'.$kataKunci.'%')
                        ->orWhere('sdm_nama', 'like', '%'.$kataKunci.'%')
                        ->orWhere('penempatan_posisi', 'like', '%'.$kataKunci.'%')
                        ->orWhere('penempatan_keterangan', 'like', '%'.$kataKunci.'%');
                });
            })
            ->when($lingkupIjin, function ($query, $lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->orWhereIn('penempatan_lokasi', $lingkupIjin)
                        ->orWhereNull('penempatan_lokasi');
                });
            })
            ->when($permintaan->kontrak, function ($query) use ($permintaan) {
                $query->whereIn('penempatan_kontrak', (array) $permintaan->kontrak);
            })
            ->when($permintaan->lokasi, function ($query) use ($permintaan) {
                $query->whereIn('penempatan_lokasi', (array) $permintaan->lokasi);
            })
            ->when($permintaan->kategori, function ($query) use ($permintaan) {
                $query->whereIn('penempatan_kategori', (array) $permintaan->kategori);
            })
            ->when($permintaan->pangkat, function ($query) use ($permintaan) {
                $query->whereIn('penempatan_pangkat', (array) $permintaan->pangkat);
            })
            ->when($permintaan->kelamin, function ($query) use ($permintaan) {
                $query->where('sdm_kelamin', $permintaan->kelamin);
            })
            ->when($permintaan->posisi, function ($query) use ($permintaan) {
                $query->whereIn('penempatan_posisi', (array) $permintaan->posisi);
            })
            ->when($permintaan->agama, function ($query) use ($permintaan) {
                $query->whereIn('sdm_agama', (array) $permintaan->agama);
            })
            ->when($permintaan->kawin, function ($query) use ($permintaan) {
                $query->whereIn('sdm_status_kawin', (array) $permintaan->kawin);
            })
            ->when($permintaan->warganegara, function ($query) use ($permintaan) {
                $query->whereIn('sdm_warganegara', (array) $permintaan->warganegara);
            })
            ->when($permintaan->pendidikan, function ($query) use ($permintaan) {
                $query->whereIn('sdm_pendidikan', (array) $permintaan->pendidikan);
            })
            ->when($permintaan->disabilitas, function ($query) use ($permintaan) {
                $query->whereIn('sdm_disabilitas', (array) $permintaan->disabilitas);
            });
    }

    public static function ambilSemuaRiwayatPenempatanSDM($permintaan, $kataKunci, $lingkupIjin, $uruts, $uuid = null)
    {
        return static::dasarSaringanPenempatanSDM($permintaan, $kataKunci, $lingkupIjin, 'dasarSemuaRiwayatPenempatanSDM')
            ->whereNotNull('sdm_no_absen')
            ->when($uuid, function ($query) use ($uuid) {
                $query->where('sdm_uuid', $uuid);
            })
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->latest('penempatan_mulai')
                        ->orderBy('sdm_no_absen', 'desc');
                }
            );
    }

    public static function ambilStatistikPenempatanSDM($lingkupIjin)
    {
        return static::dasarPenempatanSDM()
            ->whereNotNull('sdm_uuid')
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->whereIn('penempatan_lokasi', $lingkupIjin);
            })
            ->orderBy('sdm_no_absen');
    }

    public static function dasarMasaKerjaNyataSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->addSelect(
                'sdmlama.*',
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
                'posisi_wlkp',
                'sdmbaru.*',
            )
            ->selectRaw('IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, sdm_tgl_berhenti)) as masa_kerja, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, penempatan_mulai, NOW()),TIMESTAMPDIFF(YEAR, penempatan_mulai, sdm_tgl_berhenti)) as masa_aktif, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, sdm_tgl_berhenti)) as usia')
            ->fromSub(static::ambilKTPTerlamaSDM(), 'sdmlama')
            ->joinSub(static::ambilKTPTerkiniSDM(), 'sdmbaru', function ($join) {
                $join->on('sdmlama.no_ktp', '=', 'sdmbaru.sdm_no_ktp');
            })
            ->joinSub(static::ambilDBPenempatanSDMTerkini(), 'penem', function ($join) {
                $join->on('sdmbaru.sdm_no_absen', '=', 'penem.penempatan_no_absen');
            })
            ->leftJoin('posisis', 'penempatan_posisi', '=', 'posisi_nama');
    }

    public static function dasarPenempatanSDM()
    {
        return static::ambilDBPenempatanSDMTerkini()
            ->addSelect(
                'sdm_uuid',
                'sdm_no_absen',
                'sdm_no_permintaan',
                'sdm_tempat_lahir',
                'sdm_tgl_lahir',
                'sdm_tgl_gabung',
                'sdm_no_ktp',
                'sdm_nama',
                'sdm_kelamin',
                'sdm_tgl_berhenti',
                'sdm_jenis_berhenti',
                'sdm_ket_berhenti',
                'posisi_wlkp',
                'sdm_disabilitas',
                'sdm_ket_kary',
                'sdm_alamat',
                'sdm_alamat_rt',
                'sdm_alamat_rw',
                'sdm_alamat_kelurahan',
                'sdm_alamat_kecamatan',
                'sdm_alamat_kota',
                'sdm_alamat_provinsi',
                'sdm_alamat_kodepos',
                'sdm_agama',
                'sdm_status_kawin',
                'sdm_pendidikan',
                'sdm_warganegara',
                'sdm_uk_seragam',
                'sdm_uk_sepatu',
                'sdm_jurusan',
                'sdm_telepon',
                'email',
                'sdm_id_atasan',
                'sdm_no_bpjs',
                'sdm_no_jamsostek',
                'sdm_jml_anak'
            )
            ->selectRaw('IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, sdm_tgl_berhenti)) as masa_kerja, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, penempatan_mulai, NOW()),TIMESTAMPDIFF(YEAR, penempatan_mulai, sdm_tgl_berhenti)) as masa_aktif, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, sdm_tgl_berhenti)) as usia')
            ->leftJoin('sdms', 'penempatan_no_absen', '=', 'sdm_no_absen')
            ->leftJoin('posisis', 'penempatan_posisi', '=', 'posisi_nama');
    }

    public static function ambilPenempatanAktifSDM($permintaan, $kataKunci, $lingkupIjin, $uruts)
    {
        return static::dasarSaringanPenempatanSDM($permintaan, $kataKunci, $lingkupIjin, 'dasarPenempatanSDM')
            ->whereNotNull('sdm_no_absen')
            ->whereNull('sdm_tgl_berhenti')
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->latest('sdm_tgl_gabung')
                        ->orderBy('penempatan_no_absen', 'desc');
                }
            );
    }

    public static function ambilPenempatanNonAktifSDM($permintaan, $kataKunci, $lingkupIjin, $uruts)
    {
        return static::dasarSaringanPenempatanSDM($permintaan, $kataKunci, $lingkupIjin, 'dasarPenempatanSDM')
            ->whereNotNull('sdm_no_absen')
            ->whereNotNull('sdm_tgl_berhenti')
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->latest('sdm_tgl_berhenti')
                        ->orderBy('penempatan_no_absen', 'desc');
                }
            );
    }

    public static function ambilPKWTAkanHabisSDM($permintaan, $kataKunci, $lingkupIjin, $uruts, $rentang)
    {
        return static::dasarSaringanPenempatanSDM($permintaan, $kataKunci, $lingkupIjin, 'dasarPenempatanSDM')
            ->whereNotNull('sdm_no_absen')
            ->where('penempatan_kontrak', 'not like', 'OS-%')
            ->whereNull('sdm_tgl_berhenti')
            ->whereBetween('penempatan_selesai', $rentang)
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->latest('penempatan_selesai')
                        ->orderBy('penempatan_no_absen', 'desc');
                }
            );
    }

    public static function ambilPKWTKadaluarsaSDM($permintaan, $kataKunci, $lingkupIjin, $uruts, $rentang)
    {
        return static::dasarSaringanPenempatanSDM($permintaan, $kataKunci, $lingkupIjin, 'dasarPenempatanSDM')
            ->whereNotNull('sdm_no_absen')
            ->where('penempatan_kontrak', 'not like', 'OS-%')
            ->whereNull('sdm_tgl_berhenti')
            ->where('penempatan_selesai', '<=', $rentang)
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->latest('penempatan_selesai')
                        ->orderBy('penempatan_no_absen', 'desc');
                }
            );
    }

    public static function ambilMasaKerjaNyataSDM($permintaan, $kataKunci, $lingkupIjin, $uruts)
    {
        return static::dasarSaringanPenempatanSDM($permintaan, $kataKunci, $lingkupIjin, 'dasarMasaKerjaNyataSDM')

            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->latest('sdm_tgl_gabung')
                        ->latest('penempatan_mulai')
                        ->orderBy('sdm_no_absen', 'desc');
                }
            );
    }

    public static function ambilMasaKerjaNyataSDMAktif($permintaan, $kataKunci, $lingkupIjin, $uruts)
    {
        return static::ambilMasaKerjaNyataSDM($permintaan, $kataKunci, $lingkupIjin, $uruts)
            ->whereNull('sdm_tgl_berhenti');
    }

    public static function ambilMasaKerjaNyataSDMNonAktif($permintaan, $kataKunci, $lingkupIjin, $uruts)
    {
        return static::ambilMasaKerjaNyataSDM($permintaan, $kataKunci, $lingkupIjin, $uruts)
            ->whereNotNull('sdm_tgl_berhenti');
    }

    public static function ambilSDMAktifBelumDitempatkan($permintaan, $kataKunci, $lingkupIjin, $uruts)
    {
        return static::dasarSaringanPenempatanSDM($permintaan, $kataKunci, $lingkupIjin, 'dasarRiwayatPenempatanSDMTerkini')
            ->whereNull('penempatan_lokasi')
            ->whereNull('sdm_tgl_berhenti')
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->latest('sdm_tgl_gabung')
                        ->orderBy('sdm_no_absen', 'desc');
                }
            );
    }

    public static function ambilSDMBatalBergabung($permintaan, $kataKunci, $lingkupIjin, $uruts)
    {
        return static::dasarSaringanPenempatanSDM($permintaan, $kataKunci, $lingkupIjin, 'dasarRiwayatPenempatanSDMTerkini')
            ->whereNull('penempatan_lokasi')
            ->whereNotNull('sdm_tgl_berhenti')
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->latest('sdm_tgl_berhenti')
                        ->orderBy('sdm_no_absen', 'desc');
                }
            );
    }

    public static function contohImporDatabaseSDM($lingkup)
    {
        return static::ambilDBSDM()
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

        abort_unless($pengguna, 401);

        return $app->db->query()
            ->select('penempatan_lokasi')
            ->from('sdms')
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini(), 'kontrak', function ($join) {
                $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->where('kontrak.penempatan_no_absen', $pengguna?->sdm_no_absen);
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

    public static function ambilDBPenempatanSDM()
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
            )
            ->from('penempatans');
    }

    public static function ambilDataPenempatanSDM($lingkupIjin, $uuid)
    {
        return static::ambilDBPenempatanSDM()
            ->addSelect(
                'sdm_uuid',
                'sdm_no_absen',
                'sdm_nama',
                'sdm_tgl_gabung',
                'sdm_tgl_berhenti',
                'sdm_tgl_lahir',
                'sdm_tempat_lahir',
                'sdm_kelamin',
                'sdm_alamat',
                'sdm_alamat_kelurahan',
                'sdm_alamat_kecamatan',
                'sdm_alamat_kota',
                'sdm_alamat_provinsi'
            )
            ->leftJoin('sdms', 'penempatan_no_absen', '=', 'sdm_no_absen')
            ->when($lingkupIjin, function ($query, $lingkupIjin) {
                $query->whereIn('penempatan_lokasi', $lingkupIjin);
            })
            ->where('penempatan_uuid', $uuid)
            ->first();
    }

    public static function ambilPerubahanStatusKontrakTerbaru()
    {
        extract(Rangka::obyekPermintaanRangka());

        return static::ambilDBPenempatanSDM()
            ->addSelect(
                'sdm_uuid',
                'sdm_no_absen',
                'sdm_nama'
            )
            ->join('sdms', 'sdm_no_absen', '=', 'penempatan_no_absen')
            ->where('penempatan_mulai', '>=', $app->date->today()->subDays(40)->toDateString())
            ->latest('penempatan_mulai');
    }

    public static function ambilSDMBaruGabung()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->select(
                'sdm_uuid',
                'sdm_no_absen',
                'sdm_nama',
                'sdm_no_permintaan',
                'sdm_no_ktp',
                'sdm_tgl_gabung',
                'penempatan_uuid',
                'penempatan_posisi',
                'penempatan_lokasi',
                'penempatan_kontrak'
            )
            ->from('sdms')
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini(), 'kontrak', function ($join) {
                $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            // ->whereNull('sdm_tgl_berhenti')
            ->where('sdm_tgl_gabung', '>=', $app->date->today()->subDays(40)->toDateString())
            ->latest('sdm_tgl_gabung');
    }

    public static function ambilSDMKeluarTerkini()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->select(
                'sdm_uuid',
                'sdm_no_absen',
                'sdm_nama',
                'sdm_jenis_berhenti',
                'sdm_tgl_berhenti',
                'sdm_ket_berhenti',
                'penempatan_uuid',
                'penempatan_posisi',
                'penempatan_lokasi',
                'penempatan_kontrak'
            )
            ->from('sdms')
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini(), 'kontrak', function ($join) {
                $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->where(function ($query) {
                $query->whereNotNull('sdm_tgl_berhenti')
                    ->orWhereNull('penempatan_kontrak');
            })
            ->where('sdm_tgl_berhenti', '>=', $app->date->today()->subDays(40)->toDateString())
            ->latest('sdm_tgl_berhenti');
    }

    public static function ambilLaporanPelanggaranSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->select(
                'langgar_uuid',
                'langgar_lap_no',
                'langgar_no_absen',
                'langgar_pelapor',
                'langgar_tanggal',
                'langgar_status',
                'langgar_isi',
                'langgar_keterangan'
            )
            ->from('pelanggaransdms');
    }

    public static function ambilSanksiSDMTerkini()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->select(
                'sanksi_no_absen',
                'sanksi_jenis',
                'sanksi_lap_no',
                'sanksi_selesai',
                'sanksi_mulai'
            )
            ->from('sanksisdms as p1')
            ->where('sanksi_mulai', '=', function ($query) {
                $query->selectRaw('MAX(sanksi_mulai)')
                    ->from('sanksisdms as p2')
                    ->whereColumn('p1.sanksi_no_absen', 'p2.sanksi_no_absen');
            });
    }

    public static function ambilDataLapPelanggaranSDM()
    {
        return static::ambilLaporanPelanggaranSDM()
            ->join('sdms as a', 'langgar_no_absen', '=', 'a.sdm_no_absen')
            ->join('sdms as b', 'langgar_pelapor', '=', 'b.sdm_no_absen')
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini(), 'kontrak_t', function ($join) {
                $join->on('langgar_no_absen', '=', 'kontrak_t.penempatan_no_absen');
            })
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini(), 'kontrak_p', function ($join) {
                $join->on('langgar_pelapor', '=', 'kontrak_p.penempatan_no_absen');
            })
            ->leftJoinSub(static::ambilSanksiSDMTerkini(), 'sanksilama', function ($join) {
                $join->on('langgar_no_absen', '=', 'sanksilama.sanksi_no_absen')
                    ->on('sanksilama.sanksi_selesai', '>=', 'langgar_tanggal')
                    ->on('langgar_lap_no', '!=', 'sanksilama.sanksi_lap_no');
            })
            ->leftJoin('sanksisdms', function ($join) {
                $join->on('langgar_no_absen', '=', 'sanksisdms.sanksi_no_absen')
                    ->on('langgar_lap_no', '=', 'sanksisdms.sanksi_lap_no');
            });
    }

    public static function ambilDataPelanggaranSDM($uuid, $lingkupIjin)
    {
        return static::ambilDataLapPelanggaranSDM()
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->whereIn('kontrak_t.penempatan_lokasi', $lingkupIjin)
                        ->orWhereIn('kontrak_p.penempatan_lokasi', $lingkupIjin);
                });
            })
            ->where('langgar_uuid', $uuid)->first();
    }

    public static function ambilDataPelanggaran_SanksiSDM()
    {
        return static::ambilDataLapPelanggaranSDM()
            ->addSelect(
                'a.sdm_uuid as langgar_tsdm_uuid',
                'a.sdm_nama as langgar_tsdm_nama',
                'a.sdm_tgl_berhenti as langgar_tsdm_tgl_berhenti',
                'kontrak_t.penempatan_lokasi as langgar_tlokasi',
                'kontrak_t.penempatan_posisi as langgar_tposisi',
                'kontrak_t.penempatan_kontrak as langgar_tkontrak',
                'b.sdm_uuid as langgar_psdm_uuid',
                'b.sdm_nama as langgar_psdm_nama',
                'b.sdm_tgl_berhenti as langgar_psdm_tgl_berhenti',
                'kontrak_p.penempatan_lokasi as langgar_plokasi',
                'kontrak_p.penempatan_posisi as langgar_pposisi',
                'kontrak_p.penempatan_kontrak as langgar_pkontrak',
                'sanksilama.sanksi_jenis as sanksi_aktif_sebelumnya',
                'sanksilama.sanksi_lap_no as lap_no_sebelumnya',
                'sanksilama.sanksi_mulai as sanksi_mulai_sebelumnya',
                'sanksilama.sanksi_selesai as sanksi_selesai_sebelumnya',
                'sanksisdms.sanksi_uuid as final_sanksi_uuid',
                'sanksisdms.sanksi_jenis as final_sanksi_jenis',
                'sanksisdms.sanksi_mulai as final_sanksi_mulai',
                'sanksisdms.sanksi_selesai as final_sanksi_selesai',
                'sanksisdms.sanksi_tambahan as final_sanksi_tambahan',
                'sanksisdms.sanksi_keterangan as final_sanksi_keterangan'
            );
    }

    public static function ambilPelanggaranSDMTerkini()
    {
        return static::ambilDataPelanggaran_SanksiSDM()
            ->whereNull('sanksisdms.sanksi_jenis')
            ->where('langgar_status', '=', 'DIPROSES')
            ->orderBy('langgar_lap_no', 'desc');
    }

    public static function ambilSanksiSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->select(
                'sanksi_uuid',
                'sanksi_no_absen',
                'sanksi_jenis',
                'sanksi_mulai',
                'sanksi_selesai',
                'sanksi_lap_no',
                'sanksi_tambahan',
                'sanksi_keterangan'
            )
            ->from('sanksisdms');
    }

    public static function ambilDataSanksiSDM($uuid, $lingkupIjin)
    {
        return static::ambilSanksiSDM()
            ->addSelect('langgar_status')
            ->leftJoin('pelanggaransdms', 'sanksi_lap_no', '=', 'langgar_lap_no')
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini(), 'kontrak_t', function ($join) {
                $join->on('sanksi_no_absen', '=', 'kontrak_t.penempatan_no_absen');
            })
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->whereIn('kontrak_t.penempatan_lokasi', $lingkupIjin);
                });
            })
            ->where('sanksi_uuid', $uuid)->first();
    }

    public static function tambahDataSanksiSDM($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('sanksisdms')->insert($data);
    }

    public static function ubahDataSanksiSDM($uuid, $data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('sanksisdms')->where('sanksi_uuid', $uuid)->update($data);
    }

    public static function tambahDataNilaiSDM($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('penilaiansdms')->insert($data);
    }

    public static function ubahDataNilaiSDM($uuid, $data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('penilaiansdms')->where('nilaisdm_uuid', $uuid)->update($data);
    }

    public static function ambilPengingatSanksiSDMTerkini()
    {
        extract(Rangka::obyekPermintaanRangka());

        return static::ambilDBSanksiSDM()
            ->whereNull('a.sdm_tgl_berhenti')
            ->where('sanksi_selesai', '>=', $app->date->today()->format('Y-m-d'))
            ->latest('sanksi_selesai');
    }

    public static function ambilDataSanksi_PelanggaranSDM($uuid, $lingkupIjin)
    {
        return static::ambilDBSanksiSDM()
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->whereIn('kontrak_t.penempatan_lokasi', $lingkupIjin)
                        ->orWhereIn('kontrak_p.penempatan_lokasi', $lingkupIjin);
                });
            })
            ->where('sanksi_uuid', $uuid)->first();
    }

    public static function ambilPenilaianSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->select(
                'nilaisdm_uuid',
                'nilaisdm_no_absen',
                'nilaisdm_tahun',
                'nilaisdm_periode',
                'nilaisdm_bobot_hadir',
                'nilaisdm_bobot_sikap',
                'nilaisdm_bobot_target',
                'nilaisdm_tindak_lanjut',
                'nilaisdm_keterangan'
            )
            ->from('penilaiansdms');
    }

    public static function ambilDBPenilaianSDM()
    {
        return static::ambilPenilaianSDM()
            ->addSelect(
                'sdm_uuid',
                'sdm_nama',
                'sdm_tgl_berhenti',
                'penempatan_posisi',
                'penempatan_lokasi',
                'penempatan_kontrak',
            )
            ->selectRaw('(IFNULL(nilaisdm_bobot_hadir, 0) + IFNULL(nilaisdm_bobot_sikap, 0) + IFNULL(nilaisdm_bobot_target, 0)) as nilaisdm_total')
            ->leftJoin('sdms', 'nilaisdm_no_absen', '=', 'sdm_no_absen')
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini(), 'kontrak', function ($join) {
                $join->on('nilaisdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            });
    }

    public static function cariPenilaianSDM($permintaan, $kataKunci, $uuid, $lingkupIjin, $uruts)
    {
        return static::ambilDBPenilaianSDM()
            ->when($permintaan->nilaisdm_penempatan, function ($query) use ($permintaan) {
                $query->where(function ($group) use ($permintaan) {
                    $group->whereIn('kontrak.penempatan_lokasi', (array) $permintaan->nilaisdm_penempatan);
                });
            })
            ->when($permintaan->nilaisdm_kontrak, function ($query) use ($permintaan) {
                $query->where(function ($group) use ($permintaan) {
                    $group->whereIn('kontrak.penempatan_kontrak', (array) $permintaan->nilaisdm_kontrak);
                });
            })
            ->when($permintaan->nilaisdm_tahun, function ($query) use ($permintaan) {
                $query->where(function ($group) use ($permintaan) {
                    $group->whereIn('nilaisdm_tahun', (array) $permintaan->nilaisdm_tahun);
                });
            })
            ->when($permintaan->nilaisdm_periode, function ($query) use ($permintaan) {
                $query->where(function ($group) use ($permintaan) {
                    $group->whereIn('nilaisdm_periode', (array) $permintaan->nilaisdm_periode);
                });
            })
            ->when($kataKunci, function ($query, $kataKunci) {
                $query->where(function ($group) use ($kataKunci) {
                    $group->where('nilaisdm_no_absen', 'like', '%'.$kataKunci.'%')
                        ->orWhere('sdm_nama', 'like', '%'.$kataKunci.'%')
                        ->orWhere('nilaisdm_tindak_lanjut', 'like', '%'.$kataKunci.'%')
                        ->orWhere('nilaisdm_keterangan', 'like', '%'.$kataKunci.'%');
                });
            })
            ->when($permintaan->tgl_sanksi_mulai && $permintaan->tgl_sanksi_sampai, function ($query) use ($permintaan) {
                $query->whereBetween('sanksi_mulai', [$permintaan->tgl_sanksi_mulai, $permintaan->tgl_sanksi_sampai]);
            })
            ->when($uuid, function ($query) use ($uuid) {
                $query->where('sdm_uuid', $uuid);
            })
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->whereIn('kontrak.penempatan_lokasi', $lingkupIjin);
                });
            })
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->orderBy('penilaiansdms.id', 'desc');
                }
            );
    }

    public static function ambilDataPenilaianSDM($uuid, $lingkupIjin)
    {
        return static::ambilDBPenilaianSDM()
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->whereIn('kontrak.penempatan_lokasi', $lingkupIjin);
                });
            })
            ->where('nilaisdm_uuid', $uuid)->first();
    }

    public static function contohImporSanksiSDM($lingkupIjin)
    {
        return static::ambilDBPenilaianSDM()
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->whereIn('kontrak_t.penempatan_lokasi', $lingkupIjin);
                });
            })
            ->orderBy('penilaiansdms.id');
    }

    public static function contohImporPenempatanSDM($lingkup)
    {
        return static::ambilDBPenempatanSDM()
            ->addSelect('sdm_nama')
            ->join('sdms', 'penempatan_no_absen', '=', 'sdm_no_absen')
            ->whereNull('sdm_tgl_berhenti')
            ->when($lingkup, function ($c) use ($lingkup) {
                return $c->whereIn('penempatan_lokasi', $lingkup);
            })
            ->orderBy('penempatans.id');
    }

    public static function ambilPengingatPenilaianSDMTerkini()
    {
        extract(Rangka::obyekPermintaanRangka());

        $date = $app->date;

        return static::ambilPenilaianSDM()
            ->addSelect(
                'penempatan_lokasi',
                'penempatan_kontrak'
            )
            ->selectRaw('(IFNULL(nilaisdm_bobot_hadir, 0) + IFNULL(nilaisdm_bobot_sikap, 0) + IFNULL(nilaisdm_bobot_target, 0)) as nilaisdm_total')
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini(), 'kontrak_t', function ($join) {
                $join->on('nilaisdm_no_absen', '=', 'kontrak_t.penempatan_no_absen');
            })
            ->whereIn('nilaisdm_tahun', [$date->today()->format('Y'), $date->today()->subYear()->format('Y')]);
    }

    public static function ambilDBPosisiSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->select(
                'posisi_nama',
                'posisi_status'
            )
            ->from('posisis');
    }

    public static function saringKeluarMasukPosisiSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        return static::ambilDBPosisiSDM()
            ->addSelect(
                'posisi_uuid',
                'posisi_atasan',
                'posisi_wlkp',
                'posisi_keterangan',
                'posisi_dibuat'
            )
            ->selectRaw('COUNT(DISTINCT CASE WHEN sdm_tgl_berhenti IS NULL THEN sdm_no_absen END) jml_aktif, COUNT(DISTINCT CASE WHEN sdm_tgl_berhenti IS NOT NULL THEN sdm_no_absen END) jml_nonaktif')
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini(), 'kontrak', function ($join) {
                $join->on('posisi_nama', '=', 'kontrak.penempatan_posisi');
            })
            ->leftJoin('sdms', 'sdm_no_absen', '=', 'penempatan_no_absen')
            ->groupBy('posisi_nama')
            ->when($reqs->lokasi, function ($query) use ($reqs) {
                $query->whereIn('penempatan_lokasi', $reqs->lokasi);
            })
            ->when($reqs->kontrak, function ($query) use ($reqs) {
                $query->whereIn('penempatan_kontrak', $reqs->kontrak);
            });
    }

    public static function saringPosisiSDM($kataKunci, $uruts)
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->addSelect(
                'tsdm.*',
            )
            ->selectRaw('IF((jml_aktif + jml_nonaktif) > 0, (jml_nonaktif / (jml_nonaktif + jml_aktif)) * 100, 0) as pergantian')
            ->fromSub(static::saringKeluarMasukPosisiSDM(), 'tsdm')
            ->when($reqs->posisi_status, function ($query) use ($reqs) {
                $query->where('posisi_status', $reqs->posisi_status);
            })
            ->when($kataKunci, function ($query, $kataKunci) {
                $query->where(function ($group) use ($kataKunci) {
                    $group->where('posisi_nama', 'like', '%'.$kataKunci.'%')
                        ->orWhere('posisi_atasan', 'like', '%'.$kataKunci.'%')
                        ->orWhere('posisi_wlkp', 'like', '%'.$kataKunci.'%')
                        ->orWhere('posisi_keterangan', 'like', '%'.$kataKunci.'%');
                });
            })
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->latest('posisi_dibuat');
                }
            );
    }

    public static function tambahDataPosisiSDM($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('posisis')->insert($data);
    }

    public static function ubahDataPosisiSDM($data, $uuid)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('posisis')->where('posisi_uuid', $uuid)->update($data);
    }

    public static function cariDataSanksiSDM($permintaan, $hariIni, $kataKunci, $uuid, $lingkupIjin, $uruts)
    {
        return static::ambilDBSanksiSDM()
            ->when($permintaan->sanksi_jenis, function ($query) use ($permintaan) {
                $query->whereIn('sanksi_jenis', (array) $permintaan->sanksi_jenis);
            })
            ->when($permintaan->sanksi_status == 'AKTIF', function ($query) use ($hariIni) {
                $query->where('sanksi_selesai', '>=', $hariIni);
            })
            ->when($permintaan->sanksi_status == 'BERAKHIR', function ($query) use ($hariIni) {
                $query->where('sanksi_selesai', '<', $hariIni);
            })
            ->when($permintaan->sanksi_penempatan, function ($query) use ($permintaan) {
                $query->where(function ($group) use ($permintaan) {
                    $group->whereIn('kontrak_t.penempatan_lokasi', (array) $permintaan->sanksi_penempatan)
                        ->orWhereIn('kontrak_p.penempatan_lokasi', (array) $permintaan->sanksi_penempatan);
                });
            })
            ->when($permintaan->status_sdm, function ($query) use ($permintaan) {
                $query->where(function ($group) use ($permintaan) {
                    $group->whereIn('kontrak_t.penempatan_kontrak', (array) $permintaan->status_sdm);
                });
            })
            ->when($kataKunci, function ($query, $kataKunci) {
                $query->where(function ($group) use ($kataKunci) {
                    $group->where('sanksi_no_absen', 'like', '%'.$kataKunci.'%')
                        ->orWhere('a.sdm_nama', 'like', '%'.$kataKunci.'%')
                        ->orWhere('b.sdm_nama', 'like', '%'.$kataKunci.'%')
                        ->orWhere('sanksi_tambahan', 'like', '%'.$kataKunci.'%')
                        ->orWhere('sanksi_keterangan', 'like', '%'.$kataKunci.'%');
                });
            })
            ->when($permintaan->tgl_sanksi_mulai && $permintaan->tgl_sanksi_sampai, function ($query) use ($permintaan) {
                $query->whereBetween('sanksi_mulai', [$permintaan->tgl_sanksi_mulai, $permintaan->tgl_sanksi_sampai]);
            })
            ->when($uuid, function ($query) use ($uuid) {
                $query->where('a.sdm_uuid', $uuid);
            })
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->whereIn('kontrak_t.penempatan_lokasi', $lingkupIjin)
                        ->orWhereIn('kontrak_p.penempatan_lokasi', $lingkupIjin);
                });
            })
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->latest('sanksi_mulai');
                }
            );
    }

    public static function ambilDBSanksiSDM()
    {
        return static::ambilSanksiSDM()
            ->addSelect(
                'a.sdm_uuid as langgar_tsdm_uuid',
                'a.sdm_nama as langgar_tsdm_nama',
                'a.sdm_tgl_berhenti as langgar_tsdm_tgl_berhenti',
                'kontrak_t.penempatan_lokasi as langgar_tlokasi',
                'kontrak_t.penempatan_posisi as langgar_tposisi',
                'kontrak_t.penempatan_kontrak as langgar_tkontrak',
                'langgar_isi',
                'langgar_tanggal',
                'langgar_status',
                'langgar_pelapor',
                'langgar_keterangan',
                'b.sdm_uuid as langgar_psdm_uuid',
                'b.sdm_nama as langgar_psdm_nama',
                'b.sdm_tgl_berhenti as langgar_psdm_tgl_berhenti',
                'kontrak_p.penempatan_lokasi as langgar_plokasi',
                'kontrak_p.penempatan_posisi as langgar_pposisi',
                'kontrak_p.penempatan_kontrak as langgar_pkontrak'
            )
            ->join('sdms as a', 'sanksi_no_absen', '=', 'a.sdm_no_absen')
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini(), 'kontrak_t', function ($join) {
                $join->on('sanksi_no_absen', '=', 'kontrak_t.penempatan_no_absen');
            })
            ->leftJoin('pelanggaransdms', 'sanksi_lap_no', '=', 'langgar_lap_no')
            ->leftJoin('sdms as b', 'langgar_pelapor', '=', 'b.sdm_no_absen')
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini(), 'kontrak_p', function ($join) {
                $join->on('langgar_pelapor', '=', 'kontrak_p.penempatan_no_absen');
            });
    }

    public static function ambilDBSDMUtkKartuID($uuid)
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->select(
                'sdm_uuid',
                'sdm_no_absen',
                'sdm_nama',
                'sdm_telepon',
                'email',
                'penempatan_lokasi'
            )
            ->from('sdms')
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini(), 'kontrak', function ($join) {
                $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->where('sdm_uuid', $uuid)
            ->first();
    }

    public static function imporPosisiSDM($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $database = $app->db;

        $database->transaction(function () use ($database, $data) {
            $database->table('posisis')->upsert(
                $data,
                ['posisi_nama'],
                ['posisi_wlkp', 'posisi_status', 'posisi_keterangan', 'posisi_id_pengunggah', 'posisi_diunggah']
            );
        });
    }

    public static function imporSanksiSDM($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $database = $app->db;

        $database->transaction(function () use ($database, $data) {
            $database->table('sanksisdms')->upsert(
                $data,
                ['sanksi_no_absen', 'sanksi_jenis', 'sanksi_mulai'],
                ['sanksi_selesai', 'sanksi_tambahan', 'sanksi_keterangan', 'sanksi_id_pengunggah', 'nilaisdm_diunggah']
            );
        });
    }

    public static function imporNilaiSDM($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $database = $app->db;

        $database->transaction(function () use ($database, $data) {
            $database->table('penilaiansdms')->upsert(
                $data,
                ['nilaisdm_no_absen', 'nilaisdm_tahun', 'nilaisdm_periode'],
                ['nilaisdm_bobot_hadir', 'nilaisdm_bobot_sikap', 'nilaisdm_bobot_target', 'nilaisdm_tindak_lanjut', 'nilaisdm_keterangan', 'nilaisdm_id_pengunggah', 'nilaisdm_diunggah']
            );
        });
    }

    public static function imporPenempatanSDM($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $database = $app->db;

        $database->transaction(function () use ($database, $data) {
            $database->table('penempatans')->upsert(
                $data,
                ['penempatan_no_absen', 'penempatan_mulai'],
                ['penempatan_selesai', 'penempatan_ke', 'penempatan_lokasi', 'penempatan_posisi', 'penempatan_kategori', 'penempatan_kontrak', 'penempatan_pangkat', 'penempatan_golongan', 'penempatan_grup', 'penempatan_keterangan', 'penempatan_id_pengunggah', 'penempatan_diunggah']
            );
        });
    }

    public static function ambilDBPelanggaran_SanksiSDM($lingkupIjin = null)
    {
        return static::ambilDataPelanggaran_SanksiSDM()
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->whereIn('kontrak_t.penempatan_lokasi', $lingkupIjin)
                        ->orWhereIn('kontrak_p.penempatan_lokasi', $lingkupIjin);
                });
            });
    }

    public static function saringLapPelanggaranSDM($permintaan, $kataKunci, $uruts, $lingkupIjin)
    {
        return static::ambilDBPelanggaran_SanksiSDM($lingkupIjin)
            ->when($permintaan->langgar_proses == 'SELESAI', function ($query) {
                $query->whereNotNull('sanksisdms.sanksi_jenis')
                    ->where('langgar_status', '=', 'DIPROSES');
            })
            ->when($permintaan->langgar_proses == 'BELUM SELESAI', function ($query) {
                $query->whereNull('sanksisdms.sanksi_jenis')
                    ->where('langgar_status', '=', 'DIPROSES');
            })
            ->when($permintaan->langgar_status, function ($query) use ($permintaan) {
                $query->whereIn('langgar_status', (array) $permintaan->langgar_status);
            })
            ->when($permintaan->langgar_penempatan, function ($query) use ($permintaan) {
                $query->where(function ($group) use ($permintaan) {
                    $group->whereIn('kontrak_t.penempatan_lokasi', (array) $permintaan->langgar_penempatan)
                        ->orWhereIn('kontrak_p.penempatan_lokasi', (array) $permintaan->langgar_penempatan);
                });
            })
            ->when($permintaan->status_sdm, function ($query) use ($permintaan) {
                $query->where(function ($group) use ($permintaan) {
                    $group->whereIn('kontrak_t.penempatan_kontrak', (array) $permintaan->status_sdm);
                });
            })
            ->when($kataKunci, function ($query) use ($kataKunci) {
                $query->where(function ($group) use ($kataKunci) {
                    $group->where('langgar_lap_no', 'like', '%'.$kataKunci.'%')
                        ->orWhere('langgar_no_absen', 'like', '%'.$kataKunci.'%')
                        ->orWhere('langgar_pelapor', 'like', '%'.$kataKunci.'%')
                        ->orWhere('a.sdm_nama', 'like', '%'.$kataKunci.'%')
                        ->orWhere('b.sdm_nama', 'like', '%'.$kataKunci.'%')
                        ->orWhere('langgar_isi', 'like', '%'.$kataKunci.'%')
                        ->orWhere('langgar_keterangan', 'like', '%'.$kataKunci.'%');
                });
            })
            ->when($permintaan->tgl_langgar_mulai && $permintaan->tgl_langgar_sampai, function ($query) use ($permintaan) {
                $query->whereBetween('langgar_tanggal', [$permintaan->tgl_langgar_mulai, $permintaan->tgl_langgar_sampai]);
            })
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->orderBy('langgar_lap_no', 'desc');
                }
            );
    }

    public static function ambilPelanggaran_SanksiSDM($uuid, $lingkupIjin)
    {
        return static::ambilDBPelanggaran_SanksiSDM($lingkupIjin)
            ->where('langgar_uuid', $uuid)
            ->first();
    }

    public static function ambilUrutanPelanggaranSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        return static::ambilLaporanPelanggaranSDM()
            ->selectRaw('MAX(langgar_lap_no) as maxno, COUNT(langgar_lap_no) as countno')
            ->where(function ($group) {
                $group->whereYear('langgar_dibuat', date('Y'))
                    ->whereMonth('langgar_dibuat', date('m'));
            })
            ->orWhere('langgar_lap_no', 'like', date('Y').date('m').'%')
            ->first();
    }

    public static function ambilIDTambahPenempatanSDM($lingkupIjin, $uuid)
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->select('sdm_uuid', 'sdm_no_absen', 'sdm_nama', 'penempatan_lokasi')
            ->from('sdms')
            ->leftJoin('penempatans', 'sdm_no_absen', '=', 'penempatan_no_absen')
            ->when($lingkupIjin, function ($query, $lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->orWhereIn('penempatan_lokasi', $lingkupIjin)
                        ->orWhereNull('penempatan_lokasi');
                });
            })
            ->where('sdm_uuid', $uuid)
            ->first();
    }

    public static function ambilIDUbah_HapusPenempatanSDM($lingkupIjin, $uuid)
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->select(
                'sdm_no_absen',
                'sdm_nama',
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
                'penempatan_keterangan'
            )
            ->from('penempatans')
            ->join('sdms', 'penempatan_no_absen', '=', 'sdm_no_absen')
            ->when($lingkupIjin, function ($query, $lingkupIjin) {
                $query->whereIn('penempatan_lokasi', $lingkupIjin);
            })
            ->where('penempatan_uuid', $uuid)
            ->first();
    }

    public static function tambahDataPenempatanSDM($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('penempatans')->insert($data);
    }

    public static function ubahDataPenempatanSDM($data, $uuid)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('penempatans')->where('penempatan_uuid', $uuid)->update($data);
    }

    public static function aksesAkun($uuid)
    {
        return static::ambilDBSDM()
            ->select(
                'sdm_no_absen',
                'sdm_id_atasan'
            )
            ->where('sdm_uuid', $uuid)
            ->first();
    }

    public static function ambilKepuasanSDM()
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->db->query()
            ->select(
                'surveysdm_uuid',
                'surveysdm_no_absen',
                'surveysdm_tahun',
                'surveysdm_1',
                'surveysdm_2',
                'surveysdm_3',
                'surveysdm_4',
                'surveysdm_5',
                'surveysdm_6',
                'surveysdm_7',
                'surveysdm_8',
                'surveysdm_9',
                'surveysdm_10',
                'surveysdm_skor',
                'surveysdm_saran',
                'surveysdm_keterangan'
            )
            ->from('kepuasansdms');
    }

    public static function ambilDBKepuasanSDM()
    {
        return static::ambilKepuasanSDM()
            ->addSelect(
                'sdm_uuid',
                'sdm_nama',
                'sdm_tgl_berhenti',
                'penempatan_posisi',
                'penempatan_lokasi',
                'penempatan_kontrak',
            )
            ->selectRaw("(IF (surveysdm_skor = 5, 'SANGAT PUAS', IF (surveysdm_skor >= 4, 'PUAS', IF (surveysdm_skor >= 3, 'RAGU', IF (surveysdm_skor >= 2, 'KURANG PUAS', 'TIDAK PUAS'))))) as surveysdm_klasifikasi")
            ->leftJoin('sdms', 'surveysdm_no_absen', '=', 'sdm_no_absen')
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini(), 'kontrak', function ($join) {
                $join->on('surveysdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            });
    }

    public static function cariKepuasanSDM($permintaan, $kataKunci, $uuid, $lingkupIjin, $uruts)
    {
        return static::ambilDBKepuasanSDM()
            ->when($permintaan->surveysdm_penempatan, function ($query) use ($permintaan) {
                $query->where(function ($group) use ($permintaan) {
                    $group->whereIn('kontrak.penempatan_lokasi', (array) $permintaan->surveysdm_penempatan);
                });
            })
            ->when($permintaan->surveysdm_kontrak, function ($query) use ($permintaan) {
                $query->where(function ($group) use ($permintaan) {
                    $group->whereIn('kontrak.penempatan_kontrak', (array) $permintaan->surveysdm_kontrak);
                });
            })
            ->when($permintaan->surveysdm_tahun, function ($query) use ($permintaan) {
                $query->where(function ($group) use ($permintaan) {
                    $group->whereIn('surveysdm_tahun', (array) $permintaan->surveysdm_tahun);
                });
            })
            ->when($kataKunci, function ($query, $kataKunci) {
                $query->where(function ($group) use ($kataKunci) {
                    $group->where('surveysdm_no_absen', 'like', '%'.$kataKunci.'%')
                        ->orWhere('sdm_nama', 'like', '%'.$kataKunci.'%')
                        ->orWhere('surveysdm_keterangan', 'like', '%'.$kataKunci.'%');
                });
            })
            ->when($uuid, function ($query) use ($uuid) {
                $query->where('sdm_uuid', $uuid);
            })
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->whereIn('kontrak.penempatan_lokasi', $lingkupIjin);
                });
            })
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->orderBy('kepuasansdms.id', 'desc');
                }
            );
    }

    public static function tambahDataKepuasanSDM($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('kepuasansdms')->insert($data);
    }

    public static function ambilDataKepuasanSDM($uuid, $lingkupIjin)
    {
        return static::ambilDBKepuasanSDM()
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->whereIn('kontrak.penempatan_lokasi', $lingkupIjin);
                });
            })
            ->where('surveysdm_uuid', $uuid)->first();
    }

    public static function ubahDataKepuasanSDM($uuid, $data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $app->db->table('kepuasansdms')->where('surveysdm_uuid', $uuid)->update($data);
    }

    public static function ambilPengingatKepuasanSDMTerkini()
    {
        extract(Rangka::obyekPermintaanRangka());

        $date = $app->date;

        return static::ambilKepuasanSDM()
            ->addSelect(
                'penempatan_lokasi',
                'penempatan_kontrak'
            )
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini(), 'kontrak', function ($join) {
                $join->on('surveysdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->whereIn('surveysdm_tahun', [$date->today()->format('Y'), $date->today()->subYear()->format('Y')]);
    }
}
