<?php

namespace App\Http\Controllers\SDM;

use App\Interaksi\Cache;
use App\Interaksi\Berkas;
use App\Interaksi\Rangka;
use Illuminate\Support\Arr;
use App\Tambahan\FungsiStatis;
use App\Interaksi\SDM\SDMCache;
use App\Interaksi\SDM\SDMExcel;
use App\Interaksi\SDM\SDMBerkas;
use App\Interaksi\SDM\SDMDBQuery;
use App\Interaksi\SDM\SDMValidasi;
use App\Interaksi\Umum as InteraksiUmum;

class Umum
{
    public function mulai()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        $str = str();
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        $halaman = $app->view;

        abort_unless($str->contains($pengguna?->sdm_hak_akses, 'SDM'), 403, 'Akses dibatasi hanya untuk Pengguna Aplikasi SDM.');

        $linkupIjin = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $linkupIjin));
        $cache = $app->cache;
        $date = $app->date;
        $database = $app->db;

        $kemarin = $date->today()->subDay()->format('Y-m-d');
        $hariIni = $date->today()->format('Y-m-d');
        $bulanLalu = $date->today()->subDays(40)->toDateString();
        $bulanDepan = $date->today()->addDays(40)->toDateString();

        $kontrak = $database->query()->select('penempatan_uuid', 'penempatan_no_absen', 'penempatan_posisi', 'penempatan_lokasi', 'penempatan_kontrak', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_keterangan')
            ->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
                $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
            });

        $pengurus = $str->contains($pengguna->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']);

        $fragmen = $reqs->fragment;

        $respon = match (true) {
            $fragmen == 'navigasi' => $this->halamanNavigasi($halaman, $tanggapan),
            $fragmen == 'sdmIngatUltah' => $this->halamanUltah($cache, $date, $kontrak, $database, $linkupIjin, $lingkup, $halaman, $tanggapan, $pengguna, $str),
            $pengurus && $fragmen == 'sdmIngatPtsb' => $this->halamanPermintaanTambahSDM($linkupIjin, $lingkup, $halaman, $tanggapan),
            $pengurus && $fragmen == 'sdmIngatPkpd' => $this->halamanPKWTHabis($cache, $kemarin, $hariIni, $kontrak, $database, $bulanDepan, $linkupIjin, $lingkup, $halaman, $tanggapan, $date),
            $pengurus && $fragmen == 'sdmIngatPstatus' => $this->halamanPerubahanStatusSDMTerbaru($cache, $kemarin, $hariIni, $database, $bulanLalu, $linkupIjin, $lingkup, $halaman, $tanggapan),
            $pengurus && $fragmen == 'sdmIngatBaru' => $this->halamanSDMGabungTerbaru($cache, $kemarin, $hariIni, $kontrak, $database, $bulanLalu, $linkupIjin, $lingkup, $halaman, $tanggapan),
            $pengurus && $fragmen == 'sdmIngatKeluar' => $this->halamanSDMKeluarTerbaru($cache, $kemarin, $hariIni, $kontrak, $database, $bulanLalu, $linkupIjin, $lingkup, $halaman, $tanggapan),
            $pengurus && $fragmen == 'sdmIngatPelanggaran' => $this->halamanPelanggaran($cache, $kontrak, $database, $linkupIjin, $lingkup, $halaman, $tanggapan),
            $pengurus && $fragmen == 'sdmIngatSanksi' => $this->halamanSanksi($cache, $kemarin, $hariIni, $kontrak, $database, $linkupIjin, $lingkup, $halaman, $tanggapan),
            $pengurus && $fragmen == 'sdmIngatNilai' => $this->halamanNilai($cache, $kontrak, $database, $linkupIjin, $lingkup, $halaman, $tanggapan, $date),
            default => $this->halamanAwal($reqs, $halaman, $tanggapan),
        };

        return $respon;
    }

    public function halamanNavigasi($halaman, $tanggapan)
    {
        $navigasi = $halaman->make('sdm.navigasi');

        return $tanggapan->make($navigasi)->withHeaders(['Vary' => 'Accept']);
    }

    public function halamanAwal($reqs, $halaman, $tanggapan)
    {
        $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrl()]);

        $HtmlPenuh = $halaman->make('sdm.mulai');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax() ? $tanggapan->make($HtmlIsi)->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']) : $HtmlPenuh;
    }

    public function halamanUltah($cache, $date, $kontrak, $database, $linkupIjin, $lingkup, $halaman, $tanggapan, $pengguna, $str)
    {
        $cache->forget('SDMUlangTahun - ' . $date->today()->subMonth()->format('Y-m'));

        $cache_ulangTahuns = $cache->rememberForever('SDMUlangTahun - ' . $date->today()->format('Y-m'), function () use ($kontrak, $database, $date) {
            return $database->query()->select('sdm_uuid', 'sdm_no_absen', 'sdm_nama', 'sdm_tgl_lahir', 'penempatan_lokasi', 'penempatan_kontrak', 'penempatan_posisi')->from('sdms')
                ->leftJoinSub($kontrak, 'kontrak', function ($join) {
                    $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
                })
                ->whereNull('sdm_tgl_berhenti')
                ->where(function ($query) use ($date) {
                    $query->whereMonth('sdm_tgl_lahir', $date->today()->format('m'))->orWhereMonth('sdm_tgl_lahir', $date->today()->addMonth()->format('m'));
                })->orderByRaw('DAYOFYEAR(sdm_tgl_lahir), sdm_tgl_lahir')->get();
        });

        $penemPengguna = $database->query()->select('penempatan_lokasi')->from('sdms')
            ->leftJoinSub($kontrak, 'kontrak', function ($join) {
                $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })->where('kontrak.penempatan_no_absen', $pengguna->sdm_no_absen)->first();

        $ulangTahuns = $cache_ulangTahuns->when($linkupIjin, function ($c) use ($lingkup) {
            return $c->whereIn('penempatan_lokasi', [null, ...$lingkup]);
        })->when($str->contains($pengguna->sdm_hak_akses, 'SDM-PENGGUNA'), function ($c) use ($penemPengguna) {
            return $c->whereIn('penempatan_lokasi', [$penemPengguna->penempatan_lokasi]);
        });

        $jumlahOS = $ulangTahuns->whereNotNull('penempatan_kontrak')->filter(function ($item) {
            return false !== stristr($item->penempatan_kontrak, 'OS-');
        })->count();

        $jumlahOrganik = $ulangTahuns->whereNotNull('penempatan_kontrak')->filter(function ($item) {
            return false === stristr($item->penempatan_kontrak, 'OS-');
        })->count();

        $data = [
            'ulangTahuns' => $ulangTahuns ?? null,
            'jumlahOS' => $jumlahOS,
            'jumlahOrganik' => $jumlahOrganik
        ];


        $sdmIngatUltah = $halaman->make('sdm.pengingat.sdm-ultah', $data);

        return $tanggapan->make($sdmIngatUltah)->withHeaders(['Vary' => 'Accept']);
    }

    public function halamanPermintaanTambahSDM($linkupIjin, $lingkup, $halaman, $tanggapan)
    {
        $perminSDMS = FungsiStatis::ambilCachePermintaanTambahSDM()
            ->filter(function ($item) {
                return $item->tambahsdm_jumlah > $item->tambahsdm_terpenuhi;
            })
            ->when($linkupIjin, function ($c) use ($lingkup) {
                return $c->whereIn('tambahsdm_penempatan', [...$lingkup]);
            });

        $sdmIngatPtsb = $halaman->make('sdm.pengingat.permintaan-tambah-sdm', ['perminSDMS' => $perminSDMS ?? null]);

        return $tanggapan->make($sdmIngatPtsb)->withHeaders(['Vary' => 'Accept']);
    }

    public function halamanPKWTHabis($cache, $kemarin, $hariIni, $kontrak, $database, $bulanDepan, $linkupIjin, $lingkup, $halaman, $tanggapan, $date)
    {
        $cache->forget('PengingatKontrak - ' . $kemarin);

        $cache_kontraks = $cache->rememberForever('PengingatKontrak - ' . $hariIni, function () use ($kontrak, $database, $bulanDepan) {
            return $database->query()->select('sdm_uuid', 'sdm_no_absen', 'sdm_nama', 'penempatan_uuid', 'penempatan_posisi', 'penempatan_lokasi', 'penempatan_kontrak', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke')
                ->from('sdms')
                ->leftJoinSub($kontrak, 'kontrak', function ($join) {
                    $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
                })
                ->whereNull('sdm_tgl_berhenti')
                ->where('penempatan_kontrak', 'not like', 'OS-%')
                ->where('penempatan_selesai', '<=', $bulanDepan)
                ->latest('penempatan_selesai')
                ->get();
        });

        $kontraks = $cache_kontraks->when($linkupIjin, function ($c) use ($lingkup) {
            return $c->whereIn('penempatan_lokasi', [...$lingkup]);
        });

        $jmlAkanHabis = $kontraks->filter(function ($v, $k) use ($date) {
            return $date->make($v->penempatan_selesai)->diffInDays($date->today(), false) <= 0;
        })->count();

        $jmlKadaluarsa = $kontraks->filter(function ($v, $k) use ($date) {
            return $date->make($v->penempatan_selesai)->diffInDays($date->today(), false) >= 0;
        })->count();

        $data = [
            'kontraks' => $kontraks ?? null,
            'jmlAkanHabis' => $jmlAkanHabis,
            'jmlKadaluarsa' => $jmlKadaluarsa,
        ];

        $sdmIngatPkpd = $halaman->make('sdm.pengingat.pkwt-perlu-ditinjau', $data);

        return $tanggapan->make($sdmIngatPkpd)->withHeaders(['Vary' => 'Accept']);
    }

    public function halamanPerubahanStatusSDMTerbaru($cache, $kemarin, $hariIni, $database, $bulanLalu, $linkupIjin, $lingkup, $halaman, $tanggapan)
    {
        $cache->forget('PerubahanStatus - ' . $kemarin);

        $cache_statuses = $cache->rememberForever('PerubahanStatus - ' . $hariIni, function () use ($database, $bulanLalu) {
            return $database->query()->select('penempatan_uuid', 'penempatan_no_absen', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_lokasi', 'penempatan_posisi', 'penempatan_kategori', 'penempatan_kontrak', 'penempatan_pangkat', 'penempatan_golongan', 'penempatan_grup', 'penempatan_keterangan', 'sdm_uuid', 'sdm_no_absen', 'sdm_nama')
                ->from('penempatans')->join('sdms', 'sdm_no_absen', '=', 'penempatan_no_absen')
                ->where('penempatan_mulai', '>=', $bulanLalu)
                ->latest('penempatan_mulai')
                ->get();
        });

        $statuses = $cache_statuses->when($linkupIjin, function ($c) use ($lingkup) {
            return $c->whereIn('penempatan_lokasi', [...$lingkup]);
        });

        $jumlahOS = $statuses->filter(function ($item) {
            return false !== stristr($item->penempatan_kontrak, 'OS-');
        })->count();

        $jumlahOrganik = $statuses->filter(function ($item) {
            return false === stristr($item->penempatan_kontrak, 'OS-');
        })->count();

        $data = [
            'statuses' => $statuses ?? null,
            'jumlahOS' => $jumlahOS,
            'jumlahOrganik' => $jumlahOrganik
        ];

        $sdmIngatPstatus = $halaman->make('sdm.pengingat.perubahan-status', $data);

        return $tanggapan->make($sdmIngatPstatus)->withHeaders(['Vary' => 'Accept']);
    }

    public function halamanSDMGabungTerbaru($cache, $kemarin, $hariIni, $kontrak, $database, $bulanLalu, $linkupIjin, $lingkup, $halaman, $tanggapan)
    {
        $cache->forget('PengingatAkunBaru - ' . $kemarin);

        $cache_barus = $cache->rememberForever('PengingatAkunBaru - ' . $hariIni, function () use ($kontrak, $database, $bulanLalu) {
            return $database->query()->select('sdm_uuid', 'sdm_no_absen', 'sdm_nama', 'sdm_no_permintaan',  'sdm_no_ktp', 'sdm_tgl_gabung', 'penempatan_uuid', 'penempatan_posisi', 'penempatan_lokasi', 'penempatan_kontrak')
                ->from('sdms')
                ->leftJoinSub($kontrak, 'kontrak', function ($join) {
                    $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
                })
                // ->whereNull('sdm_tgl_berhenti')
                ->where('sdm_tgl_gabung', '>=', $bulanLalu)
                ->latest('sdm_tgl_gabung')
                ->get();
        });

        $barus = $cache_barus->when($linkupIjin, function ($c) use ($lingkup) {
            return $c->whereIn('penempatan_lokasi', [null, ...$lingkup]);
        });

        $jumlahOS = $barus->whereNotNull('penempatan_kontrak')->filter(function ($item) {
            return false !== stristr($item->penempatan_kontrak, 'OS-');
        })->count();

        $jumlahOrganik = $barus->whereNotNull('penempatan_kontrak')->filter(function ($item) {
            return false === stristr($item->penempatan_kontrak, 'OS-');
        })->count();

        $belumDitempatkan = $barus->whereNull('penempatan_kontrak')->count();

        $data = [
            'barus' => $barus ?? null,
            'jumlahOS' => $jumlahOS,
            'jumlahOrganik' => $jumlahOrganik,
            'belumDitempatkan' => $belumDitempatkan
        ];

        $sdmIngatBaru = $halaman->make('sdm.pengingat.sdm-baru', $data);

        return $tanggapan->make($sdmIngatBaru)->withHeaders(['Vary' => 'Accept']);
    }

    public function halamanSDMKeluarTerbaru($cache, $kemarin, $hariIni, $kontrak, $database, $bulanLalu, $linkupIjin, $lingkup, $halaman, $tanggapan)
    {
        $cache->forget('PengingatNon-Aktif - ' . $kemarin);

        $cache_berhentis = $cache->rememberForever('PengingatNon-Aktif - ' . $hariIni, function () use ($kontrak, $database, $bulanLalu) {
            return $database->query()->select('sdm_uuid', 'sdm_no_absen', 'sdm_nama', 'sdm_jenis_berhenti', 'sdm_tgl_berhenti', 'sdm_ket_berhenti', 'penempatan_uuid', 'penempatan_posisi', 'penempatan_lokasi', 'penempatan_kontrak')
                ->from('sdms')
                ->leftJoinSub($kontrak, 'kontrak', function ($join) {
                    $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
                })
                ->where(function ($query) {
                    $query->whereNotNull('sdm_tgl_berhenti')
                        ->orWhereNull('penempatan_kontrak');
                })
                ->where('sdm_tgl_berhenti', '>=', $bulanLalu)
                ->latest('sdm_tgl_berhenti')
                ->get();
        });

        $berhentis = $cache_berhentis->when($linkupIjin, function ($c) use ($lingkup) {
            return $c->whereIn('penempatan_lokasi', [null, ...$lingkup]);
        });

        $jumlahOS = $berhentis->whereNotNull('penempatan_kontrak')->filter(function ($item) {
            return false !== stristr($item->penempatan_kontrak, 'OS-');
        })->count();

        $jumlahOrganik = $berhentis->whereNotNull('penempatan_kontrak')->filter(function ($item) {
            return false === stristr($item->penempatan_kontrak, 'OS-');
        })->count();

        $data = [
            'berhentis' => $berhentis ?? null,
            'jumlahOS' => $jumlahOS,
            'jumlahOrganik' => $jumlahOrganik
        ];

        $sdmIngatKeluar = $halaman->make('sdm.pengingat.sdm-keluar', $data);

        return $tanggapan->make($sdmIngatKeluar)->withHeaders(['Vary' => 'Accept']);
    }

    public function halamanPelanggaran($cache, $kontrak, $database, $linkupIjin, $lingkup, $halaman, $tanggapan)
    {
        $cache->forget('PengingatPelanggaran');

        $dataDasar = $database->query()->select('langgar_uuid', 'langgar_lap_no', 'langgar_no_absen', 'langgar_pelapor', 'langgar_tanggal', 'langgar_status', 'langgar_isi', 'langgar_keterangan')->from('pelanggaransdms');

        $sanksi = $database->query()->select('sanksi_no_absen', 'sanksi_jenis', 'sanksi_lap_no', 'sanksi_selesai', 'sanksi_mulai')
            ->from('sanksisdms as p1')->where('sanksi_mulai', '=', function ($query) use ($database) {
                $query->select($database->raw('MAX(sanksi_mulai)'))->from('sanksisdms as p2')->whereColumn('p1.sanksi_no_absen', 'p2.sanksi_no_absen');
            });

        $cache_pelanggarans = $cache->rememberForever('PengingatPelanggaran', function () use ($kontrak, $dataDasar, $sanksi) {
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
                ->whereNull('sanksisdms.sanksi_jenis')->where('langgar_status', '=', 'DIPROSES')->orderBy('langgar_lap_no', 'desc')->get();
        });

        $pelanggarans = $cache_pelanggarans->when($linkupIjin, function ($c) use ($lingkup) {
            return $c->whereIn('langgar_tlokasi', [null, ...$lingkup]);
        });

        $jumlahOS = $pelanggarans->filter(function ($item) {
            return false !== stristr($item->langgar_tkontrak, 'OS-');
        })->count();

        $jumlahOrganik = $pelanggarans->filter(function ($item) {
            return false === stristr($item->langgar_tkontrak, 'OS-');
        })->count();

        $data = [
            'pelanggarans' => $pelanggarans ?? null,
            'jumlahOS' => $jumlahOS,
            'jumlahOrganik' => $jumlahOrganik
        ];

        $sdmIngatPelanggaran = $halaman->make('sdm.pengingat.pelanggaran', $data);

        return $tanggapan->make($sdmIngatPelanggaran)->withHeaders(['Vary' => 'Accept']);
    }

    public function halamanSanksi($cache, $kemarin, $hariIni, $kontrak, $database, $linkupIjin, $lingkup, $halaman, $tanggapan)
    {
        $cache->forget('PengingatSanksi - ' . $kemarin);

        $dataDasar = $database->query()->select('sanksi_uuid', 'sanksi_no_absen', 'sanksi_jenis', 'sanksi_mulai', 'sanksi_selesai', 'sanksi_lap_no', 'sanksi_tambahan', 'sanksi_keterangan')->from('sanksisdms');

        $cache_sanksis = $cache->rememberForever('PengingatSanksi - ' . $hariIni, function () use ($kontrak, $dataDasar, $hariIni) {
            return $dataDasar->addSelect('a.sdm_uuid as langgar_tsdm_uuid', 'a.sdm_nama as langgar_tsdm_nama', 'a.sdm_tgl_berhenti as langgar_tsdm_tgl_berhenti', 'kontrak_t.penempatan_lokasi as langgar_tlokasi', 'kontrak_t.penempatan_posisi as langgar_tposisi', 'kontrak_t.penempatan_kontrak as langgar_tkontrak', 'langgar_isi', 'langgar_tanggal', 'langgar_status', 'langgar_pelapor', 'b.sdm_uuid as langgar_psdm_uuid', 'b.sdm_nama as langgar_psdm_nama', 'b.sdm_tgl_berhenti as langgar_psdm_tgl_berhenti', 'kontrak_p.penempatan_lokasi as langgar_plokasi', 'kontrak_p.penempatan_posisi as langgar_pposisi', 'kontrak_p.penempatan_kontrak as langgar_pkontrak')
                ->join('sdms as a', 'sanksi_no_absen', '=', 'a.sdm_no_absen')
                ->leftJoinSub($kontrak, 'kontrak_t', function ($join) {
                    $join->on('sanksi_no_absen', '=', 'kontrak_t.penempatan_no_absen');
                })
                ->leftJoin('pelanggaransdms', 'sanksi_lap_no', '=', 'langgar_lap_no')
                ->leftJoin('sdms as b', 'langgar_pelapor', '=', 'b.sdm_no_absen')
                ->leftJoinSub($kontrak, 'kontrak_p', function ($join) {
                    $join->on('langgar_pelapor', '=', 'kontrak_p.penempatan_no_absen');
                })
                ->whereNull('a.sdm_tgl_berhenti')
                ->where('sanksi_selesai', '>=', $hariIni)
                ->latest('sanksi_selesai')
                ->get();
        });

        $sanksis = $cache_sanksis->when($linkupIjin, function ($c) use ($lingkup) {
            return $c->whereIn('langgar_tlokasi', [null, ...$lingkup]);
        });

        $jumlahOS = $sanksis->filter(function ($item) {
            return false !== stristr($item->langgar_tkontrak, 'OS-');
        })->count();

        $jumlahOrganik = $sanksis->filter(function ($item) {
            return false === stristr($item->langgar_tkontrak, 'OS-');
        })->count();

        $data = [
            'sanksis' => $sanksis ?? null,
            'jumlahOS' => $jumlahOS,
            'jumlahOrganik' => $jumlahOrganik
        ];

        $sdmIngatSanksi = $halaman->make('sdm.pengingat.sanksi', $data);

        return $tanggapan->make($sdmIngatSanksi)->withHeaders(['Vary' => 'Accept']);
    }

    public function halamanNilai($cache, $kontrak, $database, $linkupIjin, $lingkup, $halaman, $tanggapan, $date)
    {
        $tahunIni = $date->today()->format('Y');
        $tahunLalu = $date->today()->subYear()->format('Y');
        $rentang = [
            $tahunIni, $tahunLalu
        ];

        $cache->forget('PengingatNilai - ' . $tahunLalu);

        $dataDasar = $database->query()->select('nilaisdm_uuid', 'nilaisdm_no_absen', 'nilaisdm_tahun', 'nilaisdm_periode', 'nilaisdm_bobot_hadir', 'nilaisdm_bobot_sikap', 'nilaisdm_bobot_target', 'nilaisdm_tindak_lanjut', 'nilaisdm_keterangan')->from('penilaiansdms');

        $cache_nilais = $cache->rememberForever('PengingatNilai - ' . $tahunIni, function () use ($kontrak, $dataDasar, $rentang, $database) {
            return $dataDasar->addSelect('penempatan_lokasi', 'penempatan_kontrak', $database->raw('(IFNULL(nilaisdm_bobot_hadir, 0) + IFNULL(nilaisdm_bobot_sikap, 0) + IFNULL(nilaisdm_bobot_target, 0)) as nilaisdm_total'))
                ->leftJoinSub($kontrak, 'kontrak_t', function ($join) {
                    $join->on('nilaisdm_no_absen', '=', 'kontrak_t.penempatan_no_absen');
                })
                ->whereIn('nilaisdm_tahun', $rentang)
                ->get();
        });

        $nilais = $cache_nilais->when($linkupIjin, function ($c) use ($lingkup) {
            return $c->whereIn('langgar_tlokasi', [null, ...$lingkup]);
        });

        $rataTahunLalu = $nilais->where('nilaisdm_tahun', $tahunLalu)->avg('nilaisdm_total');

        $rataTahunIni = $nilais->where('nilaisdm_tahun', $tahunIni)->avg('nilaisdm_total');

        $data = [
            'rataTahunLalu' => $rataTahunLalu ?? 0,
            'rataTahunIni' => $rataTahunIni ?? 0
        ];

        $sdmIngatSanksi = $halaman->make('sdm.pengingat.nilai', $data);

        return $tanggapan->make($sdmIngatSanksi)->withHeaders(['Vary' => 'Accept']);
    }

    public function akun($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna, 401);

        $akun = SDMDBQuery::ambilDataAkunLengkap($uuid)->first();

        abort_unless($akun, 404, 'Profil yang dicari tidak ada.');

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $no_absen_sdm = $pengguna->sdm_no_absen;
        $no_absen_atasan = $pengguna->sdm_id_atasan;
        $lingkup = array_filter(explode(',', $ijin_akses));
        $lingkup_lokasi = collect($akun->penempatan_lokasi);
        $lingkup_akses = $lingkup_lokasi->unique()->intersect($lingkup)->count();
        $str = str();

        abort_unless(($str->contains($pengguna->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']) && (blank($ijin_akses) || $lingkup_lokasi->isEmpty() || ($lingkup_akses > 0))) || ($no_absen_sdm == $akun->sdm_no_absen) || ($akun->sdm_no_absen == $no_absen_atasan) || ($akun->sdm_id_atasan == $no_absen_sdm) || (!blank($no_absen_atasan) && ($akun->sdm_id_atasan == $no_absen_atasan)), 403, 'Ijin akses dibatasi.');

        $no_wa_tst = $str->of($akun->sdm_telepon)->replace('-', '')->replace(' ', '');

        $no_wa = match (true) {
            $str->startsWith($no_wa_tst, '0') => $str->replaceFirst('0', '62', $no_wa_tst),
            $str->startsWith($no_wa_tst, '8') => $str->start($no_wa_tst, '62'),
            default => $no_wa_tst
        };

        $cacheSDM = SDMCache::ambilCacheSDM();

        $data = [
            'akun' => $akun,
            'personils' => $cacheSDM->where('sdm_id_atasan', $akun->sdm_no_absen),
            'no_wa' =>  $no_wa ?: '0',
            'batasi' => $str->contains($pengguna->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']) || $no_absen_sdm == $akun->sdm_no_absen,
        ];

        $HtmlPenuh = $app->view->make('akun', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function ubahAkun($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid, 401);

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $ijin_akses));

        $akun = SDMDBQuery::ambilDataAkun($uuid)->first();

        $no_absen_sdm = $pengguna->sdm_no_absen;
        $lingkup_lokasi = $akun->penempatan_lokasi;
        $lingkup_akses = collect($lingkup_lokasi)->intersect($lingkup)->count();

        abort_unless(blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0) || ($no_absen_sdm == $akun->sdm_no_absen), 403, 'Akses pengguna dibatasi.');

        if ($reqs->isMethod('post')) {
            abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

            $reqs->whenFilled(
                'sdm_hak_akses',
                function ($input) use ($reqs) {
                    $reqs->except('sdm_hak_akses');
                    $reqs->merge(['sdm_hak_akses' => implode(',', $input)]);
                },
                function () use ($reqs) {
                    $reqs->merge(['sdm_hak_akses' => null]);
                }
            );

            $reqs->whenFilled(
                'sdm_ijin_akses',
                function ($input) use ($reqs) {
                    $reqs->except('sdm_ijin_akses');
                    $reqs->merge(['sdm_ijin_akses' => implode(',', $input)]);
                },
                function () use ($reqs) {
                    $reqs->merge(['sdm_ijin_akses' => null]);
                }
            );

            $reqs->merge(['sdm_id_pengubah' => $pengguna->sdm_no_absen]);

            $validasi = SDMValidasi::validasiUbahDataSDM($uuid, [$reqs->all()]);

            $validasi->validate();

            $valid = $validasi->safe()->all()[0];

            $pengurus = str()->contains($pengguna->sdm_hak_akses, 'SDM-PENGURUS');

            $data = match (true) {
                $pengurus && blank($ijin_akses) => Arr::except($valid, ['foto_profil', 'sdm_berkas']),
                $pengurus && !blank($ijin_akses) => Arr::except($valid, [
                    'foto_profil', 'sdm_berkas', 'sdm_hak_akses', 'sdm_ijin_akses'
                ]),
                default => Arr::except($valid, [
                    'foto_profil', 'sdm_berkas', 'sdm_hak_akses', 'sdm_ijin_akses', 'sdm_tgl_berhenti', 'sdm_jenis_berhenti', 'sdm_ket_berhenti', 'sdm_ket_kary', 'sdm_nama_dok', 'sdm_nomor_dok', 'sdm_penerbit_dok', 'sdm_an_dok', 'sdm_kadaluarsa_dok', 'sdm_no_permintaan', 'sdm_no_absen', 'sdm_id_atasan', 'sdm_tgl_gabung', 'sdm_warganegara', 'sdm_disabilitas', 'sdm_nama_bank', 'sdm_cabang_bank', 'sdm_rek_bank', 'sdm_an_bank'
                ])
            };

            SDMDBQuery::ubahDataSDM($uuid, $data);

            $foto = Arr::only($valid, ['foto_profil'])['foto_profil'] ?? false;
            $berkas = Arr::only($valid, ['sdm_berkas'])['sdm_berkas'] ?? false;
            $no_absen = Arr::only($valid, ['sdm_no_absen'])['sdm_no_absen'];
            $session = $reqs->session();

            if ($foto) {
                SDMBerkas::simpanFotoSDM($foto, $no_absen);
            }

            if ($berkas && $pengurus && (blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0) || ($no_absen_sdm == $akun->sdm_no_absen))) {
                SDMBerkas::simpanBerkasSDM($berkas, $no_absen);
            }

            if ($foto && $no_absen == $no_absen_sdm) {
                $sesiJS = "lemparXHR({
                    tujuan : '#tbl-menu',
                    tautan : '{$app->url->route('komponen', ['komponen' => 'menu', 'fragment' => 'avatar'])}',
                    normalview : true
                    });";
                $session->flash('sesiJS', $sesiJS);
            }

            SDMCache::hapusCacheSDMUmum();

            $pesan = Rangka::statusBerhasil();

            $perujuk = $session->get('tautan_perujuk');

            $redirect = $app->redirect;

            return $perujuk ? $redirect->to($perujuk)->with('pesan', $pesan) : $redirect->route('sdm.mulai')->with('pesan', $pesan);
        }

        $aturs = Cache::ambilCacheAtur();
        $permintaanSdms = SDMCache::ambilCachePermintaanTambahSDM();
        $atasan = SDMCache::ambilCacheSDM();

        $data = [
            'sdm' => $akun,
            'permintaanSdms' => $permintaanSdms,
            'atasans' => $atasan,
            'negaras' => $aturs->where('atur_jenis', 'NEGARA')->sortBy(['atur_butir', 'asc']),
            'kelamins' => $aturs->where('atur_jenis', 'KELAMIN')->sortBy(['atur_butir', 'asc']),
            'gdarahs' => $aturs->where('atur_jenis', 'GOLONGAN DARAH')->sortBy(['atur_butir', 'asc']),
            'agamas' => $aturs->where('atur_jenis', 'AGAMA')->sortBy(['atur_butir', 'asc']),
            'kawins' => $aturs->where('atur_jenis', 'STATUS MENIKAH')->sortBy(['atur_butir', 'asc']),
            'pendidikans' => $aturs->where('atur_jenis', 'PENDIDIKAN')->sortBy(['atur_butir', 'asc']),
            'disabilitas' => $aturs->where('atur_jenis', 'DISABILITAS')->sortBy(['atur_butir', 'asc']),
            'banks' => $aturs->where('atur_jenis', 'BANK')->sortBy(['atur_butir', 'asc']),
            'seragams' => $aturs->where('atur_jenis', 'UKURAN SERAGAM')->sortBy(['atur_butir', 'asc']),
            'phks' => $aturs->where('atur_jenis', 'JENIS BERHENTI')->sortBy(['atur_butir', 'asc']),
            'perans' => $aturs->where('atur_jenis', 'PERAN')->sortBy(['atur_butir', 'asc']),
            'penempatans' => $aturs->where('atur_jenis', 'PENEMPATAN')->sortBy(['atur_butir', 'asc']),
        ];

        $HtmlPenuh = $app->view->make('tambah-ubah-akun', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function ubahSandi()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna, 401);

        $idPenguna = $pengguna->id;
        $akun = SDMDBQuery::ambilIDPengguna($idPenguna)->first();

        abort_unless($idPenguna == $akun->id, 403, 'Identitas pengguna berbeda.');

        if ($reqs->isMethod('post')) {
            abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

            $validasiSandi = SDMValidasi::validasiUbahSandi($reqs->all());

            $validasiSandi->validate();

            $sandiBaru = $app->hash->make($validasiSandi->safe()->only('password')['password']);

            SDMDBQuery::ubahSandiPengguna($idPenguna, $sandiBaru);

            $reqs->session()->forget('spanduk');

            return $app->redirect->route('mulai')->with('pesan', 'Sandi berhasil diubah.');
        }

        $HtmlPenuh = $app->view->make('ubah-sandi');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $HtmlPenuh;
    }

    public function contohUnggah()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna->sdm_hak_akses, ['PENGURUS', 'MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku Aplikasi.');

        abort_unless($app->filesystem->exists("contoh/unggah-umum.xlsx"), 404, 'Berkas Contoh Ekspor Tidak Ditemukan.');

        $lingkup = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        return SDMExcel::eksporExcelContohUnggahSDM(SDMDBQuery::contohImporDatabaseSDM($lingkup)->orderBy('id', 'desc'));
    }

    public function unggah()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        if ($reqs->isMethod('post')) {
            $validasifile = SDMValidasi::validasiBerkasImporDataSDM($reqs->all());

            $validasifile->validate();

            $file = $validasifile->safe()->only('unggah_profil_sdm')['unggah_profil_sdm'];

            $namafile = 'unggahprofilsdm-' . date('YmdHis') . '.xlsx';

            Berkas::simpanBerkasImporExcelSementara($file, $namafile);

            $fileexcel = Berkas::ambilBerkasImporExcelSementara($namafile);

            return SDMExcel::imporExcelDataSDM($fileexcel);
        }

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make(implode('', $app->view->make('unggah')->renderSections()))->withHeaders(['Vary' => 'Accept']);
    }
}
