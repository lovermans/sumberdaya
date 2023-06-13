<?php

namespace App\Http\Controllers\SDM;

use App\Tambahan\FungsiStatis;
use Illuminate\Validation\Rule;
use App\Http\Controllers\SDM\Berkas;

class Penempatan
{
    public function index(FungsiStatis $fungsiStatis, Berkas $berkas, $uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && ($str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']) || $pengguna->sdm_uuid == $uuid), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $validator = $app->validator->make(
            $reqs->all(),
            $this->dasarValidasiPencarian(),
            $this->atributInputPencarian(),
        );

        if ($validator->fails()) {
            return $app->redirect->route('sdm.penempatan.riwayat')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $urutArray = $reqs->urut;
        $uruts = $urutArray ? implode(',', array_filter($urutArray)) : null;
        $kataKunci = $reqs->kata_kunci;
        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $cari = $this->dataSDM()->clone()->addSelect('penempatan_uuid', 'penempatan_no_absen', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_lokasi', 'penempatan_posisi', 'penempatan_kategori', 'penempatan_kontrak', 'penempatan_pangkat', 'penempatan_golongan', 'penempatan_grup', 'penempatan_keterangan', 'posisi_wlkp', $app->db->raw('IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, sdm_tgl_berhenti)) as masa_kerja, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, penempatan_mulai, NOW()),TIMESTAMPDIFF(YEAR, penempatan_mulai, sdm_tgl_berhenti)) as masa_aktif, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, sdm_tgl_berhenti)) as usia'))
            ->leftJoinSub($this->dataDasar(), 'penem', function ($join) {
                $join->on('sdm_no_absen', '=', 'penem.penempatan_no_absen');
            })
            ->leftJoinSub($this->dataPosisi(), 'pos', function ($join) {
                $join->on('penempatan_posisi', '=', 'pos.posisi_nama');
            })
            ->leftJoinSub($this->dataPermintaanTambahSDM(), 'tsdm', function ($join) {
                $join->on('sdm_no_permintaan', '=', 'tsdm.tambahsdm_no');
            })
            ->when($reqs->kontrak, function ($query) use ($reqs) {
                $query->whereIn('penempatan_kontrak', (array) $reqs->kontrak);
            })
            ->when($kataKunci, function ($query, $kataKunci) {
                $query->where(function ($group) use ($kataKunci) {
                    $group->where('sdm_no_absen', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_no_absen', 'like', '%' . $kataKunci . '%')
                        ->orWhere('sdm_no_permintaan', $kataKunci)
                        ->orWhere('sdm_no_ktp', 'like', '%' . $kataKunci . '%')
                        ->orWhere('sdm_nama', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_posisi', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_keterangan', 'like', '%' . $kataKunci . '%');
                });
            })
            ->when($uuid, function ($query) use ($uuid) {
                $query->where('sdm_uuid', $uuid);
            })
            ->when($lingkupIjin, function ($query, $lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->whereIn('tambahsdm_penempatan', $lingkupIjin)
                        ->orWhereIn('penempatan_lokasi', $lingkupIjin)
                        ->orWhereNull('penempatan_lokasi');
                });
            })
            ->when($reqs->lokasi, function ($query) use ($reqs) {
                $query->whereIn('penempatan_lokasi', (array) $reqs->lokasi);
            })
            ->when($reqs->kategori, function ($query) use ($reqs) {
                $query->whereIn('penempatan_kategori', (array) $reqs->kategori);
            })
            ->when($reqs->pangkat, function ($query) use ($reqs) {
                $query->whereIn('penempatan_pangkat', (array) $reqs->pangkat);
            })
            ->when($reqs->kelamin, function ($query) use ($reqs) {
                $query->where('sdm_kelamin', $reqs->kelamin);
            })
            ->when($reqs->posisi, function ($query) use ($reqs) {
                $query->whereIn('penempatan_posisi', (array) $reqs->posisi);
            })
            ->when($reqs->agama, function ($query)  use ($reqs) {
                $query->whereIn('sdm_agama', (array) $reqs->agama);
            })
            ->when($reqs->kawin, function ($query)  use ($reqs) {
                $query->whereIn('sdm_status_kawin', (array) $reqs->kawin);
            })
            ->when($reqs->warganegara, function ($query)  use ($reqs) {
                $query->whereIn('sdm_warganegara', (array) $reqs->warganegara);
            })
            ->when($reqs->pendidikan, function ($query)  use ($reqs) {
                $query->whereIn('sdm_pendidikan', (array) $reqs->pendidikan);
            })
            ->when($reqs->disabilitas, function ($query)  use ($reqs) {
                $query->whereIn('sdm_disabilitas', (array) $reqs->disabilitas);
            })
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->latest('penempatan_mulai')->orderBy('sdm_no_absen', 'desc');
                }
            );

        if ($reqs->unduh == 'excel') {
            return $berkas->unduhIndexPenempatanSDM($cari, $app);
        }

        $kunciUrut = array_filter((array) $urutArray);

        $urutAbsen = $str->contains($uruts, 'sdm_no_absen');
        $indexAbsen = (head(array_keys($kunciUrut, 'sdm_no_absen ASC')) + head(array_keys($kunciUrut, 'sdm_no_absen DESC')) + 1);
        $urutMasuk = $str->contains($uruts, 'sdm_tgl_gabung');
        $indexMasuk = (head(array_keys($kunciUrut, 'sdm_tgl_gabung ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_gabung DESC')) + 1);
        $urutLahir = $str->contains($uruts, 'sdm_tgl_lahir');
        $indexLahir = (head(array_keys($kunciUrut, 'sdm_tgl_lahir ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_lahir DESC')) + 1);
        $urutKeluar = $str->contains($uruts, 'sdm_tgl_berhenti');
        $indexKeluar = (head(array_keys($kunciUrut, 'sdm_tgl_berhenti ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_berhenti DESC')) + 1);

        $tabels = $cari->clone()->paginate($reqs->bph ?: 100)->withQueryString()->appends(['fragment' => 'riwa-penem-sdm_tabels', 'uuid' => $uuid ?? '']);

        $aturs = $fungsiStatis->ambilCacheAtur();
        $posisis = $fungsiStatis->ambilCachePosisiSDM();

        $data = [
            'tabels' => $tabels,
            'penempatans' => $aturs->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin);
            })->sortBy(['atur_butir', 'asc']),
            'kontraks' => $aturs->where('atur_jenis', 'STATUS KONTRAK')->sortBy(['atur_butir', 'asc']),
            'kategoris' => $aturs->where('atur_jenis', 'KATEGORI')->sortBy(['atur_butir', 'asc']),
            'pangkats' => $aturs->where('atur_jenis', 'PANGKAT')->sortBy(['atur_butir', 'asc']),
            'kelamins' => $aturs->where('atur_jenis', 'KELAMIN')->sortBy(['atur_butir', 'asc']),
            'agamas' => $aturs->where('atur_jenis', 'AGAMA')->sortBy(['atur_butir', 'asc']),
            'kawins' => $aturs->where('atur_jenis', 'STATUS MENIKAH')->sortBy(['atur_butir', 'asc']),
            'pendidikans' => $aturs->where('atur_jenis', 'PENDIDIKAN')->sortBy(['atur_butir', 'asc']),
            'warganegaras' => $aturs->where('atur_jenis', 'NEGARA')->sortBy(['atur_butir', 'asc']),
            'disabilitases' => $aturs->where('atur_jenis', 'DISABILITAS')->sortBy(['atur_butir', 'asc']),
            'posisis' => $posisis,
            'urutAbsen' => $urutAbsen,
            'indexAbsen' => $indexAbsen,
            'urutMasuk' => $urutMasuk,
            'indexMasuk' => $indexMasuk,
            'urutLahir' => $urutLahir,
            'indexLahir' => $indexLahir,
            'urutKeluar' => $urutKeluar,
            'indexKeluar' => $indexKeluar,
            'halamanAkun' => $uuid ?? '',
        ];

        if (!isset($uuid)) {
            $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);
        }

        $HtmlPenuh = $app->view->make('sdm.penempatan.riwayat', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
            ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function indexMasaKerjaNyata(FungsiStatis $fungsiStatis, Berkas $berkas)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $validator = $app->validator->make(
            $reqs->all(),
            $this->dasarValidasiPencarian(),
            $this->atributInputPencarian(),
        );

        if ($validator->fails()) {
            return $app->redirect->route('sdm.penempatan.riwayat-nyata')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $urutArray = $reqs->urut;
        $uruts = $urutArray ? implode(',', array_filter($urutArray)) : null;
        $kataKunci = $reqs->kata_kunci;
        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $cari = $app->db->query()->addSelect('sdmlama.*', 'penempatan_uuid', 'penempatan_no_absen', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_lokasi', 'penempatan_posisi', 'penempatan_kategori', 'penempatan_kontrak', 'penempatan_pangkat', 'penempatan_golongan', 'penempatan_grup', 'penempatan_keterangan', 'posisi_wlkp', 'uuid as sdm_uuid', 'no_absen as sdm_no_absen', 'tgl_lahir as sdm_tgl_lahir', 'tempat_lahir as sdm_tempat_lahir', 'no_ktp as sdm_no_ktp', 'nama as sdm_nama', 'kelamin as sdm_kelamin', 'tgl_berhenti as sdm_tgl_berhenti', 'jenis_berhenti as sdm_jenis_berhenti', 'ket_berhenti as sdm_ket_berhenti', 'disabilitas as sdm_disabilitas', 'agama as sdm_agama', 'status_kawin as sdm_status_kawin', 'pendidikan as sdm_pendidikan', 'warganegara as sdm_warganegara', 'uk_seragam as sdm_uk_seragam', 'uk_sepatu as sdm_uk_sepatu', 'jurusan as sdm_jurusan', 'telepon as sdm_telepon', 'sdm_email as email', 'id_atasan as sdm_id_atasan', 'no_bpjs as sdm_no_bpjs', 'no_jamsostek as sdm_no_jamsostek', 'jml_anak as sdm_jml_anak', $app->db->raw('IF(tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, tgl_berhenti)) as masa_kerja, IF(tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, penempatan_mulai, NOW()),TIMESTAMPDIFF(YEAR, penempatan_mulai, tgl_berhenti)) as masa_aktif, IF(tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, tgl_lahir, NOW()),TIMESTAMPDIFF(YEAR, tgl_lahir, tgl_berhenti)) as usia'))->fromSub($this->dataSDMKTPTerlama(), 'sdmlama')
            ->joinSub($this->dataSDMKTPTerbaru(), 'sdmbaru', function ($join) {
                $join->on('sdmlama.sdm_no_ktp', '=', 'sdmbaru.no_ktp');
            })
            ->joinSub($this->dataPenempatanTerkini(), 'penem', function ($join) {
                $join->on('sdmbaru.no_absen', '=', 'penem.penempatan_no_absen');
            })
            ->leftjoinSub($this->dataPosisi(), 'pos', function ($join) {
                $join->on('penempatan_posisi', '=', 'pos.posisi_nama');
            })
            ->leftJoinSub($this->dataPermintaanTambahSDM(), 'tsdm', function ($join) {
                $join->on('sdm_no_permintaan', '=', 'tsdm.tambahsdm_no');
            })
            ->when($kataKunci, function ($query, $kataKunci) {
                $query->where(function ($group) use ($kataKunci) {
                    $group->where('no_absen', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_no_absen', 'like', '%' . $kataKunci . '%')
                        ->orWhere('sdm_no_permintaan', $kataKunci)
                        ->orWhere('no_ktp', 'like', '%' . $kataKunci . '%')
                        ->orWhere('nama', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_posisi', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_keterangan', 'like', '%' . $kataKunci . '%');
                });
            })
            ->when($reqs->kontrak, function ($query)  use ($reqs) {
                $query->whereIn('penempatan_kontrak', (array) $reqs->kontrak);
            })
            ->when($lingkupIjin, function ($query, $lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->whereIn('tambahsdm_penempatan', $lingkupIjin)
                        ->orWhereIn('penempatan_lokasi', $lingkupIjin)
                        ->orWhereNull('penempatan_lokasi');
                });
            })
            ->when($reqs->lokasi, function ($query) use ($reqs) {
                $query->whereIn('penempatan_lokasi', (array) $reqs->lokasi);
            })
            ->when($reqs->kategori, function ($query) use ($reqs) {
                $query->whereIn('penempatan_kategori', (array) $reqs->kategori);
            })
            ->when($reqs->pangkat, function ($query) use ($reqs) {
                $query->whereIn('penempatan_pangkat', (array) $reqs->pangkat);
            })
            ->when($reqs->kelamin, function ($query) use ($reqs) {
                $query->where('kelamin', $reqs->kelamin);
            })
            ->when($reqs->posisi, function ($query) use ($reqs) {
                $query->whereIn('penempatan_posisi', (array) $reqs->posisi);
            })
            ->when($reqs->agama, function ($query)  use ($reqs) {
                $query->whereIn('agama', (array) $reqs->agama);
            })
            ->when($reqs->kawin, function ($query)  use ($reqs) {
                $query->whereIn('status_kawin', (array) $reqs->kawin);
            })
            ->when($reqs->warganegara, function ($query)  use ($reqs) {
                $query->whereIn('warganegara', (array) $reqs->warganegara);
            })
            ->when($reqs->pendidikan, function ($query)  use ($reqs) {
                $query->whereIn('pendidikan', (array) $reqs->pendidikan);
            })
            ->when($reqs->disabilitas, function ($query)  use ($reqs) {
                $query->whereIn('disabilitas', (array) $reqs->disabilitas);
            })
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->latest('sdm_tgl_gabung')->latest('penempatan_mulai')->orderBy('sdm_no_absen', 'desc');
                }
            );

        if ($reqs->unduh == 'excel') {
            return $berkas->unduhIndexMasaKerjaNyata($cari, $app);
        }

        $kunciUrut = array_filter((array) $urutArray);

        $urutAbsen = $str->contains($uruts, 'sdm_no_absen');
        $indexAbsen = (head(array_keys($kunciUrut, 'sdm_no_absen ASC')) + head(array_keys($kunciUrut, 'sdm_no_absen DESC')) + 1);
        $urutMasuk = $str->contains($uruts, 'sdm_tgl_gabung');
        $indexMasuk = (head(array_keys($kunciUrut, 'sdm_tgl_gabung ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_gabung DESC')) + 1);
        $urutLahir = $str->contains($uruts, 'sdm_tgl_lahir');
        $indexLahir = (head(array_keys($kunciUrut, 'sdm_tgl_lahir ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_lahir DESC')) + 1);
        $urutKeluar = $str->contains($uruts, 'sdm_tgl_berhenti');
        $indexKeluar = (head(array_keys($kunciUrut, 'sdm_tgl_berhenti ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_berhenti DESC')) + 1);

        $tabels = $cari->clone()->paginate($reqs->bph ?: 100)->withQueryString()->appends(['fragment' => 'riwa-penem-sdm_tabels']);

        $aturs = $fungsiStatis->ambilCacheAtur();
        $posisis = $fungsiStatis->ambilCachePosisiSDM();

        $data = [
            'tabels' => $tabels,
            'penempatans' => $aturs->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin);
            })->sortBy(['atur_butir', 'asc']),
            'kontraks' => $aturs->where('atur_jenis', 'STATUS KONTRAK')->sortBy(['atur_butir', 'asc']),
            'kategoris' => $aturs->where('atur_jenis', 'KATEGORI')->sortBy(['atur_butir', 'asc']),
            'pangkats' => $aturs->where('atur_jenis', 'PANGKAT')->sortBy(['atur_butir', 'asc']),
            'kelamins' => $aturs->where('atur_jenis', 'KELAMIN')->sortBy(['atur_butir', 'asc']),
            'agamas' => $aturs->where('atur_jenis', 'AGAMA')->sortBy(['atur_butir', 'asc']),
            'kawins' => $aturs->where('atur_jenis', 'STATUS MENIKAH')->sortBy(['atur_butir', 'asc']),
            'pendidikans' => $aturs->where('atur_jenis', 'PENDIDIKAN')->sortBy(['atur_butir', 'asc']),
            'warganegaras' => $aturs->where('atur_jenis', 'NEGARA')->sortBy(['atur_butir', 'asc']),
            'disabilitases' => $aturs->where('atur_jenis', 'DISABILITAS')->sortBy(['atur_butir', 'asc']),
            'posisis' => $posisis,
            'urutAbsen' => $urutAbsen,
            'indexAbsen' => $indexAbsen,
            'urutMasuk' => $urutMasuk,
            'indexMasuk' => $indexMasuk,
            'urutLahir' => $urutLahir,
            'indexLahir' => $indexLahir,
            'urutKeluar' => $urutKeluar,
            'indexKeluar' => $indexKeluar,
        ];

        if (!isset($uuid)) {
            $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);
        }

        $HtmlPenuh = $app->view->make('sdm.penempatan.riwayat', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
            ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function indexAktif(FungsiStatis $fungsiStatis, Berkas $berkas)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $validator = $app->validator->make(
            $reqs->all(),
            $this->dasarValidasiPencarian(),
            $this->atributInputPencarian(),
        );

        if ($validator->fails()) {
            return $app->redirect->route('sdm.penempatan.data-aktif')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $urutArray = $reqs->urut;
        $uruts = $urutArray ? implode(',', array_filter($urutArray)) : null;
        $kataKunci = $reqs->kata_kunci;
        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $cari = $this->dataPenempatanTerkini()->clone()->addSelect('sdm_uuid', 'sdm_no_absen', 'sdm_no_permintaan', 'sdm_tempat_lahir', 'sdm_tgl_lahir', 'sdm_tgl_gabung', 'sdm_no_ktp', 'sdm_nama', 'sdm_kelamin', 'sdm_tgl_berhenti', 'sdm_jenis_berhenti', 'sdm_ket_berhenti', 'posisi_wlkp', 'sdm_disabilitas', 'sdm_ket_kary', 'sdm_alamat', 'sdm_alamat_rt', 'sdm_alamat_rw', 'sdm_alamat_kelurahan', 'sdm_alamat_kecamatan', 'sdm_alamat_kota', 'sdm_alamat_provinsi', 'sdm_alamat_kodepos', 'sdm_agama', 'sdm_status_kawin', 'sdm_pendidikan', 'sdm_warganegara', 'sdm_uk_seragam', 'sdm_uk_sepatu', 'sdm_jurusan', 'sdm_telepon', 'email', 'sdm_id_atasan', 'sdm_no_bpjs', 'sdm_no_jamsostek', 'sdm_jml_anak', $app->db->raw('IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, sdm_tgl_berhenti)) as masa_kerja, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, penempatan_mulai, NOW()),TIMESTAMPDIFF(YEAR, penempatan_mulai, sdm_tgl_berhenti)) as masa_aktif, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, sdm_tgl_berhenti)) as usia'))
            ->joinSub($this->dataSDM(), 'sdm', function ($join) {
                $join->on('penempatan_no_absen', '=', 'sdm.sdm_no_absen');
            })
            ->leftJoinSub($this->dataPosisi(), 'pos', function ($join) {
                $join->on('penempatan_posisi', '=', 'pos.posisi_nama');
            })
            ->when($kataKunci, function ($query, $kataKunci) {
                $query->where(function ($group) use ($kataKunci) {
                    $group->where('sdm_no_absen', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_no_absen', 'like', '%' . $kataKunci . '%')
                        ->orWhere('sdm_no_permintaan', $kataKunci)
                        ->orWhere('sdm_no_ktp', 'like', '%' . $kataKunci . '%')
                        ->orWhere('sdm_nama', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_posisi', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_keterangan', 'like', '%' . $kataKunci . '%');
                });
            })
            ->when($reqs->kontrak, function ($query)  use ($reqs) {
                $query->whereIn('penempatan_kontrak', (array) $reqs->kontrak);
            })
            ->when($lingkupIjin, function ($query, $lingkupIjin) {
                $query->whereIn('penempatan_lokasi', $lingkupIjin);
            })
            ->when($reqs->lokasi, function ($query) use ($reqs) {
                $query->whereIn('penempatan_lokasi', (array) $reqs->lokasi);
            })
            ->when($reqs->kategori, function ($query) use ($reqs) {
                $query->whereIn('penempatan_kategori', (array) $reqs->kategori);
            })
            ->when($reqs->pangkat, function ($query) use ($reqs) {
                $query->whereIn('penempatan_pangkat', (array) $reqs->pangkat);
            })
            ->when($reqs->kelamin, function ($query) use ($reqs) {
                $query->where('sdm_kelamin', $reqs->kelamin);
            })
            ->when($reqs->posisi, function ($query) use ($reqs) {
                $query->whereIn('penempatan_posisi', (array) $reqs->posisi);
            })
            ->when($reqs->agama, function ($query)  use ($reqs) {
                $query->whereIn('sdm_agama', (array) $reqs->agama);
            })
            ->when($reqs->kawin, function ($query)  use ($reqs) {
                $query->whereIn('sdm_status_kawin', (array) $reqs->kawin);
            })
            ->when($reqs->warganegara, function ($query)  use ($reqs) {
                $query->whereIn('sdm_warganegara', (array) $reqs->warganegara);
            })
            ->when($reqs->pendidikan, function ($query)  use ($reqs) {
                $query->whereIn('sdm_pendidikan', (array) $reqs->pendidikan);
            })
            ->when($reqs->disabilitas, function ($query)  use ($reqs) {
                $query->whereIn('sdm_disabilitas', (array) $reqs->disabilitas);
            })
            ->whereNull('sdm_tgl_berhenti')
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->latest('sdm_tgl_gabung')->orderBy('penempatan_no_absen', 'desc');
                }
            );

        if ($reqs->unduh == 'excel') {
            return $berkas->unduhIndexPenempatanSDMAktif($cari, $app);
        }

        $kunciUrut = array_filter((array) $urutArray);

        $urutAbsen = $str->contains($uruts, 'sdm_no_absen');
        $indexAbsen = (head(array_keys($kunciUrut, 'sdm_no_absen ASC')) + head(array_keys($kunciUrut, 'sdm_no_absen DESC')) + 1);
        $urutMasuk = $str->contains($uruts, 'sdm_tgl_gabung');
        $indexMasuk = (head(array_keys($kunciUrut, 'sdm_tgl_gabung ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_gabung DESC')) + 1);
        $urutLahir = $str->contains($uruts, 'sdm_tgl_lahir');
        $indexLahir = (head(array_keys($kunciUrut, 'sdm_tgl_lahir ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_lahir DESC')) + 1);
        $urutKeluar = $str->contains($uruts, 'sdm_tgl_berhenti');
        $indexKeluar = (head(array_keys($kunciUrut, 'sdm_tgl_berhenti ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_berhenti DESC')) + 1);

        $tabels = $cari->clone()->paginate($reqs->bph ?: 100)->withQueryString()->appends(['fragment' => 'riwa-penem-sdm_tabels']);

        $aturs = $fungsiStatis->ambilCacheAtur();
        $posisis = $fungsiStatis->ambilCachePosisiSDM();

        $data = [
            'tabels' => $tabels,
            'penempatans' => $aturs->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin);
            })->sortBy(['atur_butir', 'asc']),
            'kontraks' => $aturs->where('atur_jenis', 'STATUS KONTRAK')->sortBy(['atur_butir', 'asc']),
            'kategoris' => $aturs->where('atur_jenis', 'KATEGORI')->sortBy(['atur_butir', 'asc']),
            'pangkats' => $aturs->where('atur_jenis', 'PANGKAT')->sortBy(['atur_butir', 'asc']),
            'kelamins' => $aturs->where('atur_jenis', 'KELAMIN')->sortBy(['atur_butir', 'asc']),
            'agamas' => $aturs->where('atur_jenis', 'AGAMA')->sortBy(['atur_butir', 'asc']),
            'kawins' => $aturs->where('atur_jenis', 'STATUS MENIKAH')->sortBy(['atur_butir', 'asc']),
            'pendidikans' => $aturs->where('atur_jenis', 'PENDIDIKAN')->sortBy(['atur_butir', 'asc']),
            'warganegaras' => $aturs->where('atur_jenis', 'NEGARA')->sortBy(['atur_butir', 'asc']),
            'disabilitases' => $aturs->where('atur_jenis', 'DISABILITAS')->sortBy(['atur_butir', 'asc']),
            'posisis' => $posisis,
            'urutAbsen' => $urutAbsen,
            'indexAbsen' => $indexAbsen,
            'urutMasuk' => $urutMasuk,
            'indexMasuk' => $indexMasuk,
            'urutLahir' => $urutLahir,
            'indexLahir' => $indexLahir,
            'urutKeluar' => $urutKeluar,
            'indexKeluar' => $indexKeluar,
        ];

        if (!isset($uuid)) {
            $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);
        }

        $HtmlPenuh = $app->view->make('sdm.penempatan.riwayat', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
            ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function indexNonAktif(FungsiStatis $fungsiStatis, Berkas $berkas)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $validator = $app->validator->make(
            $reqs->all(),
            $this->dasarValidasiPencarian(),
            $this->atributInputPencarian(),
        );

        if ($validator->fails()) {
            return $app->redirect->route('sdm.penempatan.data-nonaktif')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $urutArray = $reqs->urut;
        $uruts = $urutArray ? implode(',', array_filter($urutArray)) : null;
        $kataKunci = $reqs->kata_kunci;
        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $cari = $this->dataPenempatanTerkini()->clone()->addSelect('sdm_uuid', 'sdm_no_absen', 'sdm_no_permintaan', 'sdm_tempat_lahir', 'sdm_tgl_lahir', 'sdm_tgl_gabung', 'sdm_no_ktp', 'sdm_nama', 'sdm_kelamin', 'sdm_tgl_berhenti', 'sdm_jenis_berhenti', 'sdm_ket_berhenti', 'posisi_wlkp', 'sdm_disabilitas', 'sdm_ket_kary', 'sdm_alamat', 'sdm_alamat_rt', 'sdm_alamat_rw', 'sdm_alamat_kelurahan', 'sdm_alamat_kecamatan', 'sdm_alamat_kota', 'sdm_alamat_provinsi', 'sdm_alamat_kodepos', 'sdm_agama', 'sdm_status_kawin', 'sdm_pendidikan', 'sdm_warganegara', 'sdm_uk_seragam', 'sdm_uk_sepatu', 'sdm_jurusan', 'sdm_telepon', 'email', 'sdm_id_atasan', 'sdm_no_bpjs', 'sdm_no_jamsostek', 'sdm_jml_anak', $app->db->raw('IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, sdm_tgl_berhenti)) as masa_kerja, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, penempatan_mulai, NOW()),TIMESTAMPDIFF(YEAR, penempatan_mulai, sdm_tgl_berhenti)) as masa_aktif, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, sdm_tgl_berhenti)) as usia'))
            ->joinSub($this->dataSDM(), 'sdm', function ($join) {
                $join->on('penempatan_no_absen', '=', 'sdm.sdm_no_absen');
            })
            ->leftJoinSub($this->dataPosisi(), 'pos', function ($join) {
                $join->on('penempatan_posisi', '=', 'pos.posisi_nama');
            })
            ->when($kataKunci, function ($query, $kataKunci) {
                $query->where(function ($group) use ($kataKunci) {
                    $group->where('sdm_no_absen', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_no_absen', 'like', '%' . $kataKunci . '%')
                        ->orWhere('sdm_no_permintaan', $kataKunci)
                        ->orWhere('sdm_no_ktp', 'like', '%' . $kataKunci . '%')
                        ->orWhere('sdm_nama', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_posisi', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_keterangan', 'like', '%' . $kataKunci . '%');
                });
            })
            ->when($reqs->kontrak, function ($query)  use ($reqs) {
                $query->whereIn('penempatan_kontrak', (array) $reqs->kontrak);
            })
            ->when($lingkupIjin, function ($query, $lingkupIjin) {
                $query->whereIn('penempatan_lokasi', $lingkupIjin);
            })
            ->when($reqs->lokasi, function ($query) use ($reqs) {
                $query->whereIn('penempatan_lokasi', (array) $reqs->lokasi);
            })
            ->when($reqs->kategori, function ($query) use ($reqs) {
                $query->whereIn('penempatan_kategori', (array) $reqs->kategori);
            })
            ->when($reqs->pangkat, function ($query) use ($reqs) {
                $query->whereIn('penempatan_pangkat', (array) $reqs->pangkat);
            })
            ->when($reqs->kelamin, function ($query) use ($reqs) {
                $query->where('sdm_kelamin', $reqs->kelamin);
            })
            ->when($reqs->posisi, function ($query) use ($reqs) {
                $query->whereIn('penempatan_posisi', (array) $reqs->posisi);
            })
            ->when($reqs->agama, function ($query)  use ($reqs) {
                $query->whereIn('sdm_agama', (array) $reqs->agama);
            })
            ->when($reqs->kawin, function ($query)  use ($reqs) {
                $query->whereIn('sdm_status_kawin', (array) $reqs->kawin);
            })
            ->when($reqs->warganegara, function ($query)  use ($reqs) {
                $query->whereIn('sdm_warganegara', (array) $reqs->warganegara);
            })
            ->when($reqs->pendidikan, function ($query)  use ($reqs) {
                $query->whereIn('sdm_pendidikan', (array) $reqs->pendidikan);
            })
            ->when($reqs->disabilitas, function ($query)  use ($reqs) {
                $query->whereIn('sdm_disabilitas', (array) $reqs->disabilitas);
            })
            ->whereNotNull('sdm_tgl_berhenti')
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->latest('sdm_tgl_berhenti')->orderBy('penempatan_no_absen', 'desc');
                }
            );

        if ($reqs->unduh == 'excel') {
            return $berkas->unduhIndexPenempatanSDMNonAktif($cari, $app);
        }

        $kunciUrut = array_filter((array) $urutArray);

        $urutAbsen = $str->contains($uruts, 'sdm_no_absen');
        $indexAbsen = (head(array_keys($kunciUrut, 'sdm_no_absen ASC')) + head(array_keys($kunciUrut, 'sdm_no_absen DESC')) + 1);
        $urutMasuk = $str->contains($uruts, 'sdm_tgl_gabung');
        $indexMasuk = (head(array_keys($kunciUrut, 'sdm_tgl_gabung ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_gabung DESC')) + 1);
        $urutLahir = $str->contains($uruts, 'sdm_tgl_lahir');
        $indexLahir = (head(array_keys($kunciUrut, 'sdm_tgl_lahir ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_lahir DESC')) + 1);
        $urutKeluar = $str->contains($uruts, 'sdm_tgl_berhenti');
        $indexKeluar = (head(array_keys($kunciUrut, 'sdm_tgl_berhenti ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_berhenti DESC')) + 1);

        $tabels = $cari->clone()->paginate($reqs->bph ?: 100)->withQueryString()->appends(['fragment' => 'riwa-penem-sdm_tabels']);

        $aturs = $fungsiStatis->ambilCacheAtur();
        $posisis = $fungsiStatis->ambilCachePosisiSDM();

        $data = [
            'tabels' => $tabels,
            'penempatans' => $aturs->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin);
            })->sortBy(['atur_butir', 'asc']),
            'kontraks' => $aturs->where('atur_jenis', 'STATUS KONTRAK')->sortBy(['atur_butir', 'asc']),
            'kategoris' => $aturs->where('atur_jenis', 'KATEGORI')->sortBy(['atur_butir', 'asc']),
            'pangkats' => $aturs->where('atur_jenis', 'PANGKAT')->sortBy(['atur_butir', 'asc']),
            'kelamins' => $aturs->where('atur_jenis', 'KELAMIN')->sortBy(['atur_butir', 'asc']),
            'agamas' => $aturs->where('atur_jenis', 'AGAMA')->sortBy(['atur_butir', 'asc']),
            'kawins' => $aturs->where('atur_jenis', 'STATUS MENIKAH')->sortBy(['atur_butir', 'asc']),
            'pendidikans' => $aturs->where('atur_jenis', 'PENDIDIKAN')->sortBy(['atur_butir', 'asc']),
            'warganegaras' => $aturs->where('atur_jenis', 'NEGARA')->sortBy(['atur_butir', 'asc']),
            'disabilitases' => $aturs->where('atur_jenis', 'DISABILITAS')->sortBy(['atur_butir', 'asc']),
            'posisis' => $posisis,
            'urutAbsen' => $urutAbsen,
            'indexAbsen' => $indexAbsen,
            'urutMasuk' => $urutMasuk,
            'indexMasuk' => $indexMasuk,
            'urutLahir' => $urutLahir,
            'indexLahir' => $indexLahir,
            'urutKeluar' => $urutKeluar,
            'indexKeluar' => $indexKeluar,
        ];

        if (!isset($uuid)) {
            $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);
        }

        $HtmlPenuh = $app->view->make('sdm.penempatan.riwayat', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
            ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function indexAkanHabis(FungsiStatis $fungsiStatis, Berkas $berkas)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $validator = $app->validator->make(
            $reqs->all(),
            $this->dasarValidasiPencarian(),
            $this->atributInputPencarian(),
        );

        if ($validator->fails()) {
            return $app->redirect->route('sdm.penempatan.data-akanhabis')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $urutArray = $reqs->urut;
        $uruts = $urutArray ? implode(',', array_filter($urutArray)) : null;
        $kataKunci = $reqs->kata_kunci;
        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));
        $date = $app->date;

        $cari = $this->dataPenempatanTerkini()->clone()->addSelect('sdm_uuid', 'sdm_no_absen', 'sdm_no_permintaan', 'sdm_tempat_lahir', 'sdm_tgl_lahir', 'sdm_tgl_gabung', 'sdm_no_ktp', 'sdm_nama', 'sdm_kelamin', 'sdm_tgl_berhenti', 'sdm_jenis_berhenti', 'sdm_ket_berhenti', 'posisi_wlkp', 'sdm_disabilitas', 'sdm_agama', 'sdm_status_kawin', 'sdm_pendidikan', 'sdm_warganegara', 'sdm_uk_seragam', 'sdm_uk_sepatu', 'sdm_jurusan', 'sdm_telepon', 'email', 'sdm_id_atasan', 'sdm_no_bpjs', 'sdm_no_jamsostek', 'sdm_jml_anak', $app->db->raw('IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, sdm_tgl_berhenti)) as masa_kerja, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, penempatan_mulai, NOW()),TIMESTAMPDIFF(YEAR, penempatan_mulai, sdm_tgl_berhenti)) as masa_aktif, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, sdm_tgl_berhenti)) as usia'))
            ->joinSub($this->dataSDM(), 'sdm', function ($join) {
                $join->on('penempatan_no_absen', '=', 'sdm.sdm_no_absen');
            })
            ->leftJoinSub($this->dataPosisi(), 'pos', function ($join) {
                $join->on('penempatan_posisi', '=', 'pos.posisi_nama');
            })
            ->where('penempatan_kontrak', 'not like', 'OS-%')
            ->when($kataKunci, function ($query, $kataKunci) {
                $query->where(function ($group) use ($kataKunci) {
                    $group->where('sdm_no_absen', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_no_absen', 'like', '%' . $kataKunci . '%')
                        ->orWhere('sdm_no_permintaan', $kataKunci)
                        ->orWhere('sdm_no_ktp', 'like', '%' . $kataKunci . '%')
                        ->orWhere('sdm_nama', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_posisi', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_lokasi', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_kontrak', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_kategori', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_keterangan', 'like', '%' . $kataKunci . '%');
                });
            })
            ->when($reqs->kontrak, function ($query)  use ($reqs) {
                $query->whereIn('penempatan_kontrak', (array) $reqs->kontrak);
            })
            ->when($lingkupIjin, function ($query, $lingkupIjin) {
                $query->whereIn('penempatan_lokasi', $lingkupIjin);
            })
            ->when($reqs->lokasi, function ($query) use ($reqs) {
                $query->whereIn('penempatan_lokasi', (array) $reqs->lokasi);
            })
            ->when($reqs->kategori, function ($query) use ($reqs) {
                $query->whereIn('penempatan_kategori', (array) $reqs->kategori);
            })
            ->when($reqs->pangkat, function ($query) use ($reqs) {
                $query->whereIn('penempatan_pangkat', (array) $reqs->pangkat);
            })
            ->when($reqs->kelamin, function ($query) use ($reqs) {
                $query->where('sdm_kelamin', $reqs->kelamin);
            })
            ->when($reqs->posisi, function ($query) use ($reqs) {
                $query->whereIn('penempatan_posisi', (array) $reqs->posisi);
            })
            ->when($reqs->agama, function ($query)  use ($reqs) {
                $query->whereIn('sdm_agama', (array) $reqs->agama);
            })
            ->when($reqs->kawin, function ($query)  use ($reqs) {
                $query->whereIn('sdm_status_kawin', (array) $reqs->kawin);
            })
            ->when($reqs->warganegara, function ($query)  use ($reqs) {
                $query->whereIn('sdm_warganegara', (array) $reqs->warganegara);
            })
            ->when($reqs->pendidikan, function ($query)  use ($reqs) {
                $query->whereIn('sdm_pendidikan', (array) $reqs->pendidikan);
            })
            ->when($reqs->disabilitas, function ($query)  use ($reqs) {
                $query->whereIn('sdm_disabilitas', (array) $reqs->disabilitas);
            })
            ->whereNull('sdm_tgl_berhenti')
            ->whereBetween('penempatan_selesai', [$date->today()->toDateString(), $date->today()->addDays(40)->toDateString()])
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->latest('penempatan_selesai')->orderBy('penempatan_no_absen', 'desc');
                }
            );

        if ($reqs->unduh == 'excel') {
            return $berkas->unduhIndexPenempatanSDMAkanHabis($cari, $app);
        }

        $kunciUrut = array_filter((array) $urutArray);

        $urutAbsen = $str->contains($uruts, 'sdm_no_absen');
        $indexAbsen = (head(array_keys($kunciUrut, 'sdm_no_absen ASC')) + head(array_keys($kunciUrut, 'sdm_no_absen DESC')) + 1);
        $urutMasuk = $str->contains($uruts, 'sdm_tgl_gabung');
        $indexMasuk = (head(array_keys($kunciUrut, 'sdm_tgl_gabung ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_gabung DESC')) + 1);
        $urutLahir = $str->contains($uruts, 'sdm_tgl_lahir');
        $indexLahir = (head(array_keys($kunciUrut, 'sdm_tgl_lahir ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_lahir DESC')) + 1);
        $urutKeluar = $str->contains($uruts, 'sdm_tgl_berhenti');
        $indexKeluar = (head(array_keys($kunciUrut, 'sdm_tgl_berhenti ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_berhenti DESC')) + 1);

        $tabels = $cari->clone()->paginate($reqs->bph ?: 100)->withQueryString()->appends(['fragment' => 'riwa-penem-sdm_tabels']);

        $aturs = $fungsiStatis->ambilCacheAtur();
        $posisis = $fungsiStatis->ambilCachePosisiSDM();

        $data = [
            'tabels' => $tabels,
            'penempatans' => $aturs->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin);
            })->sortBy(['atur_butir', 'asc']),
            'kontraks' => $aturs->where('atur_jenis', 'STATUS KONTRAK')->sortBy(['atur_butir', 'asc']),
            'kategoris' => $aturs->where('atur_jenis', 'KATEGORI')->sortBy(['atur_butir', 'asc']),
            'pangkats' => $aturs->where('atur_jenis', 'PANGKAT')->sortBy(['atur_butir', 'asc']),
            'kelamins' => $aturs->where('atur_jenis', 'KELAMIN')->sortBy(['atur_butir', 'asc']),
            'agamas' => $aturs->where('atur_jenis', 'AGAMA')->sortBy(['atur_butir', 'asc']),
            'kawins' => $aturs->where('atur_jenis', 'STATUS MENIKAH')->sortBy(['atur_butir', 'asc']),
            'pendidikans' => $aturs->where('atur_jenis', 'PENDIDIKAN')->sortBy(['atur_butir', 'asc']),
            'warganegaras' => $aturs->where('atur_jenis', 'NEGARA')->sortBy(['atur_butir', 'asc']),
            'disabilitases' => $aturs->where('atur_jenis', 'DISABILITAS')->sortBy(['atur_butir', 'asc']),
            'posisis' => $posisis,
            'urutAbsen' => $urutAbsen,
            'indexAbsen' => $indexAbsen,
            'urutMasuk' => $urutMasuk,
            'indexMasuk' => $indexMasuk,
            'urutLahir' => $urutLahir,
            'indexLahir' => $indexLahir,
            'urutKeluar' => $urutKeluar,
            'indexKeluar' => $indexKeluar,
        ];

        if (!isset($uuid)) {
            $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);
        }

        $HtmlPenuh = $app->view->make('sdm.penempatan.riwayat', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
            ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function indexKadaluarsa(FungsiStatis $fungsiStatis, Berkas $berkas)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $validator = $app->validator->make(
            $reqs->all(),
            $this->dasarValidasiPencarian(),
            $this->atributInputPencarian(),
        );

        if ($validator->fails()) {
            return $app->redirect->route('sdm.penempatan.data-kadaluarsa')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $urutArray = $reqs->urut;
        $uruts = $urutArray ? implode(',', array_filter($urutArray)) : null;
        $kataKunci = $reqs->kata_kunci;
        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));
        $date = $app->date;

        $cari = $this->dataPenempatanTerkini()->clone()->addSelect('sdm_uuid', 'sdm_no_absen', 'sdm_no_permintaan', 'sdm_tempat_lahir', 'sdm_tgl_lahir', 'sdm_tgl_gabung', 'sdm_no_ktp', 'sdm_nama', 'sdm_kelamin', 'sdm_tgl_berhenti', 'sdm_jenis_berhenti', 'sdm_ket_berhenti', 'posisi_wlkp', 'sdm_disabilitas', 'sdm_agama', 'sdm_status_kawin', 'sdm_pendidikan', 'sdm_warganegara', 'sdm_uk_seragam', 'sdm_uk_sepatu', 'sdm_jurusan', 'sdm_telepon', 'email', 'sdm_id_atasan', 'sdm_no_bpjs', 'sdm_no_jamsostek', 'sdm_jml_anak', $app->db->raw('IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, sdm_tgl_berhenti)) as masa_kerja, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, penempatan_mulai, NOW()),TIMESTAMPDIFF(YEAR, penempatan_mulai, sdm_tgl_berhenti)) as masa_aktif, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, sdm_tgl_berhenti)) as usia'))
            ->joinSub($this->dataSDM(), 'sdm', function ($join) {
                $join->on('penempatan_no_absen', '=', 'sdm.sdm_no_absen');
            })
            ->leftJoinSub($this->dataPosisi(), 'pos', function ($join) {
                $join->on('penempatan_posisi', '=', 'pos.posisi_nama');
            })
            ->where('penempatan_kontrak', 'not like', 'OS-%')
            ->when($kataKunci, function ($query, $kataKunci) {
                $query->where(function ($group) use ($kataKunci) {
                    $group->where('sdm_no_absen', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_no_absen', 'like', '%' . $kataKunci . '%')
                        ->orWhere('sdm_no_permintaan', $kataKunci)
                        ->orWhere('sdm_no_ktp', 'like', '%' . $kataKunci . '%')
                        ->orWhere('sdm_nama', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_posisi', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_keterangan', 'like', '%' . $kataKunci . '%');
                });
            })
            ->when($reqs->kontrak, function ($query)  use ($reqs) {
                $query->whereIn('penempatan_kontrak', (array) $reqs->kontrak);
            })
            ->when($lingkupIjin, function ($query, $lingkupIjin) {
                $query->whereIn('penempatan_lokasi', $lingkupIjin);
            })
            ->when($reqs->lokasi, function ($query) use ($reqs) {
                $query->whereIn('penempatan_lokasi', (array) $reqs->lokasi);
            })
            ->when($reqs->kategori, function ($query) use ($reqs) {
                $query->whereIn('penempatan_kategori', (array) $reqs->kategori);
            })
            ->when($reqs->pangkat, function ($query) use ($reqs) {
                $query->whereIn('penempatan_pangkat', (array) $reqs->pangkat);
            })
            ->when($reqs->kelamin, function ($query) use ($reqs) {
                $query->where('sdm_kelamin', $reqs->kelamin);
            })
            ->when($reqs->posisi, function ($query) use ($reqs) {
                $query->whereIn('penempatan_posisi', (array) $reqs->posisi);
            })
            ->when($reqs->agama, function ($query)  use ($reqs) {
                $query->whereIn('sdm_agama', (array) $reqs->agama);
            })
            ->when($reqs->kawin, function ($query)  use ($reqs) {
                $query->whereIn('sdm_status_kawin', (array) $reqs->kawin);
            })
            ->when($reqs->warganegara, function ($query)  use ($reqs) {
                $query->whereIn('sdm_warganegara', (array) $reqs->warganegara);
            })
            ->when($reqs->pendidikan, function ($query)  use ($reqs) {
                $query->whereIn('sdm_pendidikan', (array) $reqs->pendidikan);
            })
            ->when($reqs->disabilitas, function ($query)  use ($reqs) {
                $query->whereIn('sdm_disabilitas', (array) $reqs->disabilitas);
            })
            ->whereNull('sdm_tgl_berhenti')
            ->where('penempatan_selesai', '<=', $date->today()->toDateString())
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->latest('penempatan_selesai')->orderBy('penempatan_no_absen', 'desc');
                }
            );

        if ($reqs->unduh == 'excel') {
            return $berkas->unduhIndexPenempatanSDMKadaluarsa($cari, $app);
        }

        $kunciUrut = array_filter((array) $urutArray);

        $urutAbsen = $str->contains($uruts, 'sdm_no_absen');
        $indexAbsen = (head(array_keys($kunciUrut, 'sdm_no_absen ASC')) + head(array_keys($kunciUrut, 'sdm_no_absen DESC')) + 1);
        $urutMasuk = $str->contains($uruts, 'sdm_tgl_gabung');
        $indexMasuk = (head(array_keys($kunciUrut, 'sdm_tgl_gabung ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_gabung DESC')) + 1);
        $urutLahir = $str->contains($uruts, 'sdm_tgl_lahir');
        $indexLahir = (head(array_keys($kunciUrut, 'sdm_tgl_lahir ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_lahir DESC')) + 1);
        $urutKeluar = $str->contains($uruts, 'sdm_tgl_berhenti');
        $indexKeluar = (head(array_keys($kunciUrut, 'sdm_tgl_berhenti ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_berhenti DESC')) + 1);

        $tabels = $cari->clone()->paginate($reqs->bph ?: 100)->withQueryString()->appends(['fragment' => 'riwa-penem-sdm_tabels']);

        $aturs = $fungsiStatis->ambilCacheAtur();
        $posisis = $fungsiStatis->ambilCachePosisiSDM();

        $data = [
            'tabels' => $tabels,
            'penempatans' => $aturs->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin);
            })->sortBy(['atur_butir', 'asc']),
            'kontraks' => $aturs->where('atur_jenis', 'STATUS KONTRAK')->sortBy(['atur_butir', 'asc']),
            'kategoris' => $aturs->where('atur_jenis', 'KATEGORI')->sortBy(['atur_butir', 'asc']),
            'pangkats' => $aturs->where('atur_jenis', 'PANGKAT')->sortBy(['atur_butir', 'asc']),
            'kelamins' => $aturs->where('atur_jenis', 'KELAMIN')->sortBy(['atur_butir', 'asc']),
            'agamas' => $aturs->where('atur_jenis', 'AGAMA')->sortBy(['atur_butir', 'asc']),
            'kawins' => $aturs->where('atur_jenis', 'STATUS MENIKAH')->sortBy(['atur_butir', 'asc']),
            'pendidikans' => $aturs->where('atur_jenis', 'PENDIDIKAN')->sortBy(['atur_butir', 'asc']),
            'warganegaras' => $aturs->where('atur_jenis', 'NEGARA')->sortBy(['atur_butir', 'asc']),
            'disabilitases' => $aturs->where('atur_jenis', 'DISABILITAS')->sortBy(['atur_butir', 'asc']),
            'posisis' => $posisis,
            'urutAbsen' => $urutAbsen,
            'indexAbsen' => $indexAbsen,
            'urutMasuk' => $urutMasuk,
            'indexMasuk' => $indexMasuk,
            'urutLahir' => $urutLahir,
            'indexLahir' => $indexLahir,
            'urutKeluar' => $urutKeluar,
            'indexKeluar' => $indexKeluar,
        ];

        if (!isset($uuid)) {
            $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);
        }

        $HtmlPenuh = $app->view->make('sdm.penempatan.riwayat', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
            ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function indexBaru(FungsiStatis $fungsiStatis, Berkas $berkas)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $validator = $app->validator->make(
            $reqs->all(),
            $this->dasarValidasiPencarian(),
            $this->atributInputPencarian(),
        );

        if ($validator->fails()) {
            return $app->redirect->route('sdm.penempatan.data-baru')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $urutArray = $reqs->urut;
        $uruts = $urutArray ? implode(',', array_filter($urutArray)) : null;
        $kataKunci = $reqs->kata_kunci;
        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $cari = $this->dataSDM()->clone()->addSelect('penempatan_uuid', 'penempatan_no_absen', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_lokasi', 'penempatan_posisi', 'penempatan_kategori', 'penempatan_kontrak', 'penempatan_pangkat', 'penempatan_golongan', 'penempatan_grup', 'penempatan_keterangan', 'posisi_wlkp', $app->db->raw('IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, sdm_tgl_berhenti)) as masa_kerja, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, sdm_tgl_berhenti)) as masa_aktif, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, sdm_tgl_berhenti)) as usia'))
            ->leftJoinSub($this->dataPenempatanTerkini(), 'penem', function ($join) {
                $join->on('sdm_no_absen', '=', 'penem.penempatan_no_absen');
            })
            ->leftJoinSub($this->dataPosisi(), 'pos', function ($join) {
                $join->on('penempatan_posisi', '=', 'pos.posisi_nama');
            })
            ->when($kataKunci, function ($query, $kataKunci) {
                $query->where(function ($group) use ($kataKunci) {
                    $group->where('sdm_no_absen', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_no_absen', 'like', '%' . $kataKunci . '%')
                        ->orWhere('sdm_no_permintaan', $kataKunci)
                        ->orWhere('sdm_no_ktp', 'like', '%' . $kataKunci . '%')
                        ->orWhere('sdm_nama', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_posisi', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_keterangan', 'like', '%' . $kataKunci . '%');
                });
            })
            ->when($reqs->kelamin, function ($query) use ($reqs) {
                $query->where('sdm_kelamin', $reqs->kelamin);
            })
            ->when($reqs->agama, function ($query)  use ($reqs) {
                $query->whereIn('sdm_agama', (array) $reqs->agama);
            })
            ->when($reqs->kawin, function ($query)  use ($reqs) {
                $query->whereIn('sdm_status_kawin', (array) $reqs->kawin);
            })
            ->when($reqs->warganegara, function ($query)  use ($reqs) {
                $query->whereIn('sdm_warganegara', (array) $reqs->warganegara);
            })
            ->when($reqs->pendidikan, function ($query)  use ($reqs) {
                $query->whereIn('sdm_pendidikan', (array) $reqs->pendidikan);
            })
            ->when($reqs->disabilitas, function ($query)  use ($reqs) {
                $query->whereIn('sdm_disabilitas', (array) $reqs->disabilitas);
            })
            ->whereNull('penempatan_lokasi')
            ->whereNull('sdm_tgl_berhenti')
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->latest('sdm_tgl_gabung')->orderBy('sdm_no_absen', 'desc');
                }
            );

        if ($reqs->unduh == 'excel') {
            return $berkas->unduhIndexPenempatanSDMBaru($cari, $app);
        }

        $kunciUrut = array_filter((array) $urutArray);

        $urutAbsen = $str->contains($uruts, 'sdm_no_absen');
        $indexAbsen = (head(array_keys($kunciUrut, 'sdm_no_absen ASC')) + head(array_keys($kunciUrut, 'sdm_no_absen DESC')) + 1);
        $urutMasuk = $str->contains($uruts, 'sdm_tgl_gabung');
        $indexMasuk = (head(array_keys($kunciUrut, 'sdm_tgl_gabung ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_gabung DESC')) + 1);
        $urutLahir = $str->contains($uruts, 'sdm_tgl_lahir');
        $indexLahir = (head(array_keys($kunciUrut, 'sdm_tgl_lahir ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_lahir DESC')) + 1);
        $urutKeluar = $str->contains($uruts, 'sdm_tgl_berhenti');
        $indexKeluar = (head(array_keys($kunciUrut, 'sdm_tgl_berhenti ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_berhenti DESC')) + 1);

        $tabels = $cari->clone()->paginate($reqs->bph ?: 100)->withQueryString()->appends(['fragment' => 'riwa-penem-sdm_tabels']);

        $aturs = $fungsiStatis->ambilCacheAtur();
        $posisis = $fungsiStatis->ambilCachePosisiSDM();

        $data = [
            'tabels' => $tabels,
            'penempatans' => $aturs->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin);
            })->sortBy(['atur_butir', 'asc']),
            'kontraks' => $aturs->where('atur_jenis', 'STATUS KONTRAK')->sortBy(['atur_butir', 'asc']),
            'kategoris' => $aturs->where('atur_jenis', 'KATEGORI')->sortBy(['atur_butir', 'asc']),
            'pangkats' => $aturs->where('atur_jenis', 'PANGKAT')->sortBy(['atur_butir', 'asc']),
            'kelamins' => $aturs->where('atur_jenis', 'KELAMIN')->sortBy(['atur_butir', 'asc']),
            'agamas' => $aturs->where('atur_jenis', 'AGAMA')->sortBy(['atur_butir', 'asc']),
            'kawins' => $aturs->where('atur_jenis', 'STATUS MENIKAH')->sortBy(['atur_butir', 'asc']),
            'pendidikans' => $aturs->where('atur_jenis', 'PENDIDIKAN')->sortBy(['atur_butir', 'asc']),
            'warganegaras' => $aturs->where('atur_jenis', 'NEGARA')->sortBy(['atur_butir', 'asc']),
            'disabilitases' => $aturs->where('atur_jenis', 'DISABILITAS')->sortBy(['atur_butir', 'asc']),
            'posisis' => $posisis,
            'urutAbsen' => $urutAbsen,
            'indexAbsen' => $indexAbsen,
            'urutMasuk' => $urutMasuk,
            'indexMasuk' => $indexMasuk,
            'urutLahir' => $urutLahir,
            'indexLahir' => $indexLahir,
            'urutKeluar' => $urutKeluar,
            'indexKeluar' => $indexKeluar,
        ];

        if (!isset($uuid)) {
            $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);
        }

        $HtmlPenuh = $app->view->make('sdm.penempatan.riwayat', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
            ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function indexBatal(FungsiStatis $fungsiStatis, Berkas $berkas)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $validator = $app->validator->make(
            $reqs->all(),
            $this->dasarValidasiPencarian(),
            $this->atributInputPencarian(),
        );

        if ($validator->fails()) {
            return $app->redirect->route('sdm.penempatan.data-batal')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $urutArray = $reqs->urut;
        $uruts = $urutArray ? implode(',', array_filter($urutArray)) : null;
        $kataKunci = $reqs->kata_kunci;
        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $cari = $this->dataSDM()->clone()->addSelect('penempatan_uuid', 'penempatan_no_absen', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_lokasi', 'penempatan_posisi', 'penempatan_kategori', 'penempatan_kontrak', 'penempatan_pangkat', 'penempatan_golongan', 'penempatan_grup', 'penempatan_keterangan', 'posisi_wlkp', $app->db->raw('IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, sdm_tgl_berhenti)) as masa_kerja, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, sdm_tgl_berhenti)) as masa_aktif, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, sdm_tgl_berhenti)) as usia'))
            ->leftJoinSub($this->dataPenempatanTerkini(), 'penem', function ($join) {
                $join->on('sdm_no_absen', '=', 'penem.penempatan_no_absen');
            })
            ->leftJoinSub($this->dataPosisi(), 'pos', function ($join) {
                $join->on('penempatan_posisi', '=', 'pos.posisi_nama');
            })
            ->when($kataKunci, function ($query, $kataKunci) {
                $query->where(function ($group) use ($kataKunci) {
                    $group->where('sdm_no_absen', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_no_absen', 'like', '%' . $kataKunci . '%')
                        ->orWhere('sdm_no_permintaan', $kataKunci)
                        ->orWhere('sdm_no_ktp', 'like', '%' . $kataKunci . '%')
                        ->orWhere('sdm_nama', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_posisi', 'like', '%' . $kataKunci . '%')
                        ->orWhere('penempatan_keterangan', 'like', '%' . $kataKunci . '%');
                });
            })
            ->when($reqs->kelamin, function ($query) use ($reqs) {
                $query->where('sdm_kelamin', $reqs->kelamin);
            })
            ->when($reqs->agama, function ($query)  use ($reqs) {
                $query->whereIn('sdm_agama', (array) $reqs->agama);
            })
            ->when($reqs->kawin, function ($query)  use ($reqs) {
                $query->whereIn('sdm_status_kawin', (array) $reqs->kawin);
            })
            ->when($reqs->warganegara, function ($query)  use ($reqs) {
                $query->whereIn('sdm_warganegara', (array) $reqs->warganegara);
            })
            ->when($reqs->pendidikan, function ($query)  use ($reqs) {
                $query->whereIn('sdm_pendidikan', (array) $reqs->pendidikan);
            })
            ->when($reqs->disabilitas, function ($query)  use ($reqs) {
                $query->whereIn('sdm_disabilitas', (array) $reqs->disabilitas);
            })
            ->whereNull('penempatan_lokasi')
            ->whereNotNull('sdm_tgl_berhenti')
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->latest('sdm_tgl_berhenti')->orderBy('sdm_no_absen', 'desc');
                }
            );

        if ($reqs->unduh == 'excel') {
            return $berkas->unduhIndexPenempatanSDMBatal($cari, $app);
        }

        $kunciUrut = array_filter((array) $urutArray);

        $urutAbsen = $str->contains($uruts, 'sdm_no_absen');
        $indexAbsen = (head(array_keys($kunciUrut, 'sdm_no_absen ASC')) + head(array_keys($kunciUrut, 'sdm_no_absen DESC')) + 1);
        $urutMasuk = $str->contains($uruts, 'sdm_tgl_gabung');
        $indexMasuk = (head(array_keys($kunciUrut, 'sdm_tgl_gabung ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_gabung DESC')) + 1);
        $urutLahir = $str->contains($uruts, 'sdm_tgl_lahir');
        $indexLahir = (head(array_keys($kunciUrut, 'sdm_tgl_lahir ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_lahir DESC')) + 1);
        $urutKeluar = $str->contains($uruts, 'sdm_tgl_berhenti');
        $indexKeluar = (head(array_keys($kunciUrut, 'sdm_tgl_berhenti ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_berhenti DESC')) + 1);

        $tabels = $cari->clone()->paginate($reqs->bph ?: 100)->withQueryString()->appends(['fragment' => 'riwa-penem-sdm_tabels']);

        $aturs = $fungsiStatis->ambilCacheAtur();
        $posisis = $fungsiStatis->ambilCachePosisiSDM();

        $data = [
            'tabels' => $tabels,
            'penempatans' => $aturs->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin);
            })->sortBy(['atur_butir', 'asc']),
            'kontraks' => $aturs->where('atur_jenis', 'STATUS KONTRAK')->sortBy(['atur_butir', 'asc']),
            'kategoris' => $aturs->where('atur_jenis', 'KATEGORI')->sortBy(['atur_butir', 'asc']),
            'pangkats' => $aturs->where('atur_jenis', 'PANGKAT')->sortBy(['atur_butir', 'asc']),
            'kelamins' => $aturs->where('atur_jenis', 'KELAMIN')->sortBy(['atur_butir', 'asc']),
            'agamas' => $aturs->where('atur_jenis', 'AGAMA')->sortBy(['atur_butir', 'asc']),
            'kawins' => $aturs->where('atur_jenis', 'STATUS MENIKAH')->sortBy(['atur_butir', 'asc']),
            'pendidikans' => $aturs->where('atur_jenis', 'PENDIDIKAN')->sortBy(['atur_butir', 'asc']),
            'warganegaras' => $aturs->where('atur_jenis', 'NEGARA')->sortBy(['atur_butir', 'asc']),
            'disabilitases' => $aturs->where('atur_jenis', 'DISABILITAS')->sortBy(['atur_butir', 'asc']),
            'posisis' => $posisis,
            'urutAbsen' => $urutAbsen,
            'indexAbsen' => $indexAbsen,
            'urutMasuk' => $urutMasuk,
            'indexMasuk' => $indexMasuk,
            'urutLahir' => $urutLahir,
            'indexLahir' => $indexLahir,
            'urutKeluar' => $urutKeluar,
            'indexKeluar' => $indexKeluar,
        ];

        if (!isset($uuid)) {
            $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);
        }

        $HtmlPenuh = $app->view->make('sdm.penempatan.riwayat', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
            ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function atributInput()
    {
        return [
            'penempatan_selesai' => 'Tanggal Selesai Penempatan',
            'penempatan_ke' => 'Penempatan Ke',
            'penempatan_lokasi' => 'Lokasi Penempatan',
            'penempatan_posisi' => 'Jabatan Penempatan',
            'penempatan_kategori' => 'Kategori Penempatan',
            'penempatan_kontrak' => 'Status Penempatan',
            'penempatan_pangkat' => 'Pangkat Penempatan',
            'penempatan_golongan' => 'Golongan Penempatan',
            'penempatan_grup' => 'Grup Penempatan',
            'penempatan_keterangan' => 'Keterangan Penempatan',
            'penempatan_id_pengunggah' => 'ID Pengunggah Penempatan',
            'penempatan_id_pembuat' => 'ID Pembuat Penempatan',
            'penempatan_id_pengubah' => 'ID Pengubah Penempatan',
            'penempatan_berkas' => 'Berkas Penempatan',
        ];
    }

    public function atributInputPencarian()
    {
        return [
            'lokasi.*' => 'Lokasi harus berupa karakter dan maksimal 40 karakter.',
            'kontrak.*' => 'Kontrak harus berupa karakter dan maksimal 40 karakter.',
            'kategori.*' => 'Kategori harus berupa karakter dan maksimal 40 karakter.',
            'pangkat.*' => 'Pangkat harus berupa karakter dan maksimal 40 karakter.',
            'kelamin.*' => 'Kelamin harus berupa karakter dan maksimal 40 karakter.',
            'agama.*' => 'Agama harus berupa karakter dan maksimal 40 karakter.',
            'kawin.*' => 'Status Kawin harus berupa karakter dan maksimal 40 karakter.',
            'pendidikan.*' => 'Pendidikan harus berupa karakter dan maksimal 40 karakter.',
            'warganegara.*' => 'Warganegara harus berupa karakter dan maksimal 40 karakter.',
            'disabilitas.*' => 'Disabilitas harus berupa karakter dan maksimal 40 karakter.',
            'posisi.*' => 'Jabatan harus berupa karakter.',
            'unduh.*' => 'Format ekspor tidak dikenali.',
            'kata_kunci.*' => 'Kata Kunci Pencarian harus berupa karakter.',
            'bph.*' => 'Baris Per halaman tidak sesuai daftar.',
        ];
    }

    public function dataDasar()
    {
        $database = app('db');
        return $database->query()->select('penempatan_uuid', 'penempatan_no_absen', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_lokasi', 'penempatan_posisi', 'penempatan_kategori', 'penempatan_kontrak', 'penempatan_pangkat', 'penempatan_golongan', 'penempatan_grup', 'penempatan_keterangan')
            ->from('penempatans');
    }

    public function dataPosisi()
    {
        $database = app('db');
        return $database->query()->select('posisi_nama', 'posisi_wlkp')->from('posisis');
    }

    public function dataPenempatanTerkini()
    {
        $database = app('db');
        return $database->query()->select('penempatan_uuid', 'penempatan_no_absen', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_lokasi', 'penempatan_posisi', 'penempatan_kategori', 'penempatan_kontrak', 'penempatan_pangkat', 'penempatan_golongan', 'penempatan_grup', 'penempatan_keterangan')
            ->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
                $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
            });
    }

    public function dataPermintaanTambahSDM()
    {
        $database = app('db');
        return $database->query()->select('tambahsdm_no', 'tambahsdm_penempatan')->from('tambahsdms');
    }

    public function dataSDM()
    {
        $database = app('db');
        return $database->query()->select('id', 'sdm_uuid', 'sdm_no_absen', 'sdm_no_permintaan', 'sdm_tgl_lahir', 'sdm_tempat_lahir', 'sdm_tgl_gabung', 'sdm_no_ktp', 'sdm_nama', 'sdm_kelamin', 'sdm_tgl_berhenti', 'sdm_jenis_berhenti', 'sdm_ket_kary', 'sdm_ket_berhenti', 'sdm_alamat', 'sdm_alamat_rt', 'sdm_alamat_rw', 'sdm_alamat_kelurahan', 'sdm_alamat_kecamatan', 'sdm_alamat_kota', 'sdm_alamat_provinsi', 'sdm_alamat_kodepos', 'sdm_disabilitas', 'sdm_agama', 'sdm_status_kawin', 'sdm_pendidikan', 'sdm_warganegara', 'sdm_uk_seragam', 'sdm_uk_sepatu', 'sdm_jurusan', 'sdm_telepon', 'email', 'sdm_id_atasan', 'sdm_no_bpjs', 'sdm_no_jamsostek', 'sdm_jml_anak')
            ->from('sdms');
    }

    public function dataSDMKTPTerlama()
    {
        $database = app('db');
        return $database->query()->select('sdm_no_permintaan', 's1.sdm_tgl_gabung', 's1.sdm_no_ktp',)
            ->from('sdms as s1')->where('sdm_tgl_gabung', '=', function ($query) use ($database) {
                $query->select($database->raw('MIN(sdm_tgl_gabung)'))->from('sdms as s2')->whereColumn('s1.sdm_no_ktp', 's2.sdm_no_ktp');
            })->groupBy('sdm_no_ktp');
    }

    public function dataSDMKTPTerbaru()
    {
        $database = app('db');
        return $database->query()->select('s3.sdm_uuid as uuid', 's3.sdm_no_absen as no_absen', 's3.sdm_tgl_lahir as tgl_lahir', 's3.sdm_tempat_lahir as tempat_lahir', 's3.sdm_no_ktp as no_ktp', 's3.sdm_nama as nama', 's3.sdm_kelamin as kelamin', 's3.sdm_tgl_berhenti as tgl_berhenti', 's3.sdm_jenis_berhenti as jenis_berhenti', 's3.sdm_ket_berhenti as ket_berhenti', 's3.sdm_disabilitas as disabilitas', 's3.sdm_agama as agama', 's3.sdm_status_kawin as status_kawin', 's3.sdm_pendidikan as pendidikan', 's3.sdm_warganegara as warganegara', 's3.sdm_uk_seragam as uk_seragam', 's3.sdm_uk_sepatu as uk_sepatu', 's3.sdm_jurusan as jurusan', 's3.sdm_telepon as telepon', 's3.email as sdm_email', 's3.sdm_id_atasan as id_atasan', 's3.sdm_no_bpjs as no_bpjs', 's3.sdm_no_jamsostek as no_jamsostek', 's3.sdm_jml_anak as jml_anak')
            ->from('sdms as s3')->where('s3.sdm_tgl_gabung', '=', function ($query) use ($database) {
                $query->select($database->raw('MAX(s4.sdm_tgl_gabung)'))->from('sdms as s4')->whereColumn('s3.sdm_no_ktp', 's4.sdm_no_ktp');
            });
    }

    public function dasarValidasi()
    {
        $rule = app('Illuminate\Validation\Rule');
        return [
            'penempatan_no_absen' => ['required', 'string', 'exists:sdms,sdm_no_absen'],
            'penempatan_selesai' => ['nullable', $rule->requiredIf(in_array(request('penempatan_kontrak'), ['PKWT', 'PERCOBAAN'])), 'date', 'after:penempatan_mulai'],
            'penempatan_ke' => ['nullable', $rule->requiredIf(in_array(request('penempatan_kontrak'), ['PKWT', 'PERCOBAAN'])), 'numeric', 'min:0'],
            'penempatan_lokasi' => ['required', 'string', 'max:40', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                return $query->where('atur_jenis', 'PENEMPATAN');
            })],
            'penempatan_posisi' => ['required', 'string', 'max:40', 'exists:posisis,posisi_nama'],
            'penempatan_kategori' => ['required', 'string', 'max:40', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                return $query->where('atur_jenis', 'KATEGORI');
            })],
            'penempatan_kontrak' => ['required', 'string', 'max:40', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                return $query->where('atur_jenis', 'STATUS KONTRAK');
            })],
            'penempatan_pangkat' => ['required', 'string', 'max:40', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                return $query->where('atur_jenis', 'PANGKAT');
            })],
            'penempatan_golongan' => ['required', 'string', 'max:40', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                return $query->where('atur_jenis', 'GOLONGAN');
            })],
            'penempatan_grup' => ['nullable', 'string', 'max:40'],
            'penempatan_keterangan' => ['nullable', 'string'],
            'penempatan_berkas' => ['sometimes', 'file', 'mimetypes:application/pdf'],
        ];
    }

    public function dasarValidasiPencarian()
    {
        $rule = app('Illuminate\Validation\Rule');
        return [
            'lokasi.*' => ['sometimes', 'nullable', 'string', 'max:40'],
            'kontrak.*' => ['sometimes', 'nullable', 'string', 'max:40'],
            'kategori.*' => ['sometimes', 'nullable', 'string', 'max:40'],
            'pangkat.*' => ['sometimes', 'nullable', 'string', 'max:40'],
            'kelamin.*' => ['sometimes', 'nullable', 'string', 'max:40'],
            'agama.*' => ['sometimes', 'nullable', 'string', 'max:40'],
            'kawin.*' => ['sometimes', 'nullable', 'string', 'max:40'],
            'pendidikan.*' => ['sometimes', 'nullable', 'string', 'max:40'],
            'warganegara.*' => ['sometimes', 'nullable', 'string', 'max:40'],
            'disabilitas.*' => ['sometimes', 'nullable', 'string', 'max:40'],
            'posisi.*' => ['sometimes', 'nullable', 'string'],
            'unduh' => ['sometimes', 'nullable', 'string', $rule->in(['excel'])],
            'kata_kunci' => ['sometimes', 'nullable', 'string'],
            'bph' => ['sometimes', 'nullable', 'numeric', $rule->in([100, 250, 500, 1000])],
        ];
    }

    public function lihat($uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $uuid && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $database = $app->db;

        $dasar = $database->query()->select('a.sdm_uuid', 'a.sdm_no_absen', 'a.sdm_nama', 'a.sdm_tgl_gabung', 'a.sdm_tgl_berhenti')->from('sdms', 'a');

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkupIjin = array_filter(explode(',', $ijin_akses));

        $penem = $database->query()->select('sdm_no_absen', 'sdm_nama', 'sdm_tgl_gabung', 'sdm_tgl_berhenti', 'penempatan_uuid', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_lokasi', 'penempatan_posisi', 'penempatan_kategori', 'penempatan_kontrak', 'penempatan_pangkat', 'penempatan_golongan', 'penempatan_grup', 'penempatan_keterangan')
            ->from('penempatans')->joinSub($dasar, 'dasar', function ($join) {
                $join->on('penempatan_no_absen', '=', 'dasar.sdm_no_absen');
            })->when($lingkupIjin, function ($query, $lingkupIjin) {
                $query->whereIn('penempatan_lokasi', $lingkupIjin);
            })
            ->where('penempatan_uuid', $uuid)->first();

        $lingkup_lokasi = collect($penem?->penempatan_lokasi);
        $lingkup_akses = $lingkup_lokasi->unique()->intersect($lingkupIjin)->count();

        abort_unless($penem && (blank($ijin_akses) || $lingkup_lokasi->isEmpty() || ($lingkup_akses > 0) || ($pengguna->sdm_no_absen == $penem?->sdm_no_absen)), 403, 'Akses pengguna dibatasi.');

        $HtmlPenuh = $app->view->make('sdm.penempatan.lihat', compact('penem'));
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }

    public function tambah(FungsiStatis $fungsiStatis, $uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $uuid && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $database = $app->db;

        $dasar = $database->query()->select('penempatan_no_absen', 'penempatan_lokasi')->from('penempatans');

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkupIjin = array_filter(explode(',', $ijin_akses));

        $penem = $database->query()->select('sdm_uuid', 'sdm_no_absen', 'sdm_nama', 'penempatan_lokasi')
            ->from('sdms')->leftJoinSub($dasar, 'dasar', function ($join) {
                $join->on('sdm_no_absen', '=', 'dasar.penempatan_no_absen');
            })->when($lingkupIjin, function ($query, $lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->orWhereIn('penempatan_lokasi', $lingkupIjin)
                        ->orWhereNull('penempatan_lokasi');
                });
            })
            ->where('sdm_uuid', $uuid)->first();

        $lingkup_lokasi = collect($penem?->penempatan_lokasi);
        $lingkup_akses = $lingkup_lokasi->unique()->intersect($lingkupIjin)->count();

        abort_unless($penem && ($pengguna->sdm_no_absen !== $penem?->sdm_no_absen) && (blank($ijin_akses) || $lingkup_lokasi->isEmpty() || ($lingkup_akses > 0)), 403, 'Akses pengguna dibatasi.');

        if ($reqs->isMethod('post')) {
            $reqs->merge(['penempatan_id_pembuat' => $pengguna->sdm_no_absen]);
            $validasi = $app->validator->make(
                $reqs->all(),
                [
                    'penempatan_mulai' => ['required', 'date', Rule::unique('penempatans')->where(function ($query) use ($reqs) {
                        $query->where('penempatan_no_absen', $reqs->penempatan_no_absen);
                    })],
                    'penempatan_id_pembuat' => ['nullable', 'string', 'exists:sdms,sdm_no_absen'],
                    ...$this->dasarValidasi()
                ],
                [],
                $this->atributInput()
            );

            $validasi->validate();

            $data = $validasi->safe()->except('penempatan_berkas');

            $database->table('penempatans')->insert($data);

            $berkas = $validasi->safe()->only('penempatan_berkas')['penempatan_berkas'] ?? false;

            if ($berkas) {
                $berkas->storeAs('sdm/penempatan/berkas', $validasi->safe()->only('penempatan_no_absen')['penempatan_no_absen'] . ' - ' . $validasi->safe()->only('penempatan_mulai')['penempatan_mulai'] . '.pdf');
            }

            $fungsiStatis->hapusCacheSDMUmum();

            $perujuk = $reqs->session()->get('tautan_perujuk');
            $pesan = $fungsiStatis->statusBerhasil();
            $redirect = $app->redirect;

            return $perujuk ? $redirect->to($perujuk)->with('pesan', $pesan) : $redirect->route('sdm.penempatan.riwayat')->with('pesan', $pesan);
        }

        $aturs = $fungsiStatis->ambilCacheAtur();
        $posisis = $fungsiStatis->ambilCachePosisiSDM();
        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $data = [
            'penempatans' => $aturs->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin);
            })->sortBy(['atur_butir', 'asc']),
            'kontraks' => $aturs->where('atur_jenis', 'STATUS KONTRAK')->sortBy(['atur_butir', 'asc']),
            'kategoris' => $aturs->where('atur_jenis', 'KATEGORI')->sortBy(['atur_butir', 'asc']),
            'pangkats' => $aturs->where('atur_jenis', 'PANGKAT')->sortBy(['atur_butir', 'asc']),
            'golongans' => $aturs->where('atur_jenis', 'GOLONGAN')->sortBy(['atur_butir', 'asc']),
            'posisis' => $posisis,
            'penem' => $penem
        ];

        $HtmlPenuh = $app->view->make('sdm.penempatan.tambah-ubah', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }

    public function ubah(FungsiStatis $fungsiStatis, $uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $uuid && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $database = $app->db;

        $dasar = $database->query()->select('a.sdm_uuid', 'a.sdm_no_absen', 'a.sdm_nama')->from('sdms', 'a');

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkupIjin = array_filter(explode(',', $ijin_akses));

        $penem = $database->query()->select('sdm_no_absen', 'sdm_nama', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_lokasi', 'penempatan_posisi', 'penempatan_kategori', 'penempatan_kontrak', 'penempatan_pangkat', 'penempatan_golongan', 'penempatan_grup', 'penempatan_keterangan')
            ->from('penempatans')->joinSub($dasar, 'dasar', function ($join) {
                $join->on('penempatan_no_absen', '=', 'dasar.sdm_no_absen');
            })->when($lingkupIjin, function ($query, $lingkupIjin) {
                $query->whereIn('penempatan_lokasi', $lingkupIjin);
            })
            ->where('penempatan_uuid', $uuid)->first();

        $lingkup_lokasi = collect($penem?->penempatan_lokasi);
        $lingkup_akses = $lingkup_lokasi->unique()->intersect($lingkupIjin)->count();

        abort_unless($penem && ($pengguna->sdm_no_absen !== $penem?->sdm_no_absen) && (blank($ijin_akses) || $lingkup_lokasi->isEmpty() || ($lingkup_akses > 0)), 403, 'Akses pengguna dibatasi.');

        if ($reqs->isMethod('post')) {
            $reqs->merge(['penempatan_id_pengubah' => $pengguna->sdm_no_absen]);
            $validasi = $app->validator->make(
                $reqs->all(),
                [
                    'penempatan_mulai' => ['required', 'date', Rule::unique('penempatans')->where(function ($query) use ($reqs, $uuid) {
                        $query->where('penempatan_no_absen', $reqs->penempatan_no_absen)->whereNot('penempatan_uuid', $uuid);
                    })],
                    'penempatan_id_pengubah' => ['nullable', 'string', 'exists:sdms,sdm_no_absen'],
                    ...$this->dasarValidasi()
                ],
                [],
                $this->atributInput()
            );

            $validasi->validate();

            $data = $validasi->safe()->except('penempatan_berkas');

            $database->table('penempatans')->where('penempatan_uuid', $uuid)->update($data);

            $berkas = $validasi->safe()->only('penempatan_berkas')['penempatan_berkas'] ?? false;

            if ($berkas) {
                $berkas->storeAs('sdm/penempatan/berkas', $validasi->safe()->only('penempatan_no_absen')['penempatan_no_absen'] . ' - ' . $validasi->safe()->only('penempatan_mulai')['penempatan_mulai'] . '.pdf');
            }

            $fungsiStatis->hapusCacheSDMUmum();

            $perujuk = $reqs->session()->get('tautan_perujuk');
            $pesan = $fungsiStatis->statusBerhasil();
            $redirect = $app->redirect;

            return $perujuk ? $redirect->to($perujuk)->with('pesan', $pesan) : $redirect->route('sdm.penempatan.riwayat')->with('pesan', $pesan);
        }

        $aturs = $fungsiStatis->ambilCacheAtur();
        $posisis = $fungsiStatis->ambilCachePosisiSDM();

        $data = [
            'penempatans' => $aturs->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin);
            })->sortBy(['atur_butir', 'asc']),
            'kontraks' => $aturs->where('atur_jenis', 'STATUS KONTRAK')->sortBy(['atur_butir', 'asc']),
            'kategoris' => $aturs->where('atur_jenis', 'KATEGORI')->sortBy(['atur_butir', 'asc']),
            'pangkats' => $aturs->where('atur_jenis', 'PANGKAT')->sortBy(['atur_butir', 'asc']),
            'golongans' => $aturs->where('atur_jenis', 'GOLONGAN')->sortBy(['atur_butir', 'asc']),
            'posisis' => $posisis,
            'penem' => $penem
        ];

        $HtmlPenuh = $app->view->make('sdm.penempatan.tambah-ubah', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }

    public function hapus(FungsiStatis $fungsiStatis, Berkas $berkas, $uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $uuid && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $database = $app->db;

        $dasar = $database->query()->select('a.sdm_uuid', 'a.sdm_no_absen', 'a.sdm_nama')->from('sdms', 'a');

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkupIjin = array_filter(explode(',', $ijin_akses));

        $penem = $database->query()->select('sdm_no_absen', 'sdm_nama', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_lokasi', 'penempatan_posisi', 'penempatan_kategori', 'penempatan_kontrak', 'penempatan_pangkat', 'penempatan_golongan', 'penempatan_grup', 'penempatan_keterangan')
            ->from('penempatans')->joinSub($dasar, 'dasar', function ($join) {
                $join->on('penempatan_no_absen', '=', 'dasar.sdm_no_absen');
            })->when($lingkupIjin, function ($query, $lingkupIjin) {
                $query->whereIn('penempatan_lokasi', $lingkupIjin);
            })
            ->where('penempatan_uuid', $uuid)->first();

        $lingkup_lokasi = collect($penem?->penempatan_lokasi);
        $lingkup_akses = $lingkup_lokasi->unique()->intersect($lingkupIjin)->count();

        abort_unless($penem && ($pengguna->sdm_no_absen !== $penem?->sdm_no_absen) && (blank($ijin_akses) || $lingkup_lokasi->isEmpty() || ($lingkup_akses > 0)), 403, 'Akses pengguna dibatasi.');

        if ($reqs->isMethod('post')) {
            $reqs->merge(['id_penghapus' => $pengguna->sdm_no_absen, 'waktu_dihapus' => $app->date->now()]);
            $validasi = $app->validator->make(
                $reqs->all(),
                [
                    'alasan' => ['required', 'string'],
                    'id_penghapus' => ['required', 'string'],
                    'waktu_dihapus' => ['required', 'date'],
                ],
                [],
                [
                    'alasan' => 'Alasan Penghapusan',
                    'id_penghapus' => 'ID Penghapus',
                    'waktu_dihapus' => 'Waktu Dihapus',
                ]
            );

            $validasi->validate();

            abort_unless($app->filesystem->exists('contoh/data-dihapus.xlsx'), 404, 'Berkas riwayat penghapusan tidak ditemukan.');

            $dataValid = $validasi->validated();

            $jenisHapus = 'Penempatan SDM';
            $idHapus = $dataValid['id_penghapus'];
            $alasanHapus = $dataValid['alasan'];
            $waktuHapus = $dataValid['waktu_dihapus']->format('Y-m-d H:i:s');
            $hapus = collect($penem)->toJson();

            $dataHapus = [
                $jenisHapus, $hapus, $idHapus, $waktuHapus, $alasanHapus
            ];

            $berkas->rekamHapusDataSDM($app, $dataHapus);

            $database->table('penempatans')->where('penempatan_uuid', $uuid)->delete();

            $fungsiStatis->hapusCacheSDMUmum();

            $perujuk = $reqs->session()->get('tautan_perujuk');
            $pesan = 'Data berhasil dihapus';
            $redirect = $app->redirect;

            return $perujuk ? $redirect->to($perujuk)->with('pesan', $pesan) : $redirect->route('sdm.penempatan.riwayat')->with('pesan', $pesan);
        }

        $data = [
            'penem' => $penem
        ];

        $HtmlPenuh = $app->view->make('sdm.penempatan.hapus', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }
}
