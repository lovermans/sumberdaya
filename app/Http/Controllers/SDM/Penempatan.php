<?php

namespace App\Http\Controllers\SDM;

use App\Tambahan\FungsiStatis;
use Illuminate\Validation\Rule;
use App\Tambahan\ChunkReadFilter;
use App\Tambahan\CustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class Penempatan
{
    public function index(FungsiStatis $fungsiStatis)
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
        ->when($reqs->kontrak, function ($query)  use ($reqs) {
            $query->whereIn('penempatan_kontrak', (array) $reqs->kontrak);
        })
        ->when($lingkupIjin, function ($query, $lingkupIjin) {
            $query->where(function ($group) use ($lingkupIjin)  {
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
            abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');
            
            set_time_limit(0);
            ob_implicit_flush();
            ob_end_flush();
            header('X-Accel-Buffering: no');
            
            $spreadsheet = new Spreadsheet();
            $filename = 'eksporriwayatpenempatan-' . date('YmdHis') . '.xlsx';
            Cell::setValueBinder(new CustomValueBinder());
            $worksheet = $spreadsheet->getActiveSheet();
            $x = 1;
            
            $cari->clone()->chunk(500, function ($hasil) use (&$x, $worksheet) {
                if ($x == 1) {
                    $list = $hasil->map(function ($x) {
                        return collect($x)->except(['id', 'sdm_uuid', 'penempatan_uuid']);
                    })->toArray();
                    array_unshift($list, array_keys($list[0]));
                    $worksheet->fromArray($list, NULL, 'A' . $x);
                    $x++;
                } else {
                    $list = $hasil->map(function ($x) {
                        return collect($x)->except(['id', 'sdm_uuid', 'penempatan_uuid']);
                    })->toArray();
                    $worksheet->fromArray($list, NULL, 'A' . $x);
                };
                $x += count($hasil);
                echo '<p>Status : Memproses ' . ($x - 2) . ' data riwayat penempatan SDM.</p>';
            });
            
            echo '<p>Status : Menyiapkan berkas excel.</p>';
            
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->setPreCalculateFormulas(false);
            $writer->save($app->storagePath("app/unduh/{$filename}"));
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            
            echo '<p>Selesai menyiapkan berkas excel. <a href="' . $app->filesystem->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)) . '">Unduh</a>.</p>';
            
            exit();
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

        $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);

        $HtmlPenuh = $app->view->make('sdm.penempatan.riwayat', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
        ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']) 
        : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function indexMasaKerjaNyata(FungsiStatis $fungsiStatis)
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
        ->joinSub($this->dataSDMKTPTerbaru(), 'sdmbaru',function ($join) {
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
                $group->where('no_absen', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_no_absen', 'like', '%'.$kataKunci.'%')
                ->orWhere('sdm_no_permintaan', $kataKunci)
                ->orWhere('no_ktp', 'like', '%'.$kataKunci.'%')
                ->orWhere('nama', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_posisi', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_keterangan', 'like', '%'.$kataKunci.'%');
            });
        })
        ->when($reqs->kontrak, function ($query)  use ($reqs) {
            $query->whereIn('penempatan_kontrak', (array) $reqs->kontrak);
        })
        ->when($lingkupIjin, function ($query, $lingkupIjin) {
            $query->where(function ($group) use ($lingkupIjin)  {
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
            abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

            set_time_limit(0);
            ob_implicit_flush();
            ob_end_flush();
            header('X-Accel-Buffering: no');
            
            $spreadsheet = new Spreadsheet();
            $filename = 'ekspormasakerjanyata-' . date('YmdHis') . '.xlsx';
            Cell::setValueBinder(new CustomValueBinder());
            $worksheet = $spreadsheet->getActiveSheet();
            $x = 1;
            
            $cari->clone()->chunk(500, function ($hasil) use (&$x, $worksheet) {
                if ($x == 1) {
                    $list = $hasil->map(function ($x) {
                        return collect($x)->except(['id', 'sdm_uuid', 'penempatan_uuid']);
                    })->toArray();
                    array_unshift($list, array_keys($list[0]));
                    $worksheet->fromArray($list, NULL, 'A' . $x);
                    $x++;
                } else {
                    $list = $hasil->map(function ($x) {
                        return collect($x)->except(['id', 'sdm_uuid', 'penempatan_uuid']);
                    })->toArray();
                    $worksheet->fromArray($list, NULL, 'A' . $x);
                };
                $x += count($hasil);
                echo '<p>Status : Memproses ' . ($x - 2) . ' data riwayat penempatan SDM.</p>';
            });
            
            echo '<p>Status : Menyiapkan berkas excel.</p>';
            
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->setPreCalculateFormulas(false);
            $writer->save($app->storagePath("app/unduh/{$filename}"));
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            
            echo '<p>Selesai menyiapkan berkas excel. <a href="' . $app->filesystem->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)) . '">Unduh</a>.</p>';
            
            exit();
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

        $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);

        $HtmlPenuh = $app->view->make('sdm.penempatan.riwayat', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
        ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']) 
        : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function indexAktif(FungsiStatis $fungsiStatis)
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
                $group->where('sdm_no_absen', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_no_absen', 'like', '%'.$kataKunci.'%')
                ->orWhere('sdm_no_permintaan', $kataKunci)
                ->orWhere('sdm_no_ktp', 'like', '%'.$kataKunci.'%')
                ->orWhere('sdm_nama', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_posisi', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_keterangan', 'like', '%'.$kataKunci.'%');
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
            abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');
            
            set_time_limit(0);
            ob_implicit_flush();
            ob_end_flush();
            header('X-Accel-Buffering: no');
            
            $spreadsheet = new Spreadsheet();
            $filename = 'eksporsdmaktif-' . date('YmdHis') . '.xlsx';
            Cell::setValueBinder(new CustomValueBinder());
            $worksheet = $spreadsheet->getActiveSheet();
            $x = 1;
            
            $cari->clone()->chunk(500, function ($hasil) use (&$x, $worksheet) {
                if ($x == 1) {
                    $list = $hasil->map(function ($x) {
                        return collect($x)->except(['id', 'sdm_uuid', 'penempatan_uuid']);
                    })->toArray();
                    array_unshift($list, array_keys($list[0]));
                    $worksheet->fromArray($list, NULL, 'A' . $x);
                    $x++;
                } else {
                    $list = $hasil->map(function ($x) {
                        return collect($x)->except(['id', 'sdm_uuid', 'penempatan_uuid']);
                    })->toArray();
                    $worksheet->fromArray($list, NULL, 'A' . $x);
                };
                $x += count($hasil);
                echo '<p>Status : Memproses ' . ($x - 2) . ' data SDM Aktif.</p>';
            });
            
            echo '<p>Status : Menyiapkan berkas excel.</p>';
            
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->setPreCalculateFormulas(false);
            $writer->save($app->storagePath("app/unduh/{$filename}"));
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            
            echo '<p>Selesai menyiapkan berkas excel. <a href="' . $app->filesystem->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)) . '">Unduh</a>.</p>';
            
            exit();
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

        $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);

        $HtmlPenuh = $app->view->make('sdm.penempatan.riwayat', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
        ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']) 
        : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function indexNonAktif(FungsiStatis $fungsiStatis)
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
                $group->where('sdm_no_absen', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_no_absen', 'like', '%'.$kataKunci.'%')
                ->orWhere('sdm_no_permintaan', $kataKunci)
                ->orWhere('sdm_no_ktp', 'like', '%'.$kataKunci.'%')
                ->orWhere('sdm_nama', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_posisi', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_keterangan', 'like', '%'.$kataKunci.'%');
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
            abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

            set_time_limit(0);
            ob_implicit_flush();
            ob_end_flush();
            header('X-Accel-Buffering: no');
            
            $spreadsheet = new Spreadsheet();
            $filename = 'eksporsdmnonaktif-' . date('YmdHis') . '.xlsx';
            Cell::setValueBinder(new CustomValueBinder());
            $worksheet = $spreadsheet->getActiveSheet();
            $x = 1;
            
            $cari->clone()->chunk(500, function ($hasil) use (&$x, $worksheet) {
                if ($x == 1) {
                    $list = $hasil->map(function ($x) {
                        return collect($x)->except(['id', 'sdm_uuid', 'penempatan_uuid']);
                    })->toArray();
                    array_unshift($list, array_keys($list[0]));
                    $worksheet->fromArray($list, NULL, 'A' . $x);
                    $x++;
                } else {
                    $list = $hasil->map(function ($x) {
                        return collect($x)->except(['id', 'sdm_uuid', 'penempatan_uuid']);
                    })->toArray();
                    $worksheet->fromArray($list, NULL, 'A' . $x);
                };
                $x += count($hasil);
                echo '<p>Status : Memproses ' . ($x - 2) . ' data SDM Non-Aktif.</p>';
            });
            
            echo '<p>Status : Menyiapkan berkas excel.</p>';
            
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->setPreCalculateFormulas(false);
            $writer->save($app->storagePath("app/unduh/{$filename}"));
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            
            echo '<p>Selesai menyiapkan berkas excel. <a href="' . $app->filesystem->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)) . '">Unduh</a>.</p>';
            
            exit();
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
        
        $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);
        
        $HtmlPenuh = $app->view->make('sdm.penempatan.riwayat', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
        ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']) 
        : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function indexAkanHabis(FungsiStatis $fungsiStatis)
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
                $group->where('sdm_no_absen', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_no_absen', 'like', '%'.$kataKunci.'%')
                ->orWhere('sdm_no_permintaan', $kataKunci)
                ->orWhere('sdm_no_ktp', 'like', '%'.$kataKunci.'%')
                ->orWhere('sdm_nama', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_posisi', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_lokasi', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_kontrak', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_kategori', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_keterangan', 'like', '%'.$kataKunci.'%');
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
            abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

            set_time_limit(0);
            ob_implicit_flush();
            ob_end_flush();
            header('X-Accel-Buffering: no');
            
            $spreadsheet = new Spreadsheet();
            $filename = 'eksporsdmakanhabis-' . date('YmdHis') . '.xlsx';
            Cell::setValueBinder(new CustomValueBinder());
            $worksheet = $spreadsheet->getActiveSheet();
            $x = 1;
            
            $cari->clone()->chunk(500, function ($hasil) use (&$x, $worksheet) {
                if ($x == 1) {
                    $list = $hasil->map(function ($x) {
                        return collect($x)->except(['id', 'sdm_uuid', 'penempatan_uuid']);
                    })->toArray();
                    array_unshift($list, array_keys($list[0]));
                    $worksheet->fromArray($list, NULL, 'A' . $x);
                    $x++;
                } else {
                    $list = $hasil->map(function ($x) {
                        return collect($x)->except(['id', 'sdm_uuid', 'penempatan_uuid']);
                    })->toArray();
                    $worksheet->fromArray($list, NULL, 'A' . $x);
                };
                $x += count($hasil);
                echo '<p>Status : Memproses ' . ($x - 2) . ' data SDM Akan Habis.</p>';
            });
            
            echo '<p>Status : Menyiapkan berkas excel.</p>';
            
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->setPreCalculateFormulas(false);
            $writer->save($app->storagePath("app/unduh/{$filename}"));
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            
            echo '<p>Selesai menyiapkan berkas excel. <a href="' . $app->filesystem->disk('local')->temporaryUrl("unduh/{$filename}", $date->now()->addMinutes(5)) . '">Unduh</a>.</p>';
            
            exit();
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
        
        $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);
        
        $HtmlPenuh = $app->view->make('sdm.penempatan.riwayat', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
        ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']) 
        : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function indexKadaluarsa(FungsiStatis $fungsiStatis)
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
                $group->where('sdm_no_absen', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_no_absen', 'like', '%'.$kataKunci.'%')
                ->orWhere('sdm_no_permintaan', $kataKunci)
                ->orWhere('sdm_no_ktp', 'like', '%'.$kataKunci.'%')
                ->orWhere('sdm_nama', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_posisi', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_keterangan', 'like', '%'.$kataKunci.'%');
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
            abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

            set_time_limit(0);
            ob_implicit_flush();
            ob_end_flush();
            header('X-Accel-Buffering: no');
            
            $spreadsheet = new Spreadsheet();
            $filename = 'eksporsdmkadaluarsa-' . date('YmdHis') . '.xlsx';
            Cell::setValueBinder(new CustomValueBinder());
            $worksheet = $spreadsheet->getActiveSheet();
            $x = 1;
            
            $cari->clone()->chunk(500, function ($hasil) use (&$x, $worksheet) {
                if ($x == 1) {
                    $list = $hasil->map(function ($x) {
                        return collect($x)->except(['id', 'sdm_uuid', 'penempatan_uuid']);
                    })->toArray();
                    array_unshift($list, array_keys($list[0]));
                    $worksheet->fromArray($list, NULL, 'A' . $x);
                    $x++;
                } else {
                    $list = $hasil->map(function ($x) {
                        return collect($x)->except(['id', 'sdm_uuid', 'penempatan_uuid']);
                    })->toArray();
                    $worksheet->fromArray($list, NULL, 'A' . $x);
                };
                $x += count($hasil);
                echo '<p>Status : Memproses ' . ($x - 2) . ' data SDM Kadaluarsa.</p>';
            });
            
            echo '<p>Status : Menyiapkan berkas excel.</p>';
            
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->setPreCalculateFormulas(false);
            $writer->save($app->storagePath("app/unduh/{$filename}"));
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            
            echo '<p>Selesai menyiapkan berkas excel. <a href="' . $app->filesystem->disk('local')->temporaryUrl("unduh/{$filename}", $date->now()->addMinutes(5)) . '">Unduh</a>.</p>';
            
            exit();
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
        
        $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);
        
        $HtmlPenuh = $app->view->make('sdm.penempatan.riwayat', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
        ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']) 
        : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function indexBaru(FungsiStatis $fungsiStatis)
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
                $group->where('sdm_no_absen', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_no_absen', 'like', '%'.$kataKunci.'%')
                ->orWhere('sdm_no_permintaan', $kataKunci)
                ->orWhere('sdm_no_ktp', 'like', '%'.$kataKunci.'%')
                ->orWhere('sdm_nama', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_posisi', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_keterangan', 'like', '%'.$kataKunci.'%');
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
            abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

            set_time_limit(0);
            ob_implicit_flush();
            ob_end_flush();
            header('X-Accel-Buffering: no');
            
            $spreadsheet = new Spreadsheet();
            $filename = 'eksporsdmbaru-' . date('YmdHis') . '.xlsx';
            Cell::setValueBinder(new CustomValueBinder());
            $worksheet = $spreadsheet->getActiveSheet();
            $x = 1;
            
            $cari->clone()->chunk(500, function ($hasil) use (&$x, $worksheet) {
                if ($x == 1) {
                    $list = $hasil->map(function ($x) {
                        return collect($x)->except(['id', 'sdm_uuid', 'penempatan_uuid']);
                    })->toArray();
                    array_unshift($list, array_keys($list[0]));
                    $worksheet->fromArray($list, NULL, 'A' . $x);
                    $x++;
                } else {
                    $list = $hasil->map(function ($x) {
                        return collect($x)->except(['id', 'sdm_uuid', 'penempatan_uuid']);
                    })->toArray();
                    $worksheet->fromArray($list, NULL, 'A' . $x);
                };
                $x += count($hasil);
                echo '<p>Status : Memproses ' . ($x - 2) . ' data SDM Baru.</p>';
            });
            
            echo '<p>Status : Menyiapkan berkas excel.</p>';
            
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->setPreCalculateFormulas(false);
            $writer->save($app->storagePath("app/unduh/{$filename}"));
            $spreadsheet->disconnectWorksheets();
            
            unset($spreadsheet);
            
            echo '<p>Selesai menyiapkan berkas excel. <a href="' . $app->filesystem->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)) . '">Unduh</a>.</p>';
            
            exit();
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
        
        $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);
        
        $HtmlPenuh = $app->view->make('sdm.penempatan.riwayat', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
        ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']) 
        : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function indexBatal(FungsiStatis $fungsiStatis)
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
                $group->where('sdm_no_absen', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_no_absen', 'like', '%'.$kataKunci.'%')
                ->orWhere('sdm_no_permintaan', $kataKunci)
                ->orWhere('sdm_no_ktp', 'like', '%'.$kataKunci.'%')
                ->orWhere('sdm_nama', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_posisi', 'like', '%'.$kataKunci.'%')
                ->orWhere('penempatan_keterangan', 'like', '%'.$kataKunci.'%');
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
            abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

            set_time_limit(0);
            ob_implicit_flush();
            ob_end_flush();
            header('X-Accel-Buffering: no');
            
            $spreadsheet = new Spreadsheet();
            $filename = 'eksporsdmbatal-' . date('YmdHis') . '.xlsx';
            Cell::setValueBinder(new CustomValueBinder());
            $worksheet = $spreadsheet->getActiveSheet();
            $x = 1;
            
            $cari->clone()->chunk(500, function ($hasil) use (&$x, $worksheet) {
                if ($x == 1) {
                    $list = $hasil->map(function ($x) {
                        return collect($x)->except(['id', 'sdm_uuid', 'penempatan_uuid']);
                    })->toArray();
                    array_unshift($list, array_keys($list[0]));
                    $worksheet->fromArray($list, NULL, 'A' . $x);
                    $x++;
                } else {
                    $list = $hasil->map(function ($x) {
                        return collect($x)->except(['id', 'sdm_uuid', 'penempatan_uuid']);
                    })->toArray();
                    $worksheet->fromArray($list, NULL, 'A' . $x);
                };
                $x += count($hasil);
                echo '<p>Status : Memproses ' . ($x - 2) . ' data SDM Batal.</p>';
            });
            
            echo '<p>Status : Menyiapkan berkas excel.</p>';
            
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->setPreCalculateFormulas(false);
            $writer->save($app->storagePath("app/unduh/{$filename}"));
            $spreadsheet->disconnectWorksheets();
            
            unset($spreadsheet);
            
            echo '<p>Selesai menyiapkan berkas excel. <a href="' . $app->filesystem->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)) . '">Unduh</a>.</p>';
            
            exit();
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
        
        $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);
        
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

    public function berkas($berkas)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        
        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        abort_unless($berkas && $app->filesystem->exists("sdm/penempatan/{$berkas}"), 404, 'Berkas tidak ditemukan.');

        $jalur = $app->storagePath("app/sdm/penempatan/{$berkas}");

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->file($jalur, [
            'Content-Disposition' => 'inline',
            'Content-Type' => $app->files->mimeType($jalur),
        ]);
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
        return $database->query()->select('id','sdm_uuid', 'sdm_no_absen', 'sdm_no_permintaan', 'sdm_tgl_lahir', 'sdm_tempat_lahir', 'sdm_tgl_gabung', 'sdm_no_ktp', 'sdm_nama', 'sdm_kelamin', 'sdm_tgl_berhenti', 'sdm_jenis_berhenti', 'sdm_ket_kary', 'sdm_ket_berhenti', 'sdm_alamat', 'sdm_alamat_rt', 'sdm_alamat_rw', 'sdm_alamat_kelurahan', 'sdm_alamat_kecamatan', 'sdm_alamat_kota', 'sdm_alamat_provinsi', 'sdm_alamat_kodepos', 'sdm_disabilitas', 'sdm_agama', 'sdm_status_kawin', 'sdm_pendidikan', 'sdm_warganegara', 'sdm_uk_seragam', 'sdm_uk_sepatu', 'sdm_jurusan', 'sdm_telepon', 'email', 'sdm_id_atasan', 'sdm_no_bpjs', 'sdm_no_jamsostek', 'sdm_jml_anak')
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

    public function formulirPenilaianSDM($uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();
        
        abort_unless($pengguna && $uuid && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');
        
        echo '<p>Memeriksa formulir.</p>';

        $permin = $this->dataDasar()->clone()->addSelect('sdm_nama')
        ->joinSub($this->dataSDM(), 'sdm', function ($join) {
            $join->on('penempatan_no_absen', '=', 'sdm_no_absen');
        })->where('penempatan_uuid', $uuid)->first();
        
        abort_unless($permin, 404, 'Data Penempatan SDM tidak ditemukan.');

        $storage = $app->filesystem;
        $no_absen = $permin->penempatan_no_absen;
        
        abort_unless($storage->exists("contoh/penilaian-kinerja.docx"), 404, 'Berkas Contoh Formulir Tidak Ditemukan.');
        
        $filename = 'penilaian-kinerja-'.$no_absen.'.docx';
        
        $templateProcessor = new TemplateProcessor($app->storagePath('app/contoh/penilaian-kinerja.docx'));
        
        echo '<p>Menyiapkan formulir.</p>';
        
        $templateProcessor->setValues([
            'sdm_nama' => str($permin->sdm_nama)->limit(30),
            'sdm_no_absen' => $no_absen,
            'sdm_jabatan' => $permin->penempatan_posisi
        ]);

        $templateProcessor->saveAs($app->storagePath("app/unduh/{$filename}"));
        
        echo '<p>Selesai menyiapkan berkas formulir. <a href="' . $storage->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)) . '">Unduh</a>.</p>';
        
        exit();
    }

    public function formulirPerubahanStatusSDM($uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();
        
        abort_unless($pengguna && $uuid && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');
        
        echo '<p>Memeriksa formulir.</p>';

        $permin = $this->dataDasar()->clone()->addSelect('sdm_nama')
        ->joinSub($this->dataSDM(), 'sdm', function ($join) {
            $join->on('penempatan_no_absen', '=', 'sdm_no_absen');
        })->where('penempatan_uuid', $uuid)->first();
        
        abort_unless($permin, 404, 'Data Penempatan SDM tidak ditemukan.');

        $storage = $app->filesystem;
        $no_absen = $permin->penempatan_no_absen;
        
        abort_unless($storage->exists("contoh/perubahan-status-sdm.docx"), 404, 'Berkas Contoh Formulir Tidak Ditemukan.');
        
        $filename = 'perubahan-status-sdm-'.$no_absen.'.docx';
        
        $templateProcessor = new TemplateProcessor($app->storagePath('app/contoh/perubahan-status-sdm.docx'));
        
        echo '<p>Menyiapkan formulir.</p>';
        
        $templateProcessor->setValues([
            'sdm_nama' => str($permin->sdm_nama)->limit(30),
            'sdm_no_absen' => $no_absen,
            'sdm_jabatan' => $permin->penempatan_posisi,
            'sdm_kontrak' => $permin->penempatan_kontrak,
            'sdm_ke' => $permin->penempatan_ke,
            'sdm_golongan' => $permin->penempatan_golongan,
            'sdm_lokasi' => $permin->penempatan_lokasi,
        ]);

        $templateProcessor->saveAs($app->storagePath("app/unduh/{$filename}"));
        
        echo '<p>Selesai menyiapkan berkas formulir. <a href="' . $storage->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)) . '">Unduh</a>.</p>';
        
        exit();
    }

    public function PKWTSDM($uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();
        
        abort_unless($pengguna && $uuid && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');
        
        echo '<p>Memeriksa formulir.</p>';

        $permin = $this->dataDasar()->clone()->addSelect('sdm_nama', 'sdm_tgl_lahir', 'sdm_tempat_lahir', 'sdm_kelamin', 'sdm_alamat', 'sdm_alamat_kelurahan', 'sdm_alamat_kecamatan', 'sdm_alamat_kota', 'sdm_alamat_provinsi')
        ->joinSub($this->dataSDM(), 'sdm', function ($join) {
            $join->on('penempatan_no_absen', '=', 'sdm_no_absen');
        })->where('penempatan_uuid', $uuid)->first();
        
        abort_unless($permin, 404, 'Data Penempatan SDM tidak ditemukan.');

        $storage = $app->filesystem;
        $date = $app->date;
        
        abort_unless($storage->exists("contoh/pkwt.docx"), 404, 'Berkas Contoh Formulir Tidak Ditemukan.');
        
        $filename = 'pkwt-'.$permin->penempatan_no_absen.'.docx';
        
        $templateProcessor = new TemplateProcessor($app->storagePath('app/contoh/pkwt.docx'));
        
        echo '<p>Menyiapkan formulir.</p>';
        
        $templateProcessor->setValues([
            'sdm_nama' => str($permin->sdm_nama)->limit(30),
            'sdm_jabatan' => $permin->penempatan_posisi,
            'sdm_tgl_lahir' => $permin->sdm_tempat_lahir . ', ' . strtoupper($date->make($permin->sdm_tgl_lahir)?->translatedFormat('d F Y')),
            'sdm_kelamin' => $permin->sdm_kelamin == 'L' ? 'LAKI - LAKI' : 'PEREMPUAN',
            'sdm_alamat' => "{$permin->sdm_alamat}, {$permin->sdm_alamat_kelurahan}, {$permin->sdm_alamat_kecamatan}, {$permin->sdm_alamat_kota}, {$permin->sdm_alamat_provinsi}.",
            'sdm_mulai' => strtoupper($date->make($permin->penempatan_mulai)?->translatedFormat('d F Y')),
            'sdm_sampai' => strtoupper($date->make($permin->penempatan_selesai)?->translatedFormat('d F Y')),
        ]);

        $templateProcessor->saveAs($app->storagePath("app/unduh/{$filename}"));
        
        echo '<p>Selesai menyiapkan berkas formulir. <a href="' . $storage->disk('local')->temporaryUrl("unduh/{$filename}", $date->now()->addMinutes(5)) . '">Unduh</a>.</p>';
        
        exit();
    }

    public function statistik()
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();
        
        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');
        
        $reader = IOFactory::createReader('Xlsx');
        $spreadsheet2 = $reader->load($app->storagePath('app/contoh/statistik-sdm.xlsx'));
        $spreadsheet = new Spreadsheet();
        $filename = 'statistik-sdm-' . date('YmdHis') . '.xlsx';
        Cell::setValueBinder(new CustomValueBinder());
        $worksheet = $spreadsheet->getActiveSheet();

        // $rumusMasaKerja = '=IF([@sdm_tgl_berhenti]="",DATEDIF([@sdm_tgl_gabung],TODAY(),"Y"),DATEDIF([@sdm_tgl_gabung],[@sdm_tgl_berhenti],"Y"))';
        // $rumusUsia = '=IF([@sdm_tgl_berhenti]="",DATEDIF([@sdm_tgl_lahir],TODAY(),"Y"),DATEDIF([@sdm_tgl_lahir],[@sdm_tgl_berhenti],"Y"))';

        $x=1;
        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));
        
        $this->dataPenempatanTerkini()->clone()->addSelect('posisi_wlkp', 'sdm_uuid', 'sdm_no_absen', 'sdm_tgl_lahir', 'sdm_tgl_gabung', 'sdm_no_ktp', 'sdm_nama', 'sdm_kelamin', 'sdm_tgl_berhenti', 'sdm_jenis_berhenti', 'sdm_ket_berhenti', 'sdm_kelamin', 'sdm_disabilitas', 'sdm_agama', 'sdm_status_kawin', 'sdm_pendidikan', 'sdm_warganegara', $app->db->raw('IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, sdm_tgl_berhenti)) as masa_kerja, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, sdm_tgl_berhenti)) as usia'))
        ->joinSub($this->dataSDM(), 'sdm', function ($join) {
            $join->on('penempatan_no_absen', '=', 'sdm.sdm_no_absen');
        })
        ->leftJoinSub($this->dataPosisi(), 'pos', function ($join) {
            $join->on('penempatan_posisi', '=', 'pos.posisi_nama');
        })
        ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
            $query->whereIn('penempatan_lokasi', $lingkupIjin);
        })
        ->orderBy('sdm.id')
        ->chunk(500, function ($hasil) use (&$x, $worksheet) {
            if ($x == 1) {
                $list = $hasil->map(function ($x) {
                    return collect($x)->except(['id', 'sdm_uuid', 'penempatan_uuid']);
                })->toArray();
                array_unshift($list, array_keys($list[0]));
                $worksheet->fromArray($list, NULL, 'A' . $x);
                $x++;
            } else {
                $list = $hasil->map(function ($x) {
                    return collect($x)->except(['id', 'sdm_uuid', 'penempatan_uuid']);
                })->toArray();
                $worksheet->fromArray($list, NULL, 'A' . $x);
            };
            $x += count($hasil);
            echo '<p>Status : Memproses ' . ($x - 2) . ' data statistik.</p>';
        });

        echo '<p>Status : Menyiapkan tabel excel.</p>';

        $tabel = new Table('A1:' . $worksheet->getHighestColumn() . $worksheet->getHighestRow(), 'Penempatan');
        $spreadsheet->getActiveSheet()->addTable($tabel);

        echo '<p>Status : Menyiapkan sheet Perhitungan.</p>';

        $clonedWorksheet = clone $spreadsheet2->getSheetByName('Sum');
        $spreadsheet->addExternalSheet($clonedWorksheet);   
        
        echo '<p>Status : Menyiapkan berkas excel.</p>';
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->setPreCalculateFormulas(false);
        $writer->save($app->storagePath("app/unduh/{$filename}"));
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        
        echo '<p>Selesai menyiapkan berkas excel. <a href="' . $app->filesystem->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)) . '">Unduh</a>.</p>';
        
        exit();
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
                $query->where(function ($group) use ($lingkupIjin)  {
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
                    'penempatan_mulai' => ['required', 'date', Rule::unique('penempatans')->where(function ($query) use ($reqs) { $query->where('penempatan_no_absen', $reqs->penempatan_no_absen);})],
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
                $berkas->storeAs('sdm/penempatan', $validasi->safe()->only('penempatan_no_absen')['penempatan_no_absen'] .' - ' . $validasi->safe()->only('penempatan_mulai')['penempatan_mulai'] . '.pdf');
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
                    'penempatan_mulai' => ['required', 'date', Rule::unique('penempatans')->where(function ($query) use ($reqs, $uuid) { $query->where('penempatan_no_absen', $reqs->penempatan_no_absen)->whereNot('penempatan_uuid', $uuid);})],
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
                $berkas->storeAs('sdm/penempatan', $validasi->safe()->only('penempatan_no_absen')['penempatan_no_absen'] .' - ' . $validasi->safe()->only('penempatan_mulai')['penempatan_mulai'] . '.pdf');
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

    public function hapus(FungsiStatis $fungsiStatis, $uuid = null)
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
            
            $reader = IOFactory::createReader('Xlsx');
            $spreadsheet = $reader->load($app->storagePath('app/contoh/data-dihapus.xlsx'));
            Cell::setValueBinder(new CustomValueBinder());
            $worksheet = $spreadsheet->getActiveSheet();
            $barisAkhir = $worksheet->getHighestRow() + 1;
            $worksheet->fromArray($dataHapus, NULL, 'A' . $barisAkhir);

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->setPreCalculateFormulas(false);
            $writer->save($app->storagePath('app/contoh/data-dihapus.xlsx'));
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

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

    public function contohUnggah()
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();
        
        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $storage = $app->filesystem;
        
        abort_unless($storage->exists("contoh/unggah-umum.xlsx"), 404, 'Berkas Contoh Ekspor Tidak Ditemukan.');
        
        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');
        
        $reader = IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($app->storagePath('app/contoh/unggah-umum.xlsx'));
        $filename = 'unggahpenempatansdm-' . date('YmdHis') . '.xlsx';
        Cell::setValueBinder(new StringValueBinder());
        $worksheet = $spreadsheet->getSheet(1);
        $x = 1;

        $database = $app->db;

        $lingkup = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $database->query()->select('sdm_nama', 'penempatan_no_absen', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_lokasi', 'penempatan_posisi', 'penempatan_kategori', 'penempatan_kontrak', 'penempatan_pangkat', 'penempatan_golongan', 'penempatan_grup', 'penempatan_keterangan')
        ->from('penempatans')
        ->join('sdms', 'penempatan_no_absen', '=', 'sdm_no_absen')
        ->whereNull('sdm_tgl_berhenti')
        ->when($lingkup, function ($c) use ($lingkup) {
            return $c->whereIn('penempatan_lokasi', $lingkup);
        })
        ->orderBy('penempatan_no_absen')->latest('penempatan_mulai')->chunk(100, function ($hasil) use (&$x, $worksheet) {
            if ($x == 1) {
                $list = $hasil->map(function ($x) {
                    return collect($x)->except(['id']);
                })->toArray();
                array_unshift($list, array_keys($list[0]));
                $worksheet->fromArray($list, NULL, 'A' . $x);
                $x++;
            } else {
                $list = $hasil->map(function ($x) {
                    return collect($x)->except(['id']);
                })->toArray();
                $worksheet->fromArray($list, NULL, 'A' . $x);
            };
            $x += count($hasil);
            echo '<p>Status : Memproses ' . ($x - 2) . ' data penempatan SDM.</p>';
        });
        
        echo '<p>Status : Menyiapkan berkas excel.</p>';
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->setPreCalculateFormulas(false);
        $writer->save($app->storagePath("app/unduh/{$filename}"));
        $spreadsheet->disconnectWorksheets();
        
        unset($spreadsheet);
        
        echo '<p>Selesai menyiapkan berkas excel. <a href="' . $storage->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)) . '">Unduh</a>.</p>';
        
        exit();
    }

    public function unggah(Rule $rule) {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();
        
        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        if ($reqs->isMethod('post')) {
            set_time_limit(0);
            ob_implicit_flush();
            ob_end_flush();
            header('X-Accel-Buffering: no');

            $validator = $app->validator;
            
            $validasifile = $validator->make(
                $reqs->all(),
                [
                    'unggah_penempatan_sdm' => ['required', 'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
                ],
                [],
                [
                    'unggah_penempatan_sdm' => 'Berkas Yang Diunggah'
                ]
            );
            
            $validasifile->validate();
            $file = $validasifile->safe()->only('unggah_penempatan_sdm')['unggah_penempatan_sdm'];
            $namafile = 'unggahpenempatansdm-' . date('YmdHis') . '.xlsx';
            
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
                
                $dataexcel = array_map(function ($x) use ($idPengunggah) {
                    return $x + ['penempatan_id_pengunggah' => $idPengunggah] + ['penempatan_id_pembuat' => $idPengunggah] + ['penempatan_id_pengubah' => $idPengunggah] + ['penempatan_diunggah' => date('Y-m-d H:i:s')];
                }, $datas);

                $data = array_combine(range(($startRow - 1), count($dataexcel) + ($startRow - 2) ), array_values($dataexcel));
                
                $validasi = $validator->make(
                    $data,
                    [
                        '*.penempatan_mulai' => ['required', 'date'],
                        '*.penempatan_no_absen' => ['required', 'string', 'exists:sdms,sdm_no_absen'],
                        '*.penempatan_selesai' => ['nullable', $rule->requiredIf(in_array(request('penempatan_kontrak'), ['PKWT', 'PERCOBAAN'])), 'date', 'after:penempatan_mulai'],
                        '*.penempatan_ke' => ['nullable', $rule->requiredIf(in_array(request('penempatan_kontrak'), ['PKWT', 'PERCOBAAN'])), 'numeric', 'min:0'],
                        '*.penempatan_lokasi' => ['required', 'string', 'max:40', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'PENEMPATAN');
                        })],
                        '*.penempatan_posisi' => ['required', 'string', 'max:40', 'exists:posisis,posisi_nama'],
                        '*.penempatan_kategori' => ['required', 'string', 'max:40', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'KATEGORI');
                        })],
                        '*.penempatan_kontrak' => ['required', 'string', 'max:40', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'STATUS KONTRAK');
                        })],
                        '*.penempatan_pangkat' => ['required', 'string', 'max:40', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'PANGKAT');
                        })],
                        '*.penempatan_golongan' => ['required', 'string', 'max:40', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'GOLONGAN');
                        })],
                        '*.penempatan_grup' => ['nullable', 'string', 'max:40'],
                        '*.penempatan_keterangan' => ['nullable', 'string'],
                        '*.penempatan_id_pengunggah' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                        '*.penempatan_id_pembuat' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                        '*.penempatan_id_pengubah' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                        '*.penempatan_diunggah' => ['required', 'nullable', 'date'],
                    ],
                    [
                        '*.penempatan_mulai.*' => 'Penempatan Mulai baris ke-:position wajib berupa tanggal.',
                        '*.penempatan_no_absen.*' => 'Nomor Absen baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                        '*.penempatan_selesai.*' => 'Penempatan Ke baris ke-:position wajib berupa tanggal setelah Penempatan Mulai jika Kontrak Penempatan adalah PKWT dan PERCOBAAN.',
                        '*.penempatan_ke.*' => 'Penempatan Ke baris ke-:position wajib berupa angka minimal 0 jika Kontrak Penempatan adalah PKWT dan PERCOBAAN.',
                        '*.penempatan_lokasi.*' => 'Lokasi Penempatan baris ke-:position maksimal 40 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.penempatan_posisi.*' => 'Jabatan Penempatan baris ke-:position maksimal 40 karakter dan wajib terdaftar Pengaturan Jabatan.',
                        '*.penempatan_kategori.*' => 'Kategori Penempatan baris ke-:position maksimal 40 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.penempatan_kontrak.*' => 'Kontrak Penempatan baris ke-:position maksimal 40 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.penempatan_pangkat.*' => 'Pangkat Penempatan baris ke-:position maksimal 40 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.penempatan_golongan.*' => 'Golongan Penempatan baris ke-:position maksimal 40 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.penempatan_grup.*' => 'Grup Penempatan baris ke-:position maksimal 40 karakter.',
                        '*.penempatan_keterangan.*' => 'Keterangan baris ke-:position wajib berupa karakter.',
                        '*.penempatan_id_pengunggah.*' => 'ID Pengunggah baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                        '*.penempatan_id_pembuat.*' => 'ID Pembuat baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                        '*.penempatan_id_pengubah.*' => 'ID Pengubah baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                        '*.penempatan_diunggah.*' => 'Waktu Unggah baris ke-:position wajib berupa tanggal.',
                    ]
                );
                
                if ($validasi->fails()) {
                    return $app->redirect->back()->withErrors($validasi);
                }
                                
                $app->db->table('penempatans')->upsert(
                    $validasi->validated(),
                    ['penempatan_no_absen', 'penempatan_mulai'],
                    ['penempatan_selesai', 'penempatan_ke', 'penempatan_lokasi', 'penempatan_posisi', 'penempatan_kategori', 'penempatan_kontrak', 'penempatan_pangkat', 'penempatan_golongan', 'penempatan_grup', 'penempatan_keterangan', 'penempatan_id_pengunggah', 'penempatan_id_pengubah', 'penempatan_diunggah']
                );
                
                echo $pesansimpan;
            };

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            
            $storage->delete($fileexcel);
            
            FungsiStatis::hapusCacheSDMUmum();
            
            echo '<p>Selesai menyimpan data excel. Mohon <a class="isi-xhr" href="' . $app->url->route('sdm.penempatan.data-aktif') . '">periksa ulang data</a>.</p>';
            
            exit();
        }

        $HtmlPenuh = $app->view->make('sdm.penempatan.unggah');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }
}
