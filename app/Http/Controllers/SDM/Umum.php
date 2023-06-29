<?php

namespace App\Http\Controllers\SDM;

use App\Tambahan\FungsiStatis;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Validation\Rules\Password;
use App\Tambahan\ChunkReadFilter;

class Umum
{
    public function mulai()
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
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
            $fragmen == 'sdmIngatUltah' => $this->halamanUltah($cache, $date, $kontrak, $database, $linkupIjin, $lingkup, $halaman, $tanggapan),
            $pengurus && $fragmen == 'sdmIngatPtsb' => $this->halamanPermintaanTambahSDM($linkupIjin, $lingkup, $halaman, $tanggapan),
            $pengurus && $fragmen == 'sdmIngatPkpd' => $this->halamanPKWTHabis($cache, $kemarin, $hariIni, $kontrak, $database, $bulanDepan, $linkupIjin, $lingkup, $halaman, $tanggapan),
            $pengurus && $fragmen == 'sdmIngatPstatus' => $this->halamanPerubahanStatusSDMTerbaru($cache, $kemarin, $hariIni, $database, $bulanLalu, $linkupIjin, $lingkup, $halaman, $tanggapan),
            $pengurus && $fragmen == 'sdmIngatBaru' => $this->halamanSDMGabungTerbaru($cache, $kemarin, $hariIni, $kontrak, $database, $bulanLalu, $linkupIjin, $lingkup, $halaman, $tanggapan),
            $pengurus && $fragmen == 'sdmIngatKeluar' => $this->halamanSDMKeluarTerbaru($cache, $kemarin, $hariIni, $kontrak, $database, $bulanLalu, $linkupIjin, $lingkup, $halaman, $tanggapan),
            $pengurus && $fragmen == 'sdmIngatPelanggaran' => $this->halamanPelanggaran($cache, $kontrak, $database, $linkupIjin, $lingkup, $halaman, $tanggapan),
            $pengurus && $fragmen == 'sdmIngatSanksi' => $this->halamanSanksi($cache, $kemarin, $hariIni, $kontrak, $database, $linkupIjin, $lingkup, $halaman, $tanggapan),
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

    public function halamanUltah($cache, $date, $kontrak, $database, $linkupIjin, $lingkup, $halaman, $tanggapan)
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

        $ulangTahuns = $cache_ulangTahuns->when($linkupIjin, function ($c) use ($lingkup) {
            return $c->whereIn('penempatan_lokasi', [null, ...$lingkup]);
        });

        $sdmIngatUltah = $halaman->make('sdm.pengingat.sdm-ultah', ['ulangTahuns' => $ulangTahuns ?? null]);

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

    public function halamanPKWTHabis($cache, $kemarin, $hariIni, $kontrak, $database, $bulanDepan, $linkupIjin, $lingkup, $halaman, $tanggapan)
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

        $sdmIngatPkpd = $halaman->make('sdm.pengingat.pkwt-perlu-ditinjau', ['kontraks' => $kontraks ?? null]);

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

        $sdmIngatPstatus = $halaman->make('sdm.pengingat.perubahan-status', ['statuses' => $statuses ?? null]);

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

        $sdmIngatBaru = $halaman->make('sdm.pengingat.sdm-baru', ['barus' => $barus ?? null]);

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

        $sdmIngatKeluar = $halaman->make('sdm.pengingat.sdm-keluar', ['berhentis' => $berhentis ?? null]);

        return $tanggapan->make($sdmIngatKeluar)->withHeaders(['Vary' => 'Accept']);
    }

    public function halamanPelanggaran($cache, $kontrak, $database, $linkupIjin, $lingkup, $halaman, $tanggapan)
    {
        $cache->forget('PengingatPelanggaran');

        $dataDasar = $database->query()->select('langgar_uuid', 'langgar_lap_no', 'langgar_no_absen', 'langgar_pelapor', 'langgar_tanggal', 'langgar_status', 'langgar_isi', 'langgar_keterangan')->from('pelanggaransdms');

        $sanksi = $database->query()->select('sanksi_no_absen', 'sanksi_jenis', 'sanksi_lap_no', 'sanksi_selesai', 'sanksi_mulai')
            ->from('sanksisdms as p1')->where('sanksi_selesai', '=', function ($query) use ($database) {
                $query->select($database->raw('MAX(sanksi_selesai)'))->from('sanksisdms as p2')->whereColumn('p1.sanksi_no_absen', 'p2.sanksi_no_absen');
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

        $sdmIngatPelanggaran = $halaman->make('sdm.pengingat.pelanggaran', ['pelanggarans' => $pelanggarans ?? null]);

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

        $sdmIngatSanksi = $halaman->make('sdm.pengingat.sanksi', ['sanksis' => $sanksis ?? null]);

        return $tanggapan->make($sdmIngatSanksi)->withHeaders(['Vary' => 'Accept']);
    }

    public function akun(FungsiStatis $fungsiStatis, $uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();

        abort_unless($pengguna, 401);

        $database = $app->db;

        $kontrak = $database->query()->select('penempatan_no_absen', 'penempatan_lokasi', 'penempatan_posisi')->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
            $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
        });

        $dasar = $database->query()->select('a.sdm_uuid', 'a.sdm_no_permintaan', 'a.sdm_no_absen', 'a.sdm_tgl_gabung', 'a.sdm_warganegara', 'a.sdm_no_ktp', 'a.sdm_nama', 'a.sdm_tempat_lahir', 'a.sdm_tgl_lahir', 'a.sdm_kelamin', 'a.sdm_gol_darah', 'a.sdm_alamat', 'a.sdm_alamat_rt', 'a.sdm_alamat_rw', 'a.sdm_alamat_kelurahan', 'a.sdm_alamat_kecamatan', 'a.sdm_alamat_kota', 'a.sdm_alamat_provinsi', 'a.sdm_alamat_kodepos', 'a.sdm_agama', 'a.sdm_no_kk', 'a.sdm_status_kawin', 'a.sdm_jml_anak', 'a.sdm_pendidikan', 'a.sdm_jurusan', 'a.sdm_telepon', 'a.email', 'a.sdm_disabilitas', 'a.sdm_no_bpjs', 'a.sdm_no_jamsostek', 'a.sdm_no_npwp', 'a.sdm_nama_bank', 'a.sdm_cabang_bank', 'a.sdm_rek_bank', 'a.sdm_an_rek', 'a.sdm_nama_dok', 'a.sdm_nomor_dok', 'a.sdm_penerbit_dok', 'a.sdm_an_dok', 'a.sdm_kadaluarsa_dok', 'a.sdm_uk_seragam', 'a.sdm_uk_sepatu', 'a.sdm_ket_kary', 'a.sdm_tgl_berhenti', 'a.sdm_jenis_berhenti', 'a.sdm_ket_berhenti', 'a.sdm_id_atasan', 'a.sdm_hak_akses', 'a.sdm_ijin_akses')->from('sdms', 'a');

        $akun = $dasar->clone()->addSelect('b.sdm_uuid as uuid_atasan', 'b.sdm_nama as nama_atasan', 'b.sdm_tgl_berhenti as tgl_berhenti_atasan', 'penempatan_lokasi', 'penempatan_posisi')->leftJoin('sdms as b', 'a.sdm_id_atasan', '=', 'b.sdm_no_absen')
            ->leftJoinSub($kontrak, 'kontrak', function ($join) {
                $join->on('b.sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->where('a.sdm_uuid', $uuid)->first();

        abort_unless($akun, 404, 'Profil yang dicari tidak ada.');

        $penempatans = $database->query()->select('sdm_uuid', 'sdm_no_absen', 'sdm_tgl_gabung', 'sdm_tgl_lahir', 'sdm_no_ktp', 'sdm_nama', 'sdm_kelamin', 'sdm_tgl_berhenti', 'penempatan_uuid', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_lokasi', 'penempatan_posisi', 'penempatan_kategori', 'penempatan_kontrak', 'penempatan_pangkat', 'penempatan_golongan', 'penempatan_grup', 'penempatan_keterangan', 'posisi_wlkp', $app->db->raw('IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, sdm_tgl_berhenti)) as masa_kerja, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, penempatan_mulai, NOW()),TIMESTAMPDIFF(YEAR, penempatan_mulai, sdm_tgl_berhenti)) as masa_aktif, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, sdm_tgl_berhenti)) as usia'))
            ->from('penempatans')->joinSub($dasar, 'dasar', function ($join) {
                $join->on('penempatan_no_absen', '=', 'dasar.sdm_no_absen');
            })
            ->leftJoin('posisis', 'penempatan_posisi', '=', 'posisi_nama')
            ->where('sdm_uuid', $uuid)->latest('penempatan_mulai')->get();

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $no_absen_sdm = $pengguna->sdm_no_absen;
        $no_absen_atasan = $pengguna->sdm_id_atasan;
        $lingkup = array_filter(explode(',', $ijin_akses));
        $lingkup_lokasi = $penempatans->pluck('penempatan_lokasi');
        $lingkup_akses = $lingkup_lokasi->unique()->intersect($lingkup)->count();
        $str = str();

        abort_unless(($str->contains($pengguna->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']) && (blank($ijin_akses) || $lingkup_lokasi->isEmpty() || ($lingkup_akses > 0))) || ($no_absen_sdm == $akun->sdm_no_absen) || ($akun->sdm_no_absen == $no_absen_atasan) || ($akun->sdm_id_atasan == $no_absen_sdm) || (!blank($no_absen_atasan) && ($akun->sdm_id_atasan == $no_absen_atasan)), 403, 'Ijin akses dibatasi.');

        $no_wa_ts = $str->replace('-', '', $akun->sdm_telepon);
        $no_wa_tst = $str->replace(' ', '', $no_wa_ts);
        $no_wa_tn = $str->startsWith($no_wa_tst, '0');
        $no_wa_td = $str->startsWith($no_wa_tst, '8');
        if ($no_wa_tn) {
            $no_wa = $str->replaceFirst('0', '62', $no_wa_tst);
        } elseif ($no_wa_td) {
            $no_wa = $str->start($no_wa_tst, '62');
        } else {
            $no_wa = $no_wa_tst;
        }

        $cacheSDM = $fungsiStatis->ambilCacheSDM();

        $data = [
            'akun' => $akun,
            'penempatans' => $penempatans,
            'personils' => $cacheSDM->where('sdm_id_atasan', $akun->sdm_no_absen),
            'no_wa' =>  $no_wa ?: '0',
            'batasi' => $str->contains($pengguna->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']) || $no_absen_sdm == $akun->sdm_no_absen,
        ];

        $HtmlPenuh = $app->view->make('akun', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }

    public function ubahAkun(FungsiStatis $fungsiStatis, $uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        abort_unless($pengguna && $uuid, 401);
        $database = $app->db;

        $penem = $database->query()->select('penempatan_no_absen', 'penempatan_lokasi', 'penempatan_mulai')->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
            $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
        });

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $ijin_akses));

        $akun = $database->query()->select('sdms.*', 'penempatan_lokasi')
            ->from('sdms')
            ->leftJoinSub($penem, 'kontrak', function ($join) {
                $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->where('sdm_uuid', $uuid)
            ->first();

        $no_absen_sdm = $pengguna->sdm_no_absen;
        $lingkup_lokasi = $akun->penempatan_lokasi;
        $lingkup_akses = collect($lingkup_lokasi)->intersect($lingkup)->count();

        abort_unless(blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0) || ($no_absen_sdm == $akun->sdm_no_absen), 403, 'Akses pengguna dibatasi.');

        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        $halaman = $app->view;

        if ($reqs->isMethod('post')) {
            abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

            $reqs->whenFilled('sdm_hak_akses', function ($input) use ($reqs) {
                $reqs->except('sdm_hak_akses');
                $reqs->merge(['sdm_hak_akses' => implode(',', $input)]);
            }, function () use ($reqs) {
                $reqs->merge(['sdm_hak_akses' => null]);
            });

            $reqs->whenFilled('sdm_ijin_akses', function ($input) use ($reqs) {
                $reqs->except('sdm_ijin_akses');
                $reqs->merge(['sdm_ijin_akses' => implode(',', $input)]);
            }, function () use ($reqs) {
                $reqs->merge(['sdm_ijin_akses' => null]);
            });

            $reqs->merge(['sdm_id_pengubah' => $pengguna->sdm_no_absen]);

            $validasi = $app->validator->make(
                $reqs->all(),
                [
                    'foto_profil' => ['sometimes', 'image', 'dimensions:min_width=299,min_height=399'],
                    'sdm_berkas' => ['sometimes', 'file', 'mimetypes:application/pdf'],
                    'sdm_no_permintaan' => ['sometimes', 'nullable', 'string', 'max:20', 'exists:tambahsdms,tambahsdm_no'],
                    'sdm_no_absen' => ['required', 'string', 'max:10', Rule::unique('sdms')->where(fn ($query) => $query->whereNot('sdm_uuid', $uuid))],
                    'sdm_id_atasan' => ['sometimes', 'nullable', 'string', 'max:10', 'different:sdm_no_absen'],
                    'sdm_tgl_gabung' => ['required', 'date'],
                    'sdm_warganegara' => ['required', 'string', 'max:40'],
                    'sdm_no_ktp' => ['required', 'string', 'max:20'],
                    'sdm_nama' => ['required', 'string', 'max:80'],
                    'sdm_tempat_lahir' => ['required', 'string', 'max:40'],
                    'sdm_tgl_lahir' => ['required', 'date'],
                    'sdm_kelamin' => ['required', 'string', 'max:2'],
                    'sdm_gol_darah' => ['sometimes', 'nullable', 'string', 'max:2'],
                    'sdm_alamat' => ['required', 'string', 'max:120'],
                    'sdm_alamat_rt' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                    'sdm_alamat_rw' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                    'sdm_alamat_kelurahan' => ['required', 'string', 'max:40'],
                    'sdm_alamat_kecamatan' => ['required', 'string', 'max:40'],
                    'sdm_alamat_kota' => ['required', 'string', 'max:40'],
                    'sdm_alamat_provinsi' => ['required', 'string', 'max:40'],
                    'sdm_alamat_kodepos' => ['sometimes', 'nullable', 'string', 'max:10'],
                    'sdm_agama' => ['required', 'string', 'max:20'],
                    'sdm_no_kk' => ['sometimes', 'nullable', 'string', 'max:20'],
                    'sdm_status_kawin' => ['required', 'string', 'max:10'],
                    'sdm_jml_anak' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                    'sdm_pendidikan' => ['required', 'string', 'max:10'],
                    'sdm_jurusan' => ['sometimes', 'nullable', 'string', 'max:60'],
                    'sdm_telepon' => ['required', 'string', 'max:40'],
                    'email' => ['required', 'email'],
                    'sdm_disabilitas' => ['required', 'string', 'max:30'],
                    'sdm_no_bpjs' => ['sometimes', 'nullable', 'string', 'max:30'],
                    'sdm_no_jamsostek' => ['sometimes', 'nullable', 'string', 'max:30'],
                    'sdm_no_npwp' => ['sometimes', 'nullable', 'string', 'max:30'],
                    'sdm_nama_bank' => ['sometimes', 'nullable', 'string', 'max:20'],
                    'sdm_cabang_bank' => ['sometimes', 'nullable', 'string', 'max:50'],
                    'sdm_rek_bank' => ['sometimes', 'nullable', 'string', 'max:40'],
                    'sdm_an_rek' => ['sometimes', 'nullable', 'string', 'max:80'],
                    'sdm_nama_dok' => ['sometimes', 'nullable', 'string', 'max:50'],
                    'sdm_nomor_dok' => ['sometimes', 'nullable', 'string', 'max:40'],
                    'sdm_penerbit_dok' => ['sometimes', 'nullable', 'string', 'max:60'],
                    'sdm_an_dok' => ['sometimes', 'nullable', 'string', 'max:80'],
                    'sdm_kadaluarsa_dok' => ['sometimes', 'nullable', 'date'],
                    'sdm_uk_seragam' => ['sometimes', 'nullable', 'string', 'max:10'],
                    'sdm_uk_sepatu' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                    'sdm_ket_kary' => ['sometimes', 'nullable', 'string'],
                    'sdm_id_pengubah' => ['sometimes', 'nullable', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                    'sdm_hak_akses' => ['sometimes', 'nullable', 'string'],
                    'sdm_ijin_akses' => ['sometimes', 'nullable', 'string'],
                    'sdm_tgl_berhenti' => ['sometimes', 'nullable', 'date', 'required_unless:sdm_jenis_berhenti,null'],
                    'sdm_jenis_berhenti' => ['sometimes', 'nullable', 'string', 'required_unless:sdm_tgl_berhenti,null'],
                    'sdm_ket_berhenti' => ['sometimes', 'nullable', 'string'],
                ],
                [],
                [
                    'foto_profil' => 'Foto Profil',
                    'sdm_berkas' => 'Berkas Yang Diunggah',
                    'sdm_no_permintaan' => 'Nomor Permintaan Tambah SDM',
                    'sdm_no_absen' => 'Nomor Absen SDM',
                    'sdm_id_atasan' => 'Nomor Absen Atasan',
                    'sdm_tgl_gabung' => 'Tanggal Bergabung SDM',
                    'sdm_warganegara' => 'Warganegara',
                    'sdm_no_ktp' => 'Nomor E-KTP/Passport',
                    'sdm_nama' => 'Nama SDM',
                    'sdm_tempat_lahir' => 'Tempat Lahir',
                    'sdm_tgl_lahir' => 'Tanggal Lahir',
                    'sdm_kelamin' => 'Kelamin',
                    'sdm_gol_darah' => 'Golongan Darah',
                    'sdm_alamat' => 'Alamat',
                    'sdm_alamat_rt' => 'Alamat RT',
                    'sdm_alamat_rw' => 'Alamat RW',
                    'sdm_alamat_kelurahan' => 'Alamat Kelurahan',
                    'sdm_alamat_kecamatan' => 'Alamat Kecamatan',
                    'sdm_alamat_kota' => 'Alamat Kota/Kabupaten',
                    'sdm_alamat_provinsi' => 'Alamat Provinsi',
                    'sdm_alamat_kodepos' => 'Alamat Kode Pos',
                    'sdm_agama' => 'Agama',
                    'sdm_no_kk' => 'Nomor KK',
                    'sdm_status_kawin' => 'Status Menikah',
                    'sdm_jml_anak' => 'Jumlah Anak',
                    'sdm_pendidikan' => 'Pendidikan',
                    'sdm_jurusan' => 'Jurusan',
                    'sdm_telepon' => 'Telepon',
                    'email' => 'Email',
                    'sdm_disabilitas' => 'Disabilitas',
                    'sdm_no_bpjs' => 'Nomor BPJS',
                    'sdm_no_jamsostek' => 'Nomor Jamsostek',
                    'sdm_no_npwp' => 'NPWM',
                    'sdm_nama_bank' => 'Nama Bank',
                    'sdm_cabang_bank' => 'Cabang Bank',
                    'sdm_rek_bank' => 'Nomor Rekening Bank',
                    'sdm_an_bank' => 'Nama Rekening Bank',
                    'sdm_nama_dok' => 'Nama/Judul Dokumen Titipan',
                    'sdm_nomor_dok' => 'Nomor Dokumen Titipan',
                    'sdm_penerbit_dok' => 'Penerbit Dokumen Titipan',
                    'sdm_an_dok' => 'A.n Dokumen Titipan',
                    'sdm_kadaluarsa_dok' => 'Tanggal Kadaluarsa Dokumen Titipan',
                    'sdm_uk_seragam' => 'Ukuran Seragam',
                    'sdm_uk_sepatu' => 'Ukuran Sepatu',
                    'sdm_ket_kary' => 'Keterangan Karyawan',
                    'sdm_id_pengubah' => 'No Absen Pengurus',
                    'sdm_hak_akses' => 'Hak Akses Aplikasi',
                    'sdm_ijin_akses' => 'Ijin Akses Aplikasi',
                    'sdm_tgl_berhenti' => 'Tanggal Berhenti',
                    'sdm_jenis_berhenti' => 'Jenis Berhenti SDM',
                    'sdm_ket_berhenti' => 'Keterangan Berhenti SDM',
                ]
            );

            $validasi->validate();

            $valid = $validasi->safe();
            $str = str();
            $pengurus = $str->contains($pengguna->sdm_hak_akses, 'SDM-PENGURUS');

            if ($pengurus && blank($ijin_akses)) {
                $data = $valid->except(['foto_profil', 'sdm_berkas']);
            } elseif ($pengurus & !blank($ijin_akses)) {
                $data = $valid->except(['foto_profil', 'sdm_berkas', 'sdm_hak_akses', 'sdm_ijin_akses']);
            } else {
                $data = $valid->except(['foto_profil', 'sdm_berkas', 'sdm_hak_akses', 'sdm_ijin_akses', 'sdm_tgl_berhenti', 'sdm_jenis_berhenti', 'sdm_ket_berhenti', 'sdm_ket_kary', 'sdm_nama_dok', 'sdm_nomor_dok', 'sdm_penerbit_dok', 'sdm_an_dok', 'sdm_kadaluarsa_dok', 'sdm_no_permintaan', 'sdm_no_absen', 'sdm_id_atasan', 'sdm_tgl_gabung', 'sdm_warganegara', 'sdm_disabilitas', 'sdm_nama_bank', 'sdm_cabang_bank', 'sdm_rek_bank', 'sdm_an_bank']);
            }

            $database->table('sdms')->where('sdm_uuid', $uuid)->update($data);

            $foto = $valid->only('foto_profil')['foto_profil'] ?? false;
            $berkas = $valid->only('sdm_berkas')['sdm_berkas'] ?? false;
            $no_absen = $valid->only('sdm_no_absen')['sdm_no_absen'];
            $session = $reqs->session();

            if ($foto) {
                $foto->storeAs('sdm/foto-profil', $no_absen . '.webp');
            }

            if ($berkas && $pengurus && (blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0) || ($no_absen_sdm == $akun->sdm_no_absen))) {
                $berkas->storeAs('sdm/berkas', $no_absen . '.pdf');
            }

            if ($foto && $no_absen == $no_absen_sdm) {
                $sesiJS = "lemparXHR({
                    tujuan : '#tbl-menu',
                    tautan : '{$app->url->route('komponen', ['komponen' => 'menu', 'fragment' => 'avatar'], false)}',
                    normalview : true
                    });";
                $session->flash('sesiJS', $sesiJS);
            }

            $fungsiStatis->hapusCacheSDMUmum();

            $pesan = $fungsiStatis->statusBerhasil();

            $perujuk = $session->get('tautan_perujuk');

            $redirect = $app->redirect;

            return $perujuk ? $redirect->to($perujuk)->with('pesan', $pesan) : $redirect->route('sdm.mulai')->with('pesan', $pesan);
        }

        $aturs = $fungsiStatis->ambilCacheAtur();
        $permintaanSdms = $fungsiStatis->ambilCachePermintaanTambahSDM();
        $atasan = $fungsiStatis->ambilCacheSDM();

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

        $HtmlPenuh = $halaman->make('tambah-ubah-akun', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $tanggapan->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }

    public function ubahSandi()
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        abort_unless($pengguna, 401);

        $database = $app->db;
        $idPenguna = $pengguna->id;

        $akun = $database->query()->select('id')->from('sdms')->where('id', $idPenguna)->first();

        abort_unless($idPenguna == $akun->id, 403, 'Identitas pengguna berbeda.');

        $session = $reqs->session();
        $hash = $app->hash;
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        $halaman = $app->view;

        if ($reqs->isMethod('post')) {
            abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

            $validasiSandi = $app->validator->make(
                $reqs->all(),
                [
                    'password_lama' => ['required', 'string', 'current_password'],
                    'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
                ],
                [],
                [
                    'password_lama' => 'Kata Sandi Lama',
                    'password' => 'Kata Sandi Baru',
                ]
            );

            $validasiSandi->validate();

            $sandiBaru = $hash->make($validasiSandi->safe()->only('password')['password']);

            $database->table('sdms')->where('id', $idPenguna)->update(['password' => $sandiBaru]);

            $session->forget('spanduk');

            return $app->redirect->route('mulai')->with('pesan', 'Sandi berhasil diubah.');
        }

        $HtmlPenuh = $halaman->make('ubah-sandi');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $tanggapan->make($HtmlIsi)->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']) : $HtmlPenuh;
    }

    public function unggah(Rule $rule, FungsiStatis $fungsiStatis)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        if ($reqs->isMethod('post')) {
            abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

            set_time_limit(0);
            ob_implicit_flush();
            ob_end_flush();
            header('X-Accel-Buffering: no');

            $validator = $app->validator;

            $validasifile = $validator->make(
                $reqs->all(),
                [
                    'unggah_profil_sdm' => ['required', 'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
                ],
                [],
                [
                    'unggah_profil_sdm' => 'Berkas Yang Diunggah'
                ]
            );

            $validasifile->validate();

            $file = $validasifile->safe()->only('unggah_profil_sdm')['unggah_profil_sdm'];
            $namafile = 'unggahprofilsdm-' . date('YmdHis') . '.xlsx';

            $storage = $app->filesystem;
            $storage->putFileAs('unggah', $file, $namafile);
            $fileexcel = $app->storagePath("app/unggah/{$namafile}");
            $reader = IOFactory::createReader('Xlsx');
            $spreadsheetInfo = $reader->listWorksheetInfo($fileexcel);
            $chunkSize = 50;
            $chunkFilter = new ChunkReadFilter();
            $reader->setReadFilter($chunkFilter);
            $reader->setReadDataOnly(true);
            $totalRows = $spreadsheetInfo[1]['totalRows'];
            $hash = $app->hash;
            $idPengunggah = $pengguna->sdm_no_absen;

            for ($startRow = 2; $startRow <= $totalRows; $startRow += $chunkSize) {
                $chunkFilter->setRows($startRow, $chunkSize);
                $spreadsheet = $reader->load($fileexcel);
                $worksheet = $spreadsheet->getSheet(1);
                $barisTertinggi = $worksheet->getHighestRow();
                $kolomTertinggi = $worksheet->getHighestColumn();

                $pesanbaca = '<p>Status : Membaca excel baris ' . ($startRow) . ' sampai baris ' . $barisTertinggi . '.</p>';
                $pesansimpan = '<p>Status : Berhasil menyimpan data excel baris ' . ($startRow) . ' sampai baris ' . $barisTertinggi . '.</p>';

                echo $pesanbaca;

                $headingArray = $worksheet->rangeToArray('A1:' . $kolomTertinggi . '1', null, false, false, false);
                $dataArray = $worksheet->rangeToArray('A' . $startRow . ':' . $kolomTertinggi . $barisTertinggi, null, false, false, false);
                $tabel = array_merge($headingArray, $dataArray);
                $isitabel = array_shift($tabel);

                $datas = array_map(function ($x) use ($isitabel) {
                    return array_combine($isitabel, $x);
                }, $tabel);

                $dataexcel = array_map(function ($x) use ($idPengunggah, $hash) {
                    return $x + ['sdm_id_pengunggah' => $idPengunggah] + ['sdm_id_pembuat' => $idPengunggah] + ['sdm_id_pengubah' => $idPengunggah] + ['sdm_diunggah' => date('Y-m-d H:i:s')] + ['password' => $hash->make($x['sdm_no_ktp'])];
                }, $datas);

                $data = array_combine(range(($startRow - 1), count($dataexcel) + ($startRow - 2)), array_values($dataexcel));

                $validasi = $validator->make(
                    $data,
                    [
                        '*.sdm_no_permintaan' => ['sometimes', 'nullable', 'string', 'max:20', 'exists:tambahsdms,tambahsdm_no'],
                        '*.sdm_no_absen' => ['required', 'string', 'max:10'],
                        '*.sdm_tgl_gabung' => ['required', 'date'],
                        '*.sdm_warganegara' => ['required', 'string', 'max:40', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'NEGARA');
                        })],
                        '*.sdm_no_ktp' => ['required', 'string', 'max:20'],
                        '*.sdm_nama' => ['required', 'string', 'max:80'],
                        '*.sdm_tempat_lahir' => ['required', 'string', 'max:40'],
                        '*.sdm_tgl_lahir' => ['required', 'date'],
                        '*.sdm_kelamin' => ['required', 'string', 'max:2', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'KELAMIN');
                        })],
                        '*.sdm_gol_darah' => ['sometimes', 'nullable', 'string', 'max:2', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'GOLONGAN DARAH');
                        })],
                        '*.sdm_alamat' => ['required', 'string', 'max:120'],
                        '*.sdm_alamat_rt' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                        '*.sdm_alamat_rw' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                        '*.sdm_alamat_kelurahan' => ['required', 'string', 'max:40'],
                        '*.sdm_alamat_kecamatan' => ['required', 'string', 'max:40'],
                        '*.sdm_alamat_kota' => ['required', 'string', 'max:40'],
                        '*.sdm_alamat_provinsi' => ['required', 'string', 'max:40'],
                        '*.sdm_alamat_kodepos' => ['sometimes', 'nullable', 'string'],
                        '*.sdm_agama' => ['required', 'string', 'max:20', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'AGAMA');
                        })],
                        '*.sdm_no_kk' => ['sometimes', 'nullable', 'string', 'max:20'],
                        '*.sdm_status_kawin' => ['required', 'string', 'max:10', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'STATUS MENIKAH');
                        })],
                        '*.sdm_jml_anak' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                        '*.sdm_pendidikan' => ['required', 'string', 'max:10', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'PENDIDIKAN');
                        })],
                        '*.sdm_jurusan' => ['sometimes', 'nullable', 'string', 'max:60'],
                        '*.sdm_telepon' => ['required', 'string', 'max:40'],
                        '*.email' => ['required', 'email'],
                        '*.sdm_disabilitas' => ['required', 'string', 'max:30', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'DISABILITAS');
                        })],
                        '*.sdm_no_bpjs' => ['sometimes', 'nullable', 'string', 'max:30'],
                        '*.sdm_no_jamsostek' => ['sometimes', 'nullable', 'string', 'max:30'],
                        '*.sdm_no_npwp' => ['sometimes', 'nullable', 'string', 'max:30'],
                        '*.sdm_nama_bank' => ['sometimes', 'nullable', 'string', 'max:20', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'BANK');
                        })],
                        '*.sdm_cabang_bank' => ['sometimes', 'nullable', 'string', 'max:50'],
                        '*.sdm_rek_bank' => ['sometimes', 'nullable', 'string', 'max:40'],
                        '*.sdm_an_rek' => ['sometimes', 'nullable', 'string', 'max:80'],
                        '*.sdm_nama_dok' => ['sometimes', 'nullable', 'string', 'max:50'],
                        '*.sdm_nomor_dok' => ['sometimes', 'nullable', 'string', 'max:40'],
                        '*.sdm_penerbit_dok' => ['sometimes', 'nullable', 'string', 'max:60'],
                        '*.sdm_an_dok' => ['sometimes', 'nullable', 'string', 'max:80'],
                        '*.sdm_kadaluarsa_dok' => ['sometimes', 'nullable', 'date'],
                        '*.sdm_uk_seragam' => ['sometimes', 'nullable', 'string', 'max:10', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'UKURAN SERAGAM');
                        })],
                        '*.sdm_uk_sepatu' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                        '*.sdm_ket_kary' => ['sometimes', 'nullable', 'string'],
                        '*.sdm_id_pengunggah' => ['sometimes', 'nullable', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                        '*.sdm_tgl_berhenti' => ['sometimes', 'nullable', 'date', 'required_unless:sdm_jenis_berhenti,null'],
                        '*.sdm_jenis_berhenti' => ['sometimes', 'nullable', 'string', 'required_unless:sdm_tgl_berhenti,null'],
                        '*.sdm_ket_berhenti' => ['sometimes', 'nullable', 'string'],
                        '*.sdm_id_atasan' => ['sometimes', 'nullable', 'string', 'max:10', 'different:sdm_no_absen', 'exists:sdms,sdm_no_absen'],
                        '*.sdm_id_pengunggah' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                        '*.sdm_id_pembuat' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                        '*.sdm_id_pengubah' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                        '*.sdm_diunggah' => ['required', 'nullable', 'date'],
                    ],
                    [
                        '*.sdm_no_permintaan.*' => 'Nomor Permintaan baris ke-:position maksimal 20 karakter dan wajib terdaftar Permintaan SDM.',
                        '*.sdm_no_absen.*' => 'Nomor Absen baris ke-:position maksimal 10 karakter.',
                        '*.sdm_tgl_gabung.*' => 'Tanggal Bergabung baris ke-:position wajib berupa tanggal.',
                        '*.sdm_warganegara.*' => 'Warga Negara baris ke-:position maksimal 20 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_no_ktp.*' => 'No KTP Passport baris ke-:position maksimal 20 karakter.',
                        '*.sdm_nama.*' => 'Nama SDM baris ke-:position maksimal 80 karakter.',
                        '*.sdm_tempat_lahir.*' => 'Tempat Lahir baris ke-:position maksimal 40 karakter.',
                        '*.sdm_tgl_lahir.*' => 'Tanggal Lahir baris ke-:position wajib berupa tanggal.',
                        '*.sdm_kelamin.*' => 'Kelamin baris ke-:position maksimal 2 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_gol_darah.*' => 'Golongan Darah baris ke-:position maksimal 2 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_alamat.*' => 'Alamat baris ke-:position maksimal 120 karakter.',
                        '*.sdm_alamat_rt.*' => 'Alamat RT baris ke-:position wajib berupa angka lebih dari 0.',
                        '*.sdm_alamat_rw.*' => 'Alamat RW baris ke-:position wajib berupa angka lebih dari 0.',
                        '*.sdm_alamat_kelurahan.*' => 'Kelurahan baris ke-:position maksimal 40 karakter.',
                        '*.sdm_alamat_kecamatan.*' => 'Kecamatan baris ke-:position maksimal 40 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_alamat_kota.*' => 'Kota baris ke-:position maksimal 40 karakter.',
                        '*.sdm_alamat_provinsi.*' => 'Provinsi baris ke-:position maksimal 40 karakter.',
                        '*.sdm_alamat_kodepos.*' => 'Kode Pos baris ke-:position maksimal 10 karakter.',
                        '*.sdm_agama.*' => 'Agama baris ke-:position maksimal 20 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_no_kk.*' => 'Nomor KK baris ke-:position maksimal 20 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_status_kawin.*' => 'Status Menikah baris ke-:position maksimal 10 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_jml_anak.*' => 'Jumlah Anak baris ke-:position wajib berupa angka lebih dari 0.',
                        '*.sdm_pendidikan.*' => 'Pendidikan baris ke-:position maksimal 10 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_jurusan.*' => 'Jurusan baris ke-:position maksimal 60 karakter.',
                        '*.sdm_telepon.*' => 'Telepon baris ke-:position maksimal 40 karakter.',
                        '*.email.*' => 'Email baris ke-:position wajib berupa email.',
                        '*.sdm_disabilitas.*' => 'Disabilitas baris ke-:position maksimal 30 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_no_bpjs.*' => 'No BPJS baris ke-:position maksimal 30 karakter.',
                        '*.sdm_no_jamsostek.*' => 'No Jamsostek baris ke-:position maksimal 30 karakter.',
                        '*.sdm_no_npwp.*' => 'NPWP baris ke-:position maksimal 30 karakter.',
                        '*.sdm_nama_bank.*' => 'Nama Bank baris ke-:position maksimal 20 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_cabang_bank.*' => 'Cabang Bank baris ke-:position maksimal 50 karakter.',
                        '*.sdm_rek_bank.*' => 'Nomor Rekening Bank baris ke-:position maksimal 40 karakter.',
                        '*.sdm_an_rek.*' => 'A.n Rekening baris ke-:position maksimal 40 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_nama_dok.*' => 'Judul Dokumen Titipan baris ke-:position maksimal 50 karakter.',
                        '*.sdm_nomor_dok.*' => 'Nomor Dokumen Titipan baris ke-:position maksimal 40 karakter.',
                        '*.sdm_penerbit_dok.*' => 'Penembit Dokumen Titipan baris ke-:position maksimal 60 karakter.',
                        '*.sdm_an_dok.*' => 'A.n Dokumen Titipan baris ke-:position maksimal 80 karakter.',
                        '*.sdm_kadaluarsa_dok.*' => 'Kadaluarsa Dokumen Titipan baris ke-:position wajib berupa tanggal.',
                        '*.sdm_uk_seragam.*' => 'Ukuran Seragam baris ke-:position maksimal 10 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_uk_sepatu.*' => 'Ukuran Sepatu baris ke-:position wajib berupa angka lebih dari 0.',
                        '*.sdm_ket_kary.*' => 'Keterangan SDM baris ke-:position wajib berupa karakter.',
                        '*.sdm_id_pengunggah.*' => 'ID Pengunggah baris ke-:position maksimal 10 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_tgl_berhenti.*' => 'ID Pengunggah baris ke-:position wajib berupa tanggal jika Jenis Berhenti terisi.',
                        '*.sdm_jenis_berhenti.*' => 'ID Pengunggah baris ke-:position wajib berupa tanggal jika Jenis Berhenti terisi.',
                        '*.sdm_ket_berhenti.*' => 'Keterangan Berhenti baris ke-:position wajib berupa karakter.',
                        '*.sdm_id_atasan.*' => 'ID Atasan baris ke-:position maksimal 10 karakter, berbeda dengan No Absen SDM dan terdaftar di data SDM.',
                        '*.sdm_id_pengunggah.*' => 'ID Pengunggah baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                        '*.sdm_id_pembuat.*' => 'ID Pembuat baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                        '*.sdm_id_pengubah.*' => 'ID Pengubah baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                        '*.sdm_diunggah.*' => 'Waktu Unggah baris ke-:position wajib berupa tanggal.',
                    ]
                );

                $validasi->validate();

                $app->db->table('sdms')->upsert(
                    $validasi->validated(),
                    ['sdm_no_absen'],
                    ['sdm_no_permintaan', 'sdm_tgl_gabung', 'sdm_warganegara', 'sdm_no_ktp', 'sdm_nama', 'sdm_tempat_lahir', 'sdm_tgl_lahir', 'sdm_kelamin', 'sdm_gol_darah', 'sdm_alamat', 'sdm_alamat_rt', 'sdm_alamat_rw', 'sdm_alamat_kelurahan', 'sdm_alamat_kecamatan', 'sdm_alamat_kota', 'sdm_alamat_provinsi', 'sdm_alamat_kodepos', 'sdm_agama', 'sdm_no_kk', 'sdm_status_kawin', 'sdm_jml_anak', 'sdm_pendidikan', 'sdm_jurusan', 'sdm_telepon', 'email', 'sdm_disabilitas', 'sdm_no_bpjs', 'sdm_no_jamsostek', 'sdm_no_npwp', 'sdm_nama_bank', 'sdm_cabang_bank', 'sdm_rek_bank', 'sdm_an_rek', 'sdm_nama_dok', 'sdm_nomor_dok', 'sdm_penerbit_dok', 'sdm_an_dok', 'sdm_kadaluarsa_dok', 'sdm_uk_seragam', 'sdm_uk_sepatu', 'sdm_ket_kary', 'sdm_tgl_berhenti', 'sdm_jenis_berhenti', 'sdm_ket_berhenti', 'sdm_id_atasan', 'sdm_id_pengunggah', 'sdm_id_pengubah', 'sdm_diunggah']
                );

                echo $pesansimpan;
            };

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            $storage->delete($fileexcel);

            $fungsiStatis->hapusCacheSDMUmum();
            $pesan = $fungsiStatis->statusBerhasil();

            return $app->redirect->route('atur.data')->with('pesan', $pesan);
        }

        $HtmlPenuh = $app->view->make('unggah');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }
}
