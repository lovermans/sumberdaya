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

    public static function hapusCachePelanggaranSDM()
    {
        cache()->forget('PengingatPelanggaran');
    }

    public static function hapusCacheSanksiSDM()
    {
        cache()->forget('PengingatSanksi - ' . app()->date->today()->format('Y-m-d'));
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
        $cache->forget('PengingatPelanggaran');
        $cache->forget('PengingatSanksi - ' . $hariIni->format('Y-m-d'));
    }

    public static function statusBerhasil()
    {
        return 'Data berhasil diperbarui. Mohon periksa ulang data.';
    }
}
