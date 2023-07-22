<?php

namespace App\Http\Controllers;

use App\Tambahan\FungsiStatis;
use Illuminate\Validation\Rule;
use App\Tambahan\ChunkReadFilter;
use App\Tambahan\CustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ExcelReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as ExcelWriter;
use App\Interaksi\Umum;


class Pengaturan
{
    public function index()
    {
        extract(Umum::obyekLaravel());

        abort_unless($pengguna && $str->contains($pengguna->sdm_hak_akses, ['PENGURUS', 'MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku Aplikasi.');

        $cacheAturs = FungsiStatis::ambilCacheAtur();

        $validator = $this->validasiPermintaanDataPengaturan($app->validator, $reqs->all());

        if ($validator->fails()) {
            return $app->redirect->route('atur.data')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $urutArray = $reqs->urut;

        $uruts = $urutArray ? implode(',', array_filter($urutArray)) : null;

        $statuses = $cacheAturs->unique('atur_status')->pluck('atur_status')->merge($reqs->atur_status)->unique()->sort();

        $jenises = $cacheAturs
            ->when($reqs->filled('atur_status'), function ($cacheAturs, $value) use ($reqs) {
                return $cacheAturs->whereIn('atur_status', $reqs->atur_status);
            })->unique('atur_jenis')->pluck('atur_jenis')->merge($reqs->atur_jenis)->unique()->sort();

        $butirs = $cacheAturs
            ->when($reqs->filled('atur_status'), function ($cacheAturs, $value) use ($reqs) {
                return $cacheAturs->whereIn('atur_status', $reqs->atur_status);
            })
            ->when($reqs->filled('atur_jenis'), function ($cacheAturs, $value) use ($reqs) {
                return $cacheAturs->whereIn('atur_jenis', $reqs->atur_jenis);
            })->unique('atur_butir')->pluck('atur_butir')->merge($reqs->atur_butir)->unique()->sort();

        $kunciUrut = array_filter((array) $urutArray);

        $urutJenis = $str->contains($uruts, 'atur_jenis');
        $indexJenis = (head(array_keys($kunciUrut, 'atur_jenis ASC')) + head(array_keys($kunciUrut, 'atur_jenis DESC')) + 1);
        $urutButir = $str->contains($uruts, 'atur_butir');
        $indexButir = (head(array_keys($kunciUrut, 'atur_butir ASC')) + head(array_keys($kunciUrut, 'atur_butir DESC')) + 1);
        $urutStatus = $str->contains($uruts, 'atur_status');
        $indexStatus = (head(array_keys($kunciUrut, 'atur_status ASC')) + head(array_keys($kunciUrut, 'atur_status DESC')) + 1);


        $cari = $this->ambilDatabasePengaturan($reqs, $uruts);

        if ($reqs->unduh == 'excel') {
            return $this->eksporExcelDatabasePengaturan($app, $reqs, $cari);
        }

        $tabels = $cari->clone()->paginate($reqs->bph ?: 25)->withQueryString()->appends(['fragment' => 'atur_tabels']);

        $data = [
            'tabels' => $tabels,
            'statuses' => $statuses,
            'jenises' => $jenises,
            'butirs' => $butirs,
            'urutJenis' => $urutJenis,
            'indexJenis' => $indexJenis,
            'urutButir' => $urutButir,
            'indexButir' => $indexButir,
            'urutStatus' => $urutStatus,
            'indexStatus' => $indexStatus,
        ];

        $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);

        $HtmlPenuh = $view->make('pengaturan.data', $data);

        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
            ? $respon->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $respon->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function atributInput()
    {
        return [
            'atur_jenis' => 'Kelompok Pengaturan',
            'atur_butir' => 'Butir Pengaturan',
            'atur_detail' => 'Keterangan Pengaturan',
            'atur_status' => 'Status Pengaturan',
            'atur_id_pengunggah' => 'ID Pengunggah',
            'atur_id_pembuat' => 'ID Pembuat',
            'atur_id_pengubah' => 'ID Pengubah',
        ];
    }

    public function contohUnggah()
    {
        $app = app();
        $reqs = $app->request;
        $storage = $app->filesystem;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $str->contains($pengguna->sdm_hak_akses, ['PENGURUS', 'MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku Aplikasi.');

        abort_unless($storage->exists("contoh/unggah-umum.xlsx"), 404, 'Berkas Contoh Ekspor Tidak Ditemukan.');

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');

        $reader = new ExcelReader();
        $spreadsheet = $reader->load($app->storagePath('app/contoh/unggah-umum.xlsx'));
        $filename = 'unggahpengaturan-' . date('YmdHis') . '.xlsx';
        Cell::setValueBinder(new CustomValueBinder());
        $worksheet = $spreadsheet->getSheet(1);
        $x = 1;

        $this->dataDasar()->clone()->latest('atur_dibuat')->chunk(100, function ($hasil) use (&$x, $worksheet) {
            if ($x == 1) {
                $list = $hasil->map(function ($x) {
                    return collect($x)->except(['atur_uuid']);
                })->toArray();
                array_unshift($list, array_keys($list[0]));
                $worksheet->fromArray($list, NULL, 'A' . $x);
                $x++;
            } else {
                $list = $hasil->map(function ($x) {
                    return collect($x)->except(['atur_uuid']);
                })->toArray();
                $worksheet->fromArray($list, NULL, 'A' . $x);
            };
            $x += count($hasil);
            echo '<p>Status : Memproses ' . ($x - 2) . ' data pengaturan.</p>';
        });

        echo '<p>Status : Menyiapkan berkas excel.</p>';

        $writer = new ExcelWriter($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->save($app->storagePath("app/unduh/{$filename}"));
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $app->redirect->to($storage->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)));
    }

    public function dataDasar()
    {
        return app('db')->query()->select('atur_jenis', 'atur_butir', 'atur_detail', 'atur_status')->from('aturs');
    }


    public function dasarValidasi()
    {
        return [
            'atur_jenis' => ['required', 'string', 'max:20'],
            'atur_detail' => ['sometimes', 'nullable', 'string'],
            'atur_status' => ['sometimes', 'string', Rule::in(['AKTIF', 'NON-AKTIF'])],
        ];
    }

    public function lihat($uuid)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        abort_unless($pengguna && $str->contains($pengguna->sdm_hak_akses, ['PENGURUS', 'MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku Aplikasi.');

        $atur = $this->dataDasar()->clone()->addSelect('atur_uuid')->where('atur_uuid', $uuid)->first();

        abort_unless($atur, 404, 'Data Pengaturan tidak ditemukan.');

        $HtmlPenuh = $app->view->make('pengaturan.lihat', compact('atur'));
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']);
    }

    public function tambah(FungsiStatis $fungsiStatis)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        abort_unless($pengguna && $str->contains($pengguna->sdm_hak_akses, ['PENGURUS']), 403, 'Akses dibatasi hanya untuk Pengurus Aplikasi.');

        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        $halaman = $app->view;

        if ($reqs->isMethod('post')) {

            $reqs->merge(['atur_id_pembuat' => $pengguna->sdm_no_absen]);

            $validasi = $app->validator->make(
                $reqs->all(),
                [
                    'atur_butir' => ['required', 'string', 'max:40', Rule::unique('aturs')->where(function ($query) use ($reqs) {
                        $query->where('atur_jenis', $reqs->atur_jenis);
                    })],
                    'atur_id_pembuat' => ['sometimes', 'nullable', 'string', 'exists:sdms,sdm_no_absen'],
                    ...$this->dasarValidasi()
                ],
                [],
                $this->atributInput()
            );

            $validasi->validate();

            $data = $validasi->safe()->all();

            $database = $app->db;

            $database->table('aturs')->insert($data);

            $fungsiStatis->hapusCacheAtur();
            $pesan = $fungsiStatis->statusBerhasil();

            return $app->redirect->route('atur.data')->with('pesan', $pesan);
        }

        $HtmlPenuh = $halaman->make('pengaturan.tambah-ubah');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $tanggapan->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']);
    }

    public function ubah(FungsiStatis $fungsiStatis, $uuid)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $str->contains($pengguna->sdm_hak_akses, ['PENGURUS']), 403, 'Akses dibatasi hanya untuk Pengurus Aplikasi.');

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $atur = $this->dataDasar()->clone()->addSelect('atur_uuid')->where('atur_uuid', $uuid)->first();

        abort_unless($atur, 404, 'Data Pengaturan tidak ditemukan.');

        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        $halaman = $app->view;

        if ($reqs->isMethod('post')) {

            $reqs->merge(['atur_id_pengubah' => $pengguna->sdm_no_absen]);

            $validasi = $app->validator->make(
                $reqs->all(),
                [
                    'atur_butir' => ['required', 'string', 'max:40', Rule::unique('aturs')->where(function ($query) use ($reqs, $uuid) {
                        $query->where('atur_jenis', $reqs->atur_jenis)->whereNot('atur_uuid', $uuid);
                    })],
                    'atur_id_pengubah' => ['sometimes', 'nullable', 'string', 'exists:sdms,sdm_no_absen'],
                    ...$this->dasarValidasi()
                ],
                [],
                $this->atributInput()
            );

            $validasi->validate();

            $data = $validasi->safe()->all();

            $database = $app->db;

            $database->table('aturs')->where('atur_uuid', $uuid)->update($data);

            $fungsiStatis->hapusCacheAtur();
            $pesan = $fungsiStatis->statusBerhasil();

            $session = $reqs->session();
            $redirect = $app->redirect;

            if ($reqs->header('X-Minta-Javascript', false)) {

                $session->now('pesan', $pesan);

                return $tanggapan->make($halaman->make('pemberitahuan'))->withHeaders([
                    'Vary' => 'Accept',
                    'X-Tujuan' => 'sematan_javascript',
                    'X-Kode-Javascript' => true
                ]);
            }

            $perujuk = $session->get('tautan_perujuk');

            return $perujuk ? $redirect->to($perujuk)->with('pesan', $pesan) : $redirect->route('atur.data')->with('pesan', $pesan);
        }

        $data = [
            'atur' => $atur,
        ];

        $HtmlPenuh = $halaman->make('pengaturan.tambah-ubah', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $tanggapan->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']);
    }

    public function unggah(FungsiStatis $fungsiStatis)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $str->contains($pengguna->sdm_hak_akses, ['PENGURUS']), 403, 'Akses dibatasi hanya untuk Pengurus Aplikasi.');

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        $halaman = $app->view;

        if ($reqs->isMethod('post')) {

            set_time_limit(0);
            ob_implicit_flush();
            ob_end_flush();
            header('X-Accel-Buffering: no');

            $validator = $app->validator;

            echo '<p>Memeriksa berkas yang diunggah.</p>';

            $validasifile = $validator->make(
                $reqs->all(),
                [
                    'atur_unggah' => ['required', 'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
                ],
                [],
                [
                    'atur_unggah' => 'Berkas Yang Diunggah'
                ]
            );

            $validasifile->validate();

            $file = $validasifile->safe()->only('atur_unggah')['atur_unggah'];
            $namafile = 'unggahpengaturan-' . date('YmdHis') . '.xlsx';

            $storage = $app->filesystem;

            $storage->putFileAs('unggah', $file, $namafile);

            $fileexcel = storage_path("app/unggah/{$namafile}");

            $reader = new ExcelReader();
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
                    return $x + ['atur_id_pengunggah' => $idPengunggah] + ['atur_id_pembuat' => $idPengunggah] + ['atur_id_pengubah' => $idPengunggah] + ['atur_diunggah' => date('Y-m-d H:i:s')];
                }, $datas);

                $data = array_combine(range(($startRow - 1), count($dataexcel) + ($startRow - 2)), array_values($dataexcel));

                $validasi = $validator->make(
                    $data,
                    [
                        '*.atur_jenis' => ['required', 'string', 'max:20'],
                        '*.atur_butir' => ['required', 'string', 'max:40'],
                        '*.atur_detail' => ['sometimes', 'nullable', 'string'],
                        '*.atur_status' => ['required', 'string', $kategori_status],
                        '*.atur_id_pengunggah' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                        '*.atur_id_pembuat' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                        '*.atur_id_pengubah' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                        '*.atur_diunggah' => ['sometimes', 'nullable', 'date']
                    ],
                    [
                        '*.atur_jenis.required' => 'Jenis Aturan baris ke-:position wajib diisi.',
                        '*.atur_jenis.string' => 'Jenis Aturan baris ke-:position wajib berupa karakter.',
                        '*.atur_jenis.max' => 'Jenis Aturan baris ke-:position maksimum 20 karakter.',
                        '*.atur_butir.required' => 'Butir Aturan baris ke-:position wajib diisi.',
                        '*.atur_butir.string' => 'Butir Aturan baris ke-:position wajib berupa karakter.',
                        '*.atur_butir.max' => 'Butir Aturan baris ke-:position maksimum 40 karakter.',
                        '*.atur_detail.string' => 'Keterangan Aturan baris ke-:position wajib berupa karakter.',
                        '*.atur_status.required' => 'Status Aturan baris ke-:position wajib diisi.',
                        '*.atur_status.string' => 'Status Aturan baris ke-:position wajib berupa karakter.',
                        '*.atur_status.in' => 'Status Aturan baris ke-:position tidak sesuai daftar yang berlaku.',
                        '*.atur_id_pengunggah.*' => 'ID Pengunggah baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                        '*.atur_id_pembuat.*' => 'ID Pembuat baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                        '*.atur_id_pengubah.*' => 'ID Pengubah baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                        '*.atur_diunggah.*' => 'Waktu Unggah baris ke-:position wajib berupa tanggal.'
                    ]
                );

                $validasi->validate();

                $app->db->table('aturs')->upsert(
                    $validasi->validated(),
                    ['atur_jenis', 'atur_butir'],
                    ['atur_detail', 'atur_status', 'atur_id_pengunggah', 'atur_diunggah', 'atur_id_pengubah']
                );

                echo $pesansimpan;
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            $storage->delete($fileexcel);

            $fungsiStatis->hapusCacheAtur();

            $pesan = $fungsiStatis->statusBerhasil();

            return $app->redirect->route('atur.data')->with('pesan', $pesan);
        };

        $HtmlPenuh = $halaman->make('pengaturan.unggah');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']);
    }

    public function validasiPermintaanDataPengaturan($validator, $permintaan)
    {
        return $validator->make(
            $permintaan,
            [
                'kata_kunci' => ['sometimes', 'nullable', 'string'],
                'atur_jenis.*' => ['sometimes', 'nullable', 'string', 'max:20',],
                'atur_butir.*' => ['sometimes', 'nullable', 'string', 'max:40'],
                'atur_status.*' => ['sometimes', 'nullable', 'string', Rule::in(['', 'AKTIF', 'NON-AKTIF'])],
                'bph' => ['sometimes', 'nullable', 'numeric', Rule::in([25, 50, 75, 100])],
                'urut.*' => ['sometimes', 'nullable', 'string']
            ],
            [
                'atur_status.*.string' => 'Status Pengaturan urutan #:position wajib berupa karakter.',
                'atur_status.*.in' => 'Status Pengaturan urutan #:position tidak sesuai daftar.',
                'atur_butir.*.string' => 'Butir Pengaturan urutan #:position wajib berupa karakter.',
                'atur_butir.*.max' => 'Butir Pengaturan urutan #:position maksimal 40 karakter.',
                'atur_jenis.*.string' => 'Butir Pengaturan urutan #:position wajib berupa karakter.',
                'atur_jenis.*.max' => 'Butir Pengaturan urutan #:position maksimal 20 karakter.',
                'kata_kunci.*' => 'Kata Kunci Pencarian harus berupa karakter',
                'bph.*' => 'Baris Per halaman tidak sesuai daftar.',
                'urut.*.string' => 'Butir Pengaturan urutan #:position wajib berupa karakter.'
            ]
        );
    }

    public function ambilDatabasePengaturan($reqs, $uruts)
    {
        return $this->dataDasar()->clone()->addSelect('atur_uuid')
            ->when($reqs->atur_status, function ($query) use ($reqs) {
                $query->whereIn('atur_status', $reqs->atur_status);
            })
            ->when($reqs->atur_jenis, function ($query) use ($reqs) {
                $query->whereIn('atur_jenis', $reqs->atur_jenis);
            })
            ->when($reqs->atur_butir, function ($query) use ($reqs) {
                $query->whereIn('atur_butir', $reqs->atur_butir);
            })
            ->when($reqs->kata_kunci, function ($query) use ($reqs) {
                $query->where(function ($group) use ($reqs) {
                    $group->where('atur_jenis', 'like', '%' . $reqs->kata_kunci . '%')
                        ->orWhere('atur_butir', 'like', '%' . $reqs->kata_kunci . '%')
                        ->orWhere('atur_detail', 'like', '%' . $reqs->kata_kunci . '%');
                });
            })
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->latest('atur_dibuat');
                }
            );
    }

    public function eksporExcelDatabasePengaturan($app, $reqs, $data)
    {
        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $this->setelResponStream();

        $spreadsheet = new Spreadsheet();
        $filename = 'eksporpengaturan-' . date('YmdHis') . '.xlsx';
        Cell::setValueBinder(new CustomValueBinder());
        $worksheet = $spreadsheet->getActiveSheet();
        $x = 1;

        $data->clone()->chunk(100, function ($hasil) use (&$x, $worksheet) {
            if ($x == 1) {
                $list = $hasil->map(function ($x) {
                    return collect($x)->except(['atur_uuid']);
                })->toArray();
                array_unshift($list, array_keys($list[0]));
                $worksheet->fromArray($list, NULL, 'A' . $x);
                $x++;
            } else {
                $list = $hasil->map(function ($x) {
                    return collect($x)->except(['atur_uuid']);
                })->toArray();
                $worksheet->fromArray($list, NULL, 'A' . $x);
            };
            $x += count($hasil);
            echo '<p>Status : Memproses ' . ($x - 2) . ' data pengaturan.</p>';
        });

        echo '<p>Status : Menyiapkan berkas excel.</p>';

        $writer = new ExcelWriter($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->save($app->storagePath("app/unduh/{$filename}"));
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $app->redirect->to($app->filesystem->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)));
    }

    public function setelResponStream()
    {
        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');
    }
}
