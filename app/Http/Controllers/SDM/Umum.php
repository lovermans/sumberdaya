<?php

namespace App\Http\Controllers\SDM;

use App\Tambahan\FungsiStatis;

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

        if($reqs->fragment == 'navigasi') {
            $navigasi = $halaman->make('sdm.navigasi');
            return $tanggapan->make($navigasi)->withHeaders(['Vary' => 'Accept']);
        }

        $linkupIjin = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $linkupIjin));
        $cache = $app->cache;
        $date = $app->date;        
        $database = $app->db;
        
        $kontrak = $database->query()->select('penempatan_uuid', 'penempatan_no_absen', 'penempatan_posisi', 'penempatan_lokasi', 'penempatan_kontrak', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_keterangan')
        ->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
            $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
        });
        
        if($reqs->fragment == 'sdmIngatUltah') {
            
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

        if ($str->contains($pengguna->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN'])) {
            
            $kemarin = $date->today()->subDay()->format('Y-m-d');
            $hariIni = $date->today()->format('Y-m-d');
            
            if($reqs->fragment == 'sdmIngatPtsb') {
                
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

            if($reqs->fragment == 'sdmIngatPkpd') {

                $cache->forget('PengingatKontrak - ' . $kemarin);

                $cache_kontraks = $cache->rememberForever('PengingatKontrak - ' . $hariIni, function () use ($kontrak, $database, $date) {
                    return $database->query()->select('sdm_uuid', 'sdm_no_absen', 'sdm_nama', 'penempatan_uuid', 'penempatan_posisi', 'penempatan_lokasi', 'penempatan_kontrak', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke')
                        ->from('sdms')
                        ->leftJoinSub($kontrak, 'kontrak', function ($join) {
                            $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
                        })
                        ->whereNull('sdm_tgl_berhenti')
                        ->where('penempatan_kontrak', 'not like', 'OS-%')
                        ->where('penempatan_selesai', '<=', $date->today()->addDays(40)->toDateString())
                        ->latest('penempatan_selesai')
                        ->get();
                });

                $kontraks = $cache_kontraks->when($linkupIjin, function ($c) use ($lingkup) {
                    return $c->whereIn('penempatan_lokasi', [...$lingkup]);
                });

                $sdmIngatPkpd = $halaman->make('sdm.pengingat.pkwt-perlu-ditinjau', ['kontraks' => $kontraks ?? null]);
                return $tanggapan->make($sdmIngatPkpd)->withHeaders(['Vary' => 'Accept']);
            
            }

            $bulanLalu = $date->today()->subDays(40)->toDateString();

            if($reqs->fragment == 'sdmIngatPstatus') {

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

            if($reqs->fragment == 'sdmIngatBaru') {

                $cache->forget('PengingatAkunBaru - ' . $kemarin);

                $cache_barus = $cache->rememberForever('PengingatAkunBaru - ' . $hariIni, function () use ($kontrak, $database, $bulanLalu) {
                    return $database->query()->select('sdm_uuid', 'sdm_no_absen', 'sdm_nama', 'sdm_no_permintaan',  'sdm_no_ktp', 'sdm_tgl_gabung', 'penempatan_uuid', 'penempatan_posisi', 'penempatan_lokasi', 'penempatan_kontrak')
                        ->from('sdms')
                        ->leftJoinSub($kontrak, 'kontrak', function ($join) {
                            $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
                        })
                        ->whereNull('sdm_tgl_berhenti')
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

            if($reqs->fragment == 'sdmIngatKeluar') {
            
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
            
        }

        $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrl()]);

        $HtmlPenuh = $halaman->make('sdm.mulai');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $tanggapan->make($HtmlIsi)->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']) : $HtmlPenuh;
    }
}
