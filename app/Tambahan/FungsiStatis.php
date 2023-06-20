<?php

namespace App\Tambahan;

class FungsiStatis
{
    public static function ambilCacheAtur()
    {
        $app = app();
        $database = $app->db;

        return $app->cache->rememberForever('SetelanAtur', function () use ($database) {
            return $database->query()->select('atur_jenis', 'atur_butir', 'atur_status')->from('aturs')->get();
        });
    }

    public static function ambilCachePermintaanTambahSDM()
    {
        $app = app();
        $database = $app->db;

        $tabelSub = $database->query()->select('tambahsdm_uuid', 'tambahsdm_no', 'tambahsdm_sdm_id', 'tambahsdm_penempatan', 'tambahsdm_posisi', 'tambahsdm_jumlah', $database->raw('COUNT(a.sdm_no_permintaan) as tambahsdm_terpenuhi, MAX(a.sdm_tgl_gabung) as pemenuhan_terkini'), 'tambahsdm_status', 'b.sdm_uuid', 'b.sdm_nama', 'tambahsdm_tgl_diusulkan', 'tambahsdm_tgl_dibutuhkan', 'tambahsdm_alasan', 'tambahsdm_keterangan')
            ->from('tambahsdms')->leftJoin('sdms as a', 'tambahsdm_no', '=', 'a.sdm_no_permintaan')
            ->join('sdms as b', 'tambahsdm_sdm_id', '=', 'b.sdm_no_absen')
            ->groupBy('tambahsdm_no');

        return $app->cache->rememberForever('PermintaanTambahSDM', function () use ($database, $tabelSub) {
            return $database->query()->addSelect('tsdm.*')->fromSub($tabelSub, 'tsdm')
                ->where('tambahsdm_status', 'DISETUJUI')->whereColumn('tambahsdm_jumlah', '>', 'tambahsdm_terpenuhi')
                ->latest('tambahsdm_tgl_diusulkan')
                ->get();
        });
    }

    public static function ambilCachePosisiSDM()
    {
        $app = app();
        $database = $app->db;

        return $app->cache->rememberForever('PosisiSDM', function () use ($database) {
            return $database->query()->select('posisi_nama', 'posisi_status')->from('posisis')
                // ->where('posisi_status', 'AKTIF')
                ->orderBy('posisi_nama')->get();
        });
    }

    public static function ambilCacheSDM()
    {
        $app = app();
        $database = $app->db;

        $kontrak = $database->query()->select('penempatan_no_absen', 'penempatan_lokasi', 'penempatan_posisi')->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
            $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
        });

        return $app->cache->rememberForever('NamaNIKSDM', function () use ($kontrak, $database) {
            return $database->query()->select('sdm_uuid', 'sdm_no_absen', 'sdm_tgl_berhenti', 'sdm_nama', 'penempatan_lokasi', 'penempatan_posisi', 'sdm_id_atasan')->from('sdms')
                ->joinSub($kontrak, 'kontrak', function ($join) {
                    $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
                })
                ->whereNull('sdm_tgl_berhenti')
                ->orderBy('sdm_no_absen')->get();
        });
    }

    public static function ambilCachePelanggaranSDM()
    {
        $app = app();
        $database = $app->db;

        $dataDasar = $database->query()->select('langgar_uuid', 'langgar_lap_no', 'langgar_no_absen', 'langgar_pelapor', 'langgar_tanggal', 'langgar_status', 'langgar_isi', 'langgar_keterangan')->from('pelanggaransdms');

        $kontrak = $database->query()->select('penempatan_uuid', 'penempatan_no_absen', 'penempatan_posisi', 'penempatan_lokasi', 'penempatan_kontrak', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_keterangan')
            ->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
                $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
            });

        $sanksi = $database->query()->select('sanksi_no_absen', 'sanksi_jenis', 'sanksi_lap_no', 'sanksi_selesai')
            ->from('sanksisdms as p1')->where('sanksi_selesai', '=', function ($query) use ($database) {
                $query->select($database->raw('MAX(sanksi_selesai)'))->from('sanksisdms as p2')->whereColumn('p1.sanksi_no_absen', 'p2.sanksi_no_absen');
            });

        return $app->cache->rememberForever('PelanggaranSDM', function () use ($dataDasar, $kontrak, $sanksi) {
            return $dataDasar->addSelect('a.sdm_uuid as langgar_tsdm_uuid', 'a.sdm_nama as langgar_tsdm_nama', 'a.sdm_tgl_berhenti as langgar_tsdm_tgl_berhenti', 'kontrak_t.penempatan_lokasi as langgar_tlokasi', 'kontrak_t.penempatan_posisi as langgar_tposisi', 'kontrak_t.penempatan_kontrak as langgar_tkontrak', 'b.sdm_uuid as langgar_psdm_uuid', 'b.sdm_nama as langgar_psdm_nama', 'b.sdm_tgl_berhenti as langgar_psdm_tgl_berhenti', 'kontrak_p.penempatan_lokasi as langgar_plokasi', 'kontrak_p.penempatan_posisi as langgar_pposisi', 'kontrak_p.penempatan_kontrak as langgar_pkontrak', 'sanksilama.sanksi_jenis as sanksi_aktif_sebelumnya', 'sanksilama.sanksi_lap_no as lap_no_sebelumnya', 'sanksilama.sanksi_selesai as sanksi_selesai_sebelumnya', 'sanksisdms.sanksi_uuid as final_sanksi_uuid', 'sanksisdms.sanksi_jenis as final_sanksi_jenis', 'sanksisdms.sanksi_mulai as final_sanksi_mulai', 'sanksisdms.sanksi_selesai as final_sanksi_selesai', 'sanksisdms.sanksi_tambahan as final_sanksi_tambahan', 'sanksisdms.sanksi_keterangan as final_sanksi_keterangan')
                ->join('sdms as a', 'langgar_no_absen', '=', 'a.sdm_no_absen')
                ->join('sdms as b', 'langgar_pelapor', '=', 'b.sdm_no_absen')
                ->leftJoinSub($kontrak, 'kontrak_t', function ($join) {
                    $join->on('langgar_no_absen', '=', 'kontrak_t.penempatan_no_absen');
                })
                ->leftJoinSub($kontrak, 'kontrak_p', function ($join) {
                    $join->on('langgar_pelapor', '=', 'kontrak_p.penempatan_no_absen');
                })
                ->leftJoinSub($sanksi, 'sanksilama', function ($join) {
                    $join->on('langgar_no_absen', '=', 'sanksilama.sanksi_no_absen')->on('sanksilama.sanksi_selesai', '>=', 'langgar_tanggal')->on('langgar_lap_no', '!=', 'sanksilama.sanksi_lap_no');
                })
                ->leftJoin('sanksisdms', function ($join) {
                    $join->on('langgar_no_absen', '=', 'sanksisdms.sanksi_no_absen')->on('langgar_lap_no', '=', 'sanksisdms.sanksi_lap_no');
                })
                ->whereNull('sanksisdms.sanksi_jenis')
                ->where('langgar_status', '=', 'DIPROSES')
                ->orderBy('langgar_lap_no', 'desc')->get();
        });
    }

    public static function hapusBerkasLama()
    {
        $app = app();
        $storage = $app->filesystem;

        $berkas_unduh = array_filter($storage->files('unduh'), function ($berkas) {
            return $berkas !== 'unduh/.gitignore';
        });

        if ($berkas_unduh) {
            $date = $app->date;

            foreach ($berkas_unduh as $lama) {
                $tgl_berkas = $date->parse($storage->lastModified($lama));
                $batas_waktu = $date->today()->subDays(2);
                if ($tgl_berkas->lte($batas_waktu)) {
                    $storage->delete($lama);
                }
            }
        }
    }

    public static function hapusCacheAtur()
    {
        cache()->forget('SetelanAtur');
    }

    public static function hapusCachePermintaanTambahSDM()
    {
        cache()->forget('PermintaanTambahSDM');
    }

    public static function hapusCachePosisiSDM()
    {
        cache()->forget('PosisiSDM');
    }

    public static function hapusCacheSDMUmum()
    {
        $app = app();
        $cache = $app->cache;
        $hariIni = $app->date->today();
        $cache->forget('NamaNIKSDM');
        $cache->forget('PermintaanTambahSDM');
        $cache->forget('PosisiSDM');
        $cache->forget('SDMUlangTahun - ' . $hariIni->format('Y-m'));
        $cache->forget('PengingatKontrak - ' . $hariIni->format('Y-m-d'));
        $cache->forget('PengingatNon-Aktif - ' . $hariIni->format('Y-m-d'));
        $cache->forget('PengingatAkunBaru - ' . $hariIni->format('Y-m-d'));
        $cache->forget('PerubahanStatus - ' . $hariIni->format('Y-m-d'));
    }

    public static function statusBerhasil()
    {
        return 'Data berhasil diperbarui. Mohon periksa ulang data.';
    }
}
