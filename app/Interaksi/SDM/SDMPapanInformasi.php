<?php

namespace App\Interaksi\SDM;

use App\Interaksi\Rangka;
use App\Interaksi\SDM\SDMCache;
use App\Interaksi\SDM\SDMDBQuery;

class SDMPapanInformasi
{
    public static function pengingatUltah()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless(str()->contains($pengguna?->sdm_hak_akses, 'SDM'), 403, 'Akses dibatasi hanya untuk Pengguna Aplikasi SDM.');

        $linkupIjin = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $linkupIjin));

        SDMCache::hapusCacheSDMUltah();

        $cache_ulangTahuns = SDMCache::ambilCacheSDMUltah();
        $penemPengguna = SDMDBQuery::ambilLokasiPenempatanSDM()->first();

        $ulangTahuns = $cache_ulangTahuns->when($linkupIjin, function ($c) use ($lingkup) {
            return $c->whereIn('penempatan_lokasi', [null, ...$lingkup]);
        })->when(str()->contains($pengguna->sdm_hak_akses, 'SDM-PENGGUNA'), function ($c) use ($penemPengguna) {
            return $c->whereIn('penempatan_lokasi', [$penemPengguna->penempatan_lokasi]);
        });

        $data = [
            'ulangTahuns' => $ulangTahuns ?? null,
            'jumlahOS' => $ulangTahuns->whereNotNull('penempatan_kontrak')->filter(function ($item) {
                return false !== stristr($item->penempatan_kontrak, 'OS-');
            })->count(),
            'jumlahOrganik' => $ulangTahuns->whereNotNull('penempatan_kontrak')->filter(function ($item) {
                return false === stristr($item->penempatan_kontrak, 'OS-');
            })->count()
        ];

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')
            ->make($app->view->make('sdm.papan-informasi.sdm-ultah', $data))
            ->withHeaders(['Vary' => 'Accept']);
    }

    public static function pengingatPermintaanTambahSDM()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless(str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $linkupIjin = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $linkupIjin));

        $perminSDMS = SDMCache::ambilCachePermintaanTambahSDM()
            ->filter(function ($item) {
                return $item->tambahsdm_jumlah > $item->tambahsdm_terpenuhi;
            })
            ->when($linkupIjin, function ($c) use ($lingkup) {
                return $c->whereIn('tambahsdm_penempatan', [...$lingkup]);
            });

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')
            ->make($app->view->make('sdm.papan-informasi.permintaan-tambah-sdm', ['perminSDMS' => $perminSDMS ?? null]))
            ->withHeaders(['Vary' => 'Accept']);
    }

    public static function pengingatPKWTHabis()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless(str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $linkupIjin = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $linkupIjin));
        $date = $app->date;

        SDMCache::hapusCachePKWTHabis();

        $cache_kontraks = SDMCache::ambilCachePKWTHabis();

        $kontraks = $cache_kontraks->when($linkupIjin, function ($c) use ($lingkup) {
            return $c->whereIn('penempatan_lokasi', [...$lingkup]);
        });

        $data = [
            'kontraks' => $kontraks ?? null,
            'jmlAkanHabis' => $kontraks->filter(function ($v, $k) use ($date) {
                return $date->make($v->penempatan_selesai)->diffInDays($date->today(), false) <= 0;
            })->count(),
            'jmlKadaluarsa' => $kontraks->filter(function ($v, $k) use ($date) {
                return $date->make($v->penempatan_selesai)->diffInDays($date->today(), false) >= 0;
            })->count()
        ];

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')
            ->make($app->view->make('sdm.papan-informasi.pkwt-perlu-ditinjau', $data))
            ->withHeaders(['Vary' => 'Accept']);
    }

    public static function pengingatPerubahanStatusSDMTerbaru()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless(str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $linkupIjin = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $linkupIjin));

        SDMCache::hapusCachePerubahanStatusKontrakTerbaru();

        $cache_statuses = SDMCache::ambilCachePerubahanStatusKontrakTerbaru();

        $statuses = $cache_statuses->when($linkupIjin, function ($c) use ($lingkup) {
            return $c->whereIn('penempatan_lokasi', [...$lingkup]);
        });

        $data = [
            'statuses' => $statuses ?? null,
            'jumlahOS' => $statuses->filter(function ($item) {
                return false !== stristr($item->penempatan_kontrak, 'OS-');
            })->count(),
            'jumlahOrganik' => $statuses->filter(function ($item) {
                return false === stristr($item->penempatan_kontrak, 'OS-');
            })->count()
        ];

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')
            ->make($app->view->make('sdm.papan-informasi.perubahan-status', $data))
            ->withHeaders(['Vary' => 'Accept']);
    }

    public static function pengingatSDMGabungTerbaru()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless(str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $linkupIjin = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $linkupIjin));

        SDMCache::hapusCacheSDMBaru();

        $cache_barus = SDMCache::ambilCacheSDMBaru();

        $barus = $cache_barus->when($linkupIjin, function ($c) use ($lingkup) {
            return $c->whereIn('penempatan_lokasi', [null, ...$lingkup]);
        });

        $data = [
            'barus' => $barus ?? null,
            'jumlahOS' => $barus->whereNotNull('penempatan_kontrak')->filter(function ($item) {
                return false !== stristr($item->penempatan_kontrak, 'OS-');
            })->count(),
            'jumlahOrganik' => $barus->whereNotNull('penempatan_kontrak')->filter(function ($item) {
                return false === stristr($item->penempatan_kontrak, 'OS-');
            })->count(),
            'belumDitempatkan' => $barus->whereNull('penempatan_kontrak')->count()
        ];

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')
            ->make($app->view->make('sdm.papan-informasi.sdm-baru', $data))
            ->withHeaders(['Vary' => 'Accept']);
    }

    public static function pengingatSDMKeluarTerbaru()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless(str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $linkupIjin = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $linkupIjin));

        SDMCache::hapusCacheSDMKeluarTerkini();

        $cache_berhentis = SDMCache::ambilCacheSDMKeluarTerkini();

        $berhentis = $cache_berhentis->when($linkupIjin, function ($c) use ($lingkup) {
            return $c->whereIn('penempatan_lokasi', [null, ...$lingkup]);
        });

        $data = [
            'berhentis' => $berhentis ?? null,
            'jumlahOS' => $berhentis->whereNotNull('penempatan_kontrak')->filter(function ($item) {
                return false !== stristr($item->penempatan_kontrak, 'OS-');
            })->count(),
            'jumlahOrganik' => $berhentis->whereNotNull('penempatan_kontrak')->filter(function ($item) {
                return false === stristr($item->penempatan_kontrak, 'OS-');
            })->count()
        ];

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')
            ->make($app->view->make('sdm.papan-informasi.sdm-keluar', $data))
            ->withHeaders(['Vary' => 'Accept']);
    }

    public static function pengingatPelanggaran()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless(str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $linkupIjin = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $linkupIjin));

        SDMCache::hapusCachePelanggaranSDMTerkini();

        $cache_pelanggarans = SDMCache::ambilCachePelanggaranSDMTerkini();

        $pelanggarans = $cache_pelanggarans->when($linkupIjin, function ($c) use ($lingkup) {
            return $c->whereIn('langgar_tlokasi', [null, ...$lingkup]);
        });

        $data = [
            'pelanggarans' => $pelanggarans ?? null,
            'jumlahOS' => $pelanggarans->filter(function ($item) {
                return false !== stristr($item->langgar_tkontrak, 'OS-');
            })->count(),
            'jumlahOrganik' => $pelanggarans->filter(function ($item) {
                return false === stristr($item->langgar_tkontrak, 'OS-');
            })->count()
        ];

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')
            ->make($app->view->make('sdm.papan-informasi.pelanggaran', $data))
            ->withHeaders(['Vary' => 'Accept']);
    }

    public static function pengingatSanksi()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless(str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $linkupIjin = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $linkupIjin));

        SDMCache::hapusCacheSanksiSDMTerkini();

        $cache_sanksis = SDMCache::ambilCacheSanksiSDMTerkini();

        $sanksis = $cache_sanksis->when($linkupIjin, function ($c) use ($lingkup) {
            return $c->whereIn('langgar_tlokasi', [null, ...$lingkup]);
        });

        $data = [
            'sanksis' => $sanksis ?? null,
            'jumlahOS' =>  $sanksis->filter(function ($item) {
                return false !== stristr($item->langgar_tkontrak, 'OS-');
            })->count(),
            'jumlahOrganik' => $sanksis->filter(function ($item) {
                return false === stristr($item->langgar_tkontrak, 'OS-');
            })->count()
        ];

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')
            ->make($app->view->make('sdm.papan-informasi.sanksi', $data))
            ->withHeaders(['Vary' => 'Accept']);
    }

    public static function pengingatNilai()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless(str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $linkupIjin = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $linkupIjin));

        $tahunIni = $app->date->today()->format('Y');
        $tahunLalu = $app->date->today()->subYear()->format('Y');

        SDMCache::hapusCachePenilaianSDMTerkini();

        $cache_nilais = SDMCache::ambilCachePenilaianSDMTerkini();

        $nilais = $cache_nilais->when($linkupIjin, function ($c) use ($lingkup) {
            return $c->whereIn('langgar_tlokasi', [null, ...$lingkup]);
        });

        $data = [
            'rataTahunLalu' => $nilais->where('nilaisdm_tahun', $tahunLalu)->avg('nilaisdm_total') ?? 0,
            'rataTahunIni' => $nilais->where('nilaisdm_tahun', $tahunIni)->avg('nilaisdm_total') ?? 0
        ];

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')
            ->make($app->view->make('sdm.papan-informasi.nilai', $data))
            ->withHeaders(['Vary' => 'Accept']);
    }
}
