<?php

namespace App\Http\Controllers\SDM;

use App\Interaksi\Cache;
use App\Interaksi\Berkas;
use App\Interaksi\Rangka;
use Illuminate\Support\Arr;
use App\Interaksi\SDM\SDMCache;
use App\Interaksi\SDM\SDMExcel;
use App\Interaksi\SDM\SDMBerkas;
use App\Interaksi\SDM\SDMDBQuery;
use App\Interaksi\SDM\SDMValidasi;

class Umum
{
    public function mulai()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        $str = str();

        abort_unless($str->contains($pengguna?->sdm_hak_akses, 'SDM'), 403, 'Akses dibatasi hanya untuk Pengguna Aplikasi SDM.');

        $pengurus = $str->contains($pengguna->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']);
        $fragmen = $reqs->fragment;

        $respon = match (true) {
            $fragmen == 'navigasi' => $this->halamanNavigasi(),
            $fragmen == 'sdmIngatUltah' => $this->halamanUltah(),
            $pengurus && $fragmen == 'sdmIngatPtsb' => $this->halamanPermintaanTambahSDM(),
            $pengurus && $fragmen == 'sdmIngatPkpd' => $this->halamanPKWTHabis(),
            $pengurus && $fragmen == 'sdmIngatPstatus' => $this->halamanPerubahanStatusSDMTerbaru(),
            $pengurus && $fragmen == 'sdmIngatBaru' => $this->halamanSDMGabungTerbaru(),
            $pengurus && $fragmen == 'sdmIngatKeluar' => $this->halamanSDMKeluarTerbaru(),
            $pengurus && $fragmen == 'sdmIngatPelanggaran' => $this->halamanPelanggaran(),
            $pengurus && $fragmen == 'sdmIngatSanksi' => $this->halamanSanksi(),
            $pengurus && $fragmen == 'sdmIngatNilai' => $this->halamanNilai(),
            default => $this->halamanAwal(),
        };

        return $respon;
    }

    public function halamanNavigasi()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless(str()->contains($pengguna?->sdm_hak_akses, 'SDM'), 403, 'Akses dibatasi hanya untuk Pengguna Aplikasi SDM.');

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($app->view->make('sdm.navigasi'))->withHeaders(['Vary' => 'Accept']);
    }

    public function halamanAwal()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless(str()->contains($pengguna?->sdm_hak_akses, 'SDM'), 403, 'Akses dibatasi hanya untuk Pengguna Aplikasi SDM.');

        $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrl()]);

        $HtmlPenuh = $app->view->make('sdm.mulai');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $HtmlPenuh;
    }

    public function halamanUltah()
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

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($app->view->make('sdm.pengingat.sdm-ultah', $data))->withHeaders(['Vary' => 'Accept']);
    }

    public function halamanPermintaanTambahSDM()
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

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($app->view->make('sdm.pengingat.permintaan-tambah-sdm', ['perminSDMS' => $perminSDMS ?? null]))->withHeaders(['Vary' => 'Accept']);
    }

    public function halamanPKWTHabis()
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

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($app->view->make('sdm.pengingat.pkwt-perlu-ditinjau', $data))->withHeaders(['Vary' => 'Accept']);
    }

    public function halamanPerubahanStatusSDMTerbaru()
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

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($app->view->make('sdm.pengingat.perubahan-status', $data))->withHeaders(['Vary' => 'Accept']);
    }

    public function halamanSDMGabungTerbaru()
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

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($app->view->make('sdm.pengingat.sdm-baru', $data))->withHeaders(['Vary' => 'Accept']);
    }

    public function halamanSDMKeluarTerbaru()
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

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($app->view->make('sdm.pengingat.sdm-keluar', $data))->withHeaders(['Vary' => 'Accept']);
    }

    public function halamanPelanggaran()
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

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($app->view->make('sdm.pengingat.pelanggaran', $data))->withHeaders(['Vary' => 'Accept']);
    }

    public function halamanSanksi()
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

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($app->view->make('sdm.pengingat.sanksi', $data))->withHeaders(['Vary' => 'Accept']);
    }

    public function halamanNilai()
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

        $rataTahunLalu = $nilais->where('nilaisdm_tahun', $tahunLalu)->avg('nilaisdm_total');
        $rataTahunIni = $nilais->where('nilaisdm_tahun', $tahunIni)->avg('nilaisdm_total');

        $data = [
            'rataTahunLalu' => $rataTahunLalu ?? 0,
            'rataTahunIni' => $rataTahunIni ?? 0
        ];

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($app->view->make('sdm.pengingat.nilai', $data))->withHeaders(['Vary' => 'Accept']);
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
        $lingkup_lokasi = collect($akun->lokasi_akun);
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
