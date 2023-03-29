<?php

namespace App\Http\Controllers\SDM;

use App\Tambahan\FungsiStatis;
use Illuminate\Validation\Rule;
use App\Tambahan\ChunkReadFilter;
use App\Tambahan\CustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Posisi
{
    public function index(Rule $rule)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();
        
        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');
        
        $validator = $app->validator->make(
            $reqs->all(),
            [
                'posisi_status' => ['sometimes', 'nullable', 'string', $rule->in(['AKTIF', 'NON-AKTIF'])],
                'kata_kunci' => ['sometimes', 'nullable', 'string'],
                'bph' => ['sometimes', 'nullable', 'numeric', $rule->in([25, 50, 75, 100])],
                'urut.*' => ['sometimes', 'nullable', 'string'],
                'penempatan_lokasi.*' => ['sometimes', 'nullable', 'string'],
                'penempatan_kontrak.*' => ['sometimes', 'nullable', 'string']
            ],
            [
                'posisi_status.*' => 'Status Jabatan harus sesuai daftar.',
                'kata_kunci.*' => 'Kata Kunci Pencarian harus berupa karakter.',
                'bph.*' => 'Baris Per halaman tidak sesuai daftar.',
                'urut.*.string' => 'Butir Pengaturan urutan #:position wajib berupa karakter.',
                'penempatan_lokasi.*.string' => 'Butir Pengaturan urutan #:position wajib berupa karakter.',
                'penempatan_kontrak.*.string' => 'Butir Pengaturan urutan #:position wajib berupa karakter.',
            ]
        );
        
        if ($validator->fails()) {
            return $app->redirect->route('sdm.posisi.data')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $urutArray = $reqs->urut;
        $uruts = $urutArray ? implode(',', array_filter($urutArray)) : null;
        $kataKunci = $reqs->kata_kunci;

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkupIjin = array_filter(explode(',', $ijin_akses));
        $lingkup_lokasi = collect($reqs->lokasi);
        $lingkup_akses = $lingkup_lokasi->intersect($lingkupIjin)->count();
        $maks_akses = collect($lingkupIjin)->count();
        $permin_akses = $lingkup_lokasi->count();

        abort_unless(blank($ijin_akses) || ($lingkup_akses <= $maks_akses && $maks_akses >= $permin_akses), 403, 'Akses lokasi lain dibatasi.');
        
        $database = $app->db;

        $kontrak = $database->query()->select('penempatan_posisi', 'penempatan_no_absen', 'penempatan_lokasi', 'penempatan_kontrak')
        ->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
            $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
        });

        $cariSub = $this->dataDasar()->clone()->addSelect('posisi_uuid', 'posisi_dibuat', 'penempatan_lokasi', 'penempatan_kontrak', $database->raw('COUNT(DISTINCT CASE WHEN sdm_tgl_berhenti IS NULL THEN sdm_no_absen END) jml_aktif, COUNT(DISTINCT CASE WHEN sdm_tgl_berhenti IS NOT NULL THEN sdm_no_absen END) jml_nonaktif'))
        ->leftJoinSub($kontrak, 'kontrak', function ($join) {
            $join->on('posisi_nama', '=', 'kontrak.penempatan_posisi');
        })
        ->leftJoin('sdms', 'sdm_no_absen', '=', 'penempatan_no_absen')
        ->groupBy('posisi_nama');

        $cari = $database->query()->addSelect('tsdm.*', $app->db->raw('IF((jml_aktif + jml_nonaktif) > 0, (jml_nonaktif / (jml_nonaktif + jml_aktif)) * 100, 0) as pergantian'))->fromSub($cariSub, 'tsdm')
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
        ->when($reqs->lokasi, function ($query) use ($reqs) {
            $query->whereIn('penempatan_lokasi', $reqs->lokasi);
        })
        ->when($reqs->kontrak, function ($query) use ($reqs) {
            $query->whereIn('penempatan_kontrak', $reqs->kontrak);
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


        if ($reqs->unduh == 'excel') {
            abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

            set_time_limit(0);
            ob_implicit_flush();
            ob_end_flush();
            header('X-Accel-Buffering: no');
            
            $spreadsheet = new Spreadsheet();
            $filename = 'eksporjabatansdm-' . date('YmdHis') . '.xlsx';
            Cell::setValueBinder(new CustomValueBinder());
            $worksheet = $spreadsheet->getActiveSheet();
            $x = 1;
            
            $cari->clone()->chunk(100, function ($hasil) use (&$x, $worksheet) {
                if ($x == 1) {
                    $list = $hasil->map(function ($x) {
                        return collect($x)->except(['posisi_uuid']);
                    })->toArray();
                    array_unshift($list, array_keys($list[0]));
                    $worksheet->fromArray($list, NULL, 'A' . $x);
                    $x++;
                } else {
                    $list = $hasil->map(function ($x) {
                        return collect($x)->except(['posisi_uuid']);
                    })->toArray();
                    $worksheet->fromArray($list, NULL, 'A' . $x);
                };
                $x += count($hasil);
                echo '<p>Status : Memproses ' . ($x - 2) . ' data jabatan SDM.</p>';
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

        $aktif = $cari->clone()->sum('jml_aktif');
        $nonAktif = $cari->clone()->sum('jml_nonaktif');
        $total = $aktif + $nonAktif;

        $tabels = $cari->clone()->paginate($reqs->bph ?: 25)->withQueryString()->appends(['fragment' => 'sdm_posisi_tabels']);

        $kunciUrut = array_filter((array) $urutArray);

        $cacheAtur = FungsiStatis::ambilCacheAtur();

        $urutPergantian = $str->contains($uruts, 'pergantian');
        $indexPergantian = (head(array_keys($kunciUrut, 'pergantian ASC')) + head(array_keys(array_filter((array)  $urutArray), 'pergantian DESC')) + 1);
        $urutPosisi = $str->contains($uruts, 'posisi_nama');
        $indexPosisi = (head(array_keys($kunciUrut, 'posisi_nama ASC')) + head(array_keys(array_filter((array)  $urutArray), 'posisi_nama DESC')) + 1);
        $urutAktif = $str->contains($uruts, 'jml_aktif');
        $indexAktif = (head(array_keys($kunciUrut, 'jml_aktif ASC')) + head(array_keys(array_filter((array)  $urutArray), 'jml_aktif DESC')) + 1);
        $urutNonAktif = $str->contains($uruts, 'jml_nonaktif');
        $indexNonAktif = (head(array_keys($kunciUrut, 'jml_nonaktif ASC')) + head(array_keys(array_filter((array)  $urutArray), 'jml_nonaktif DESC')) + 1);
        
        $data = [
            'tabels' => $tabels,
            'urutPergantian' => $urutPergantian,
            'indexPergantian' => $indexPergantian,
            'urutPosisi' => $urutPosisi,
            'indexPosisi' => $indexPosisi,
            'urutAktif' => $urutAktif,
            'indexAktif' => $indexAktif,
            'urutNonAktif' => $urutNonAktif,
            'indexNonAktif' => $indexNonAktif,
            'lokasis' => $cacheAtur->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin);
            })->sortBy(['atur_butir', 'asc']),
            'kontraks' => $cacheAtur->where('atur_jenis', 'STATUS KONTRAK')->sortBy(['atur_butir', 'asc']),
            'aktif' => $aktif,
            'nonAktif' => $nonAktif,
            'total' => $total
        ];

        $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);

        $HtmlPenuh = $app->view->make('sdm.posisi.data', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
        ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']) 
        : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function atributInput()
    {
        return [
            'posisi_nama' => 'Jabatan',
            'posisi_wlkp' => 'Kode Jabatan WLKP',
            'posisi_atasan' => 'Jabatan Atasan',
            'posisi_keterangan' => 'Keterangan',
            'posisi_status' => 'Status Jabatan',
            'posisi_id_pengunggah' => 'ID Pengunggah',
            'posisi_id_pembuat' => 'ID Pembuat',
            'posisi_id_pengubah' => 'ID Pengubah',
        ];
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
        $filename = 'unggahjabatansdm-' . date('YmdHis') . '.xlsx';
        Cell::setValueBinder(new CustomValueBinder());
        $worksheet = $spreadsheet->getSheet(1);
        $x = 1;
        
        $this->dataDasar()->clone()->latest('posisi_dibuat')->chunk(100, function ($hasil) use (&$x, $worksheet) {
            if ($x == 1) {
                $list = $hasil->map(function ($x) {
                    return collect($x)->except(['posisi_uuid']);
                })->toArray();
                array_unshift($list, array_keys($list[0]));
                $worksheet->fromArray($list, NULL, 'A' . $x);
                $x++;
            } else {
                $list = $hasil->map(function ($x) {
                    return collect($x)->except(['posisi_uuid']);
                })->toArray();
                $worksheet->fromArray($list, NULL, 'A' . $x);
            };
            $x += count($hasil);
            echo '<p>Status : Memproses ' . ($x - 2) . ' data jabatan SDM.</p>';
        });
        
        echo '<p>Status : Menyiapkan berkas excel.</p>';
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->setPreCalculateFormulas(false);
        $writer->save($app->storagePath("app/unduh/{$filename}"));
        $spreadsheet->disconnectWorksheets();
        
        unset($spreadsheet);
        
        echo '<p>Selesai menyiapkan berkas excel. <a href="' . $storage->disk('local')->temporaryUrl("unduh/{$filename}", now()->addMinutes(5)) . '">Unduh</a>.</p>';
        
        exit();
    }

    public function dataDasar()
    {
        return app('db')->query()->select('posisi_nama', 'posisi_atasan', 'posisi_wlkp', 'posisi_status', 'posisi_keterangan')->from('posisis');
    }

    public function dasarValidasi()
    {
        return [
            'posisi_wlkp' => ['nullable', 'string', 'max:40'],
            'posisi_keterangan' => ['nullable', 'string', 'max:40'],
            'posisi_status' => ['required', 'string', Rule::in(['AKTIF', 'NON-AKTIF'])],
        ];
    }

    public function lihat($uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();
        
        abort_unless($pengguna && $uuid && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');
        
        $pos = $this->dataDasar()->clone()->addSelect('posisi_uuid')->where('posisi_uuid', $uuid)->first();
        
        abort_unless($pos, 404);
        
        $HtmlPenuh = $app->view->make('sdm.posisi.lihat', compact('pos'));
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }

    public function tambah(FungsiStatis $fungsiStatis)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();
        
        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');
        
        if ($reqs->isMethod('post')) {
            
            $reqs->merge(['posisi_id_pembuat' => $pengguna->sdm_no_absen]);
            
            $validasi = $app->validator->make(
                $reqs->all(),
                [
                    'posisi_nama' => ['required', 'string', 'unique:posisis,posisi_nama'],
                    'posisi_atasan' => ['nullable', 'string', 'max:40', 'different:posisi_nama'],
                    'posisi_id_pembuat' => ['sometimes', 'nullable', 'string'],
                    ...$this->dasarValidasi()
                ],
                [],
                $this->atributInput()
            );
            
            $validasi->validate();
            
            $data = $validasi->safe()->all();
            
            $app->db->table('posisis')->insert($data);
            
            $fungsiStatis->hapusCacheSDMUmum();
            $perujuk = $reqs->session()->get('tautan_perujuk');
            $pesan = $fungsiStatis->statusBerhasil();
            $redirect = $app->redirect;
            
            return $perujuk ? $redirect->to($perujuk)->with('pesan', $pesan) : $redirect->route('sdm.posisi.data')->with('pesan', $pesan);
        }

        $data = [
            'posisis' => $fungsiStatis->ambilCachePosisiSDM(),
        ];

        $HtmlPenuh = $app->view->make('sdm.posisi.tambah-ubah', $data);
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
        
        $pos = $this->dataDasar()->clone()->addSelect('posisi_uuid')->where('posisi_uuid', $uuid)->first();
        
        abort_unless($pos, 404, 'Data Jabatan tidak ditemukan.');
        
        if ($reqs->isMethod('post')) {
            
            $reqs->merge(['posisi_id_pengubah' => $pengguna->sdm_no_absen]);
            
            $validasi = $app->validator->make(
                $reqs->all(),
                [
                    'posisi_nama' => ['required', 'string', 'max:40', Rule::unique('posisis')->where(fn ($query) => $query->whereNot('posisi_uuid', $uuid))],
                    'posisi_atasan' => ['nullable', 'string', 'max:40', 'different:posisi_nama'],
                    'posisi_id_pengunggah' => ['sometimes', 'nullable', 'string'],
                    ...$this->dasarValidasi()
                ],
                [],
                $this->atributInput()
            );
            
            $validasi->validate();
            
            $data = $validasi->safe()->all();
            
            $app->db->table('posisis')->where('posisi_uuid', $uuid)->update($data);
            
            $fungsiStatis->hapusCacheSDMUmum();
            $perujuk = $reqs->session()->get('tautan_perujuk');
            $pesan = $fungsiStatis->statusBerhasil();
            $redirect = $app->redirect;
            
            return $perujuk ? $redirect->to($perujuk)->with('pesan', $pesan) : $redirect->route('sdm.posisi.data')->with('pesan', $pesan);
        }

        $data = [
            'posisis' => $fungsiStatis->ambilCachePosisiSDM(),
            'pos' => $pos
        ];

        $HtmlPenuh = $app->view->make('sdm.posisi.tambah-ubah', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }

    public function unggah()
    {
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
                    'posisi_unggah' => ['required', 'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
                ],
                [],
                [
                    'posisi_unggah' => 'Berkas Yang Diunggah'
                ]
            );
            
            $validasifile->validate();
            $file = $validasifile->safe()->only('posisi_unggah')['posisi_unggah'];
            $namafile = 'unggahjabatansdm-' . date('YmdHis') . '.xlsx';

            $storage = $app->filesystem;
            $storage->putFileAs('unggah', $file, $namafile);
            $fileexcel = $app->storagePath("app/unggah/{$namafile}");
            $reader = IOFactory::createReader('Xlsx');
            $spreadsheetInfo = $reader->listWorksheetInfo($fileexcel);
            $chunkSize = 25;
            $chunkFilter = new ChunkReadFilter();
            $reader->setReadFilter($chunkFilter);
            $reader->setReadDataOnly(true);
            $totalRows = $spreadsheetInfo[1]['totalRows'];
            $kategori_status = Rule::in(['AKTIF', 'NON-AKTIF']);
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
                
                $headingArray = $worksheet->rangeToArray('A1:' . $kolomTertinggi . '1', NULL, FALSE, TRUE, FALSE);
                $dataArray = $worksheet->rangeToArray('A' . $startRow . ':' . $kolomTertinggi . $barisTertinggi, NULL, FALSE, TRUE, FALSE);
                $tabel = array_merge($headingArray, $dataArray);
                $isitabel = array_shift($tabel);
                
                $datas = array_map(function ($x) use ($isitabel) {
                    return array_combine($isitabel, $x);
                }, $tabel);
                
                $dataexcel = array_map(function ($x) use ($idPengunggah) {
                    return $x + ['posisi_id_pengunggah' => $idPengunggah] + ['posisi_id_pembuat' => $idPengunggah] + ['posisi_id_pengubah' => $idPengunggah] + ['posisi_diunggah' => date('Y-m-d H:i:s')];
                }, $datas);

                $data = array_combine(range(($startRow - 1), count($dataexcel) + ($startRow - 2) ), array_values($dataexcel));
                
                $validasi = $validator->make(
                    $data,
                    [
                        '*.posisi_nama' => ['required', 'string', 'max:40'],
                        '*.posisi_atasan' => ['nullable', 'string', 'max:40', 'different:*.posisi_nama'],
                        '*.posisi_wlkp' => ['nullable'],
                        '*.posisi_status' => ['required', 'string', $kategori_status],
                        '*.posisi_id_pengunggah' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                        '*.posisi_id_pembuat' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                        '*.posisi_id_pengubah' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                        '*.posisi_diunggah' => ['sometimes', 'nullable', 'date']
                    ],
                    [
                        '*.posisi_nama.*' => 'Nama Jabatan baris ke-:position wajib diisi, berupa karakter maks 40 karakter.',
                        '*.posisi_atasan.*' => 'Jabatan Atasan baris ke-:position wajib berbeda dengan Nama Jabatan, berupa karakter maks 40 karakter.',
                        '*.posisi_wlkp.*' => 'Kode Jabatan WLKP baris ke-:position wajib diisi, berupa karakter.',
                        '*.posisi_status.*' => 'Status Jabatan baris ke-:position wajib diisi sesuai daftar.',
                        '*.posisi_id_pengunggah.*' => 'ID Pengunggah baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                        '*.posisi_id_pembuat.*' => 'ID Pembuat baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                        '*.posisi_id_pengubah.*' => 'ID Pengubah baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                        '*.posisi_diunggah.*' => 'Waktu Unggah baris ke-:position wajib berupa tanggal.'
                    ]
                );
                
                if ($validasi->fails()) {
                    return $app->redirect->back()->withErrors($validasi);
                }
                                
                $app->db->table('posisis')->upsert(
                    $validasi->validated(),
                    ['posisi_nama'],
                    ['posisi_wlkp', 'posisi_status', 'posisi_id_pengunggah', 'posisi_diunggah', 'posisi_id_pengubah']
                );
                
                echo $pesansimpan;
            };
            
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            
            $storage->delete($fileexcel);
            
            FungsiStatis::hapusCacheSDMUmum();
            
            echo '<p>Selesai menyimpan data excel. Mohon <a class="isi-xhr" href="' . $app->url->route('sdm.posisi.data') . '">periksa ulang data</a>.</p>';
            
            exit();
        };

        $HtmlPenuh = $app->view->make('sdm.posisi.unggah');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }
}
