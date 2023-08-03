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
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini()
                ->addSelect(
                    'penempatan_uuid',
                    'penempatan_mulai',
                    'penempatan_selesai',
                    'penempatan_ke'
                ), 'kontrak', function ($join) {
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
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini()
                ->addSelect('penempatan_uuid'), 'kontrak', function ($join) {
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
            ->leftJoinSub(static::ambilDBPenempatanSDMTerkini()
                ->addSelect('penempatan_uuid'), 'kontrak', function ($join) {
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

        $database = $app->db;

        return $database->query()
            ->select(
                'sanksi_no_absen',
                'sanksi_jenis',
                'sanksi_lap_no',
                'sanksi_selesai',
                'sanksi_mulai'
            )
            ->from('sanksisdms as p1')
            ->where('sanksi_mulai', '=', function ($query) use ($database) {
                $query->select($database->raw('MAX(sanksi_mulai)'))
                    ->from('sanksisdms as p2')
                    ->whereColumn('p1.sanksi_no_absen', 'p2.sanksi_no_absen');
            });
    }

    public static function ambilPelanggaranSDMTerkini()
    {
        return static::ambilLaporanPelanggaranSDM()
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
                'sanksilama.sanksi_selesai as sanksi_selesai_sebelumnya',
                'sanksisdms.sanksi_uuid as final_sanksi_uuid',
                'sanksisdms.sanksi_jenis as final_sanksi_jenis',
                'sanksisdms.sanksi_mulai as final_sanksi_mulai',
                'sanksisdms.sanksi_selesai as final_sanksi_selesai',
                'sanksisdms.sanksi_tambahan as final_sanksi_tambahan',
                'sanksisdms.sanksi_keterangan as final_sanksi_keterangan'
            )
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
            })
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

    public static function ambilPengingatSanksiSDMTerkini()
    {
        extract(Rangka::obyekPermintaanRangka());

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
            })
            ->whereNull('a.sdm_tgl_berhenti')
            ->where('sanksi_selesai', '>=', $app->date->today()->format('Y-m-d'))
            ->latest('sanksi_selesai');
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

    public static function ambilPengingatPenilaianSDMTerkini()
    {
        extract(Rangka::obyekPermintaanRangka());

        $date = $app->date;

        return static::ambilPenilaianSDM()
            ->addSelect(
                'penempatan_lokasi',
                'penempatan_kontrak',
                $app->db->raw('(IFNULL(nilaisdm_bobot_hadir, 0) + IFNULL(nilaisdm_bobot_sikap, 0) + IFNULL(nilaisdm_bobot_target, 0)) as nilaisdm_total')
            )
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
                'posisi_dibuat',
                $app->db->raw('COUNT(DISTINCT CASE WHEN sdm_tgl_berhenti IS NULL THEN sdm_no_absen END) jml_aktif, COUNT(DISTINCT CASE WHEN sdm_tgl_berhenti IS NOT NULL THEN sdm_no_absen END) jml_nonaktif')
            )
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

        $database = $app->db;

        return $database->query()
            ->addSelect(
                'tsdm.*',
                $database->raw('IF((jml_aktif + jml_nonaktif) > 0, (jml_nonaktif / (jml_nonaktif + jml_aktif)) * 100, 0) as pergantian')
            )
            ->fromSub(static::saringKeluarMasukPosisiSDM(), 'tsdm')
            ->when($reqs->posisi_status, function ($query) use ($reqs) {
                $query->where('posisi_status', $reqs->posisi_status);
            })
            ->when($kataKunci, function ($query, $kataKunci) {
                $query->where(function ($group) use ($kataKunci) {
                    $group->where('posisi_nama', 'like', '%' . $kataKunci . '%')
                        ->orWhere('posisi_atasan', 'like', '%' . $kataKunci . '%')
                        ->orWhere('posisi_wlkp', 'like', '%' . $kataKunci . '%')
                        ->orWhere('posisi_keterangan', 'like', '%' . $kataKunci . '%');
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
}
