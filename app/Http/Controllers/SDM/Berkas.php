<?php

namespace App\Http\Controllers\SDM;

use App\Tambahan\FungsiStatis;
use Illuminate\Validation\Rule;
use App\Tambahan\ChunkReadFilter;
use App\Tambahan\CustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ExcelReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as ExcelWriter;

class Berkas
{
    public function eksporExcelStream($namaBerkas, $dataEkspor, $pengecualian, $pesanData, $app, $binder, $spreadsheet, $worksheet, $chunk = 100, $tabelStart = null, $namaTabel = null, $spreadsheet2 = null)
    {
        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');

        $filename = $namaBerkas . date('YmdHis') . '.xlsx';
        Cell::setValueBinder($binder);
        $x = 1;

        $dataEkspor->chunk($chunk, function ($hasil) use (&$x, $worksheet, $pengecualian, $pesanData) {
            if ($x == 1) {
                $list = $hasil->map(function ($x) use ($pengecualian) {
                    return collect($x)->except($pengecualian);
                })->toArray();
                array_unshift($list, array_keys($list[0]));
                $worksheet->fromArray($list, NULL, 'A' . $x);
                $x++;
            } else {
                $list = $hasil->map(function ($x) use ($pengecualian) {
                    return collect($x)->except($pengecualian);
                })->toArray();
                $worksheet->fromArray($list, NULL, 'A' . $x);
            };
            $x += count($hasil);
            echo '<p>Status : Memproses ' . ($x - 2) . $pesanData . '.</p>';
        });

        if ($tabelStart && $namaTabel && $spreadsheet2) {
            echo '<p>Status : Menyiapkan tabel excel.</p>';

            $tabel = new Table($tabelStart . $worksheet->getHighestColumn() . $worksheet->getHighestRow(), $namaTabel);

            $spreadsheet->getActiveSheet()->addTable($tabel);

            echo '<p>Status : Menyiapkan sheet Perhitungan.</p>';

            $clonedWorksheet = clone $spreadsheet2->getSheetByName('Sum');
            $spreadsheet->addExternalSheet($clonedWorksheet);
        }

        $writer = new ExcelWriter($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->save($app->storagePath("app/unduh/{$filename}"));
        $spreadsheet->disconnectWorksheets();

        unset($spreadsheet);

        echo '<p>Status : Menyiapkan berkas excel.</p>';

        return $app->redirect->to($app->filesystem->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)));

        exit();
    }

    public function contohUnggahPenempatanSDM()
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $storage = $app->filesystem;

        $berkasContoh = 'unggah-umum.xlsx';

        abort_unless($storage->exists("contoh/unggah-umum.xlsx"), 404, 'Berkas Contoh Ekspor Tidak Ditemukan.');

        $binder = new CustomValueBinder();
        $reader = new ExcelReader();
        $spreadsheet = $reader->load($app->storagePath("app/contoh/{$berkasContoh}"));
        $worksheet = $spreadsheet->getSheet(1);

        $database = $app->db;

        $lingkup = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $cari = $database->query()->select('sdm_nama', 'penempatan_no_absen', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_lokasi', 'penempatan_posisi', 'penempatan_kategori', 'penempatan_kontrak', 'penempatan_pangkat', 'penempatan_golongan', 'penempatan_grup', 'penempatan_keterangan')
            ->from('penempatans')
            ->join('sdms', 'penempatan_no_absen', '=', 'sdm_no_absen')
            ->whereNull('sdm_tgl_berhenti')
            ->when($lingkup, function ($c) use ($lingkup) {
                return $c->whereIn('penempatan_lokasi', $lingkup);
            })
            ->orderBy('penempatans.id');

        $argumen = [
            'namaBerkas' => 'unggahpenempatansdm-',
            'dataEkspor' => $cari->clone(),
            'pengecualian' => ['id'],
            'pesanData' =>  ' data penempatan SDM',
            'app' => $app,
            'binder' => $binder,
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet
        ];

        return $this->eksporExcelStream(...$argumen);
    }

    public function isiFormulir($app, $contoh, $data, $filename)
    {
        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');

        echo '<p>Memeriksa formulir.</p>';

        $storage = $app->filesystem;

        abort_unless($storage->exists("contoh/{$contoh}"), 404, 'Berkas Contoh Formulir Tidak Ditemukan.');

        $templateProcessor = new TemplateProcessor($app->storagePath("app/contoh/{$contoh}"));

        echo '<p>Menyiapkan formulir.</p>';

        $templateProcessor->setValues($data);

        $templateProcessor->saveAs($app->storagePath("app/unduh/{$filename}"));

        return $app->redirect->to($storage->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)));

        exit();
    }

    public function formulirPenilaianSDM($uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $uuid && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $database = $app->db;

        $dataSDM = $database->query()->select('id', 'sdm_uuid', 'sdm_no_absen', 'sdm_no_permintaan', 'sdm_tgl_lahir', 'sdm_tempat_lahir', 'sdm_tgl_gabung', 'sdm_no_ktp', 'sdm_nama', 'sdm_kelamin', 'sdm_tgl_berhenti', 'sdm_jenis_berhenti', 'sdm_ket_kary', 'sdm_ket_berhenti', 'sdm_alamat', 'sdm_alamat_rt', 'sdm_alamat_rw', 'sdm_alamat_kelurahan', 'sdm_alamat_kecamatan', 'sdm_alamat_kota', 'sdm_alamat_provinsi', 'sdm_alamat_kodepos', 'sdm_disabilitas', 'sdm_agama', 'sdm_status_kawin', 'sdm_pendidikan', 'sdm_warganegara', 'sdm_uk_seragam', 'sdm_uk_sepatu', 'sdm_jurusan', 'sdm_telepon', 'email', 'sdm_id_atasan', 'sdm_no_bpjs', 'sdm_no_jamsostek', 'sdm_jml_anak')
            ->from('sdms');

        $permin = $database->query()->select('penempatan_uuid', 'penempatan_no_absen', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_lokasi', 'penempatan_posisi', 'penempatan_kategori', 'penempatan_kontrak', 'penempatan_pangkat', 'penempatan_golongan', 'penempatan_grup', 'penempatan_keterangan')
            ->from('penempatans')->clone()->addSelect('sdm_nama')
            ->joinSub($dataSDM, 'sdm', function ($join) {
                $join->on('penempatan_no_absen', '=', 'sdm_no_absen');
            })->where('penempatan_uuid', $uuid)->first();

        abort_unless($permin, 404, 'Data Penempatan SDM tidak ditemukan.');

        $no_absen = $permin->penempatan_no_absen;

        $filename = 'penilaian-kinerja-' . $no_absen . '.docx';

        $data = [
            'sdm_nama' => str($permin->sdm_nama)->limit(30),
            'sdm_no_absen' => $no_absen,
            'sdm_jabatan' => $permin->penempatan_posisi
        ];

        $argumen = [
            'app' => $app,
            'contoh' => 'penilaian-kinerja.docx',
            'data' => $data,
            'filename' => $filename
        ];

        return $this->isiFormulir(...$argumen);
    }

    public function formulirPerubahanStatusSDM($uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $uuid && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $database = $app->db;

        $dataSDM = $database->query()->select('id', 'sdm_uuid', 'sdm_no_absen', 'sdm_no_permintaan', 'sdm_tgl_lahir', 'sdm_tempat_lahir', 'sdm_tgl_gabung', 'sdm_no_ktp', 'sdm_nama', 'sdm_kelamin', 'sdm_tgl_berhenti', 'sdm_jenis_berhenti', 'sdm_ket_kary', 'sdm_ket_berhenti', 'sdm_alamat', 'sdm_alamat_rt', 'sdm_alamat_rw', 'sdm_alamat_kelurahan', 'sdm_alamat_kecamatan', 'sdm_alamat_kota', 'sdm_alamat_provinsi', 'sdm_alamat_kodepos', 'sdm_disabilitas', 'sdm_agama', 'sdm_status_kawin', 'sdm_pendidikan', 'sdm_warganegara', 'sdm_uk_seragam', 'sdm_uk_sepatu', 'sdm_jurusan', 'sdm_telepon', 'email', 'sdm_id_atasan', 'sdm_no_bpjs', 'sdm_no_jamsostek', 'sdm_jml_anak')
            ->from('sdms');

        $permin = $database->query()->select('penempatan_uuid', 'penempatan_no_absen', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_lokasi', 'penempatan_posisi', 'penempatan_kategori', 'penempatan_kontrak', 'penempatan_pangkat', 'penempatan_golongan', 'penempatan_grup', 'penempatan_keterangan')
            ->from('penempatans')->clone()->addSelect('sdm_nama')
            ->joinSub($dataSDM, 'sdm', function ($join) {
                $join->on('penempatan_no_absen', '=', 'sdm_no_absen');
            })->where('penempatan_uuid', $uuid)->first();

        abort_unless($permin, 404, 'Data Penempatan SDM tidak ditemukan.');

        $no_absen = $permin->penempatan_no_absen;

        $filename = 'perubahan-status-sdm-' . $no_absen . '.docx';

        $data = [
            'sdm_nama' => str($permin->sdm_nama)->limit(30),
            'sdm_no_absen' => $no_absen,
            'sdm_jabatan' => $permin->penempatan_posisi,
            'sdm_kontrak' => $permin->penempatan_kontrak,
            'sdm_ke' => $permin->penempatan_ke,
            'sdm_golongan' => $permin->penempatan_golongan,
            'sdm_lokasi' => $permin->penempatan_lokasi,
        ];

        $argumen = [
            'app' => $app,
            'contoh' => 'perubahan-status-sdm.docx',
            'data' => $data,
            'filename' => $filename
        ];

        return $this->isiFormulir(...$argumen);
    }

    public function PKWTSDM($uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $uuid && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $database = $app->db;

        $dataSDM = $database->query()->select('id', 'sdm_uuid', 'sdm_no_absen', 'sdm_no_permintaan', 'sdm_tgl_lahir', 'sdm_tempat_lahir', 'sdm_tgl_gabung', 'sdm_no_ktp', 'sdm_nama', 'sdm_kelamin', 'sdm_tgl_berhenti', 'sdm_jenis_berhenti', 'sdm_ket_kary', 'sdm_ket_berhenti', 'sdm_alamat', 'sdm_alamat_rt', 'sdm_alamat_rw', 'sdm_alamat_kelurahan', 'sdm_alamat_kecamatan', 'sdm_alamat_kota', 'sdm_alamat_provinsi', 'sdm_alamat_kodepos', 'sdm_disabilitas', 'sdm_agama', 'sdm_status_kawin', 'sdm_pendidikan', 'sdm_warganegara', 'sdm_uk_seragam', 'sdm_uk_sepatu', 'sdm_jurusan', 'sdm_telepon', 'email', 'sdm_id_atasan', 'sdm_no_bpjs', 'sdm_no_jamsostek', 'sdm_jml_anak')
            ->from('sdms');

        $permin = $database->query()->select('penempatan_uuid', 'penempatan_no_absen', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_lokasi', 'penempatan_posisi', 'penempatan_kategori', 'penempatan_kontrak', 'penempatan_pangkat', 'penempatan_golongan', 'penempatan_grup', 'penempatan_keterangan')
            ->from('penempatans')->clone()->addSelect('sdm_nama', 'sdm_tgl_lahir', 'sdm_tempat_lahir', 'sdm_kelamin', 'sdm_alamat', 'sdm_alamat_kelurahan', 'sdm_alamat_kecamatan', 'sdm_alamat_kota', 'sdm_alamat_provinsi')
            ->joinSub($dataSDM, 'sdm', function ($join) {
                $join->on('penempatan_no_absen', '=', 'sdm_no_absen');
            })->where('penempatan_uuid', $uuid)->first();

        abort_unless($permin, 404, 'Data Penempatan SDM tidak ditemukan.');

        $date = $app->date;

        $filename = 'pkwt-' . $permin->penempatan_no_absen . '.docx';

        $data = [
            'sdm_nama' => str($permin->sdm_nama)->limit(30),
            'sdm_jabatan' => $permin->penempatan_posisi,
            'sdm_tgl_lahir' => $permin->sdm_tempat_lahir . ', ' . strtoupper($date->make($permin->sdm_tgl_lahir)?->translatedFormat('d F Y')),
            'sdm_kelamin' => $permin->sdm_kelamin == 'L' ? 'LAKI - LAKI' : 'PEREMPUAN',
            'sdm_alamat' => "{$permin->sdm_alamat}, {$permin->sdm_alamat_kelurahan}, {$permin->sdm_alamat_kecamatan}, {$permin->sdm_alamat_kota}, {$permin->sdm_alamat_provinsi}.",
            'sdm_mulai' => strtoupper($date->make($permin->penempatan_mulai)?->translatedFormat('d F Y')),
            'sdm_sampai' => strtoupper($date->make($permin->penempatan_selesai)?->translatedFormat('d F Y')),
        ];

        $argumen = [
            'app' => $app,
            'contoh' => 'pkwt.docx',
            'data' => $data,
            'filename' => $filename
        ];

        return $this->isiFormulir(...$argumen);
    }

    public function rekamHapusDataSDM($app, $dataHapus)
    {
        $reader = new ExcelReader();
        $spreadsheet = $reader->load($app->storagePath('app/contoh/data-dihapus.xlsx'));
        Cell::setValueBinder(new CustomValueBinder());
        $worksheet = $spreadsheet->getActiveSheet();
        $barisAkhir = $worksheet->getHighestRow() + 1;
        $worksheet->fromArray($dataHapus, NULL, 'A' . $barisAkhir);

        $writer = new ExcelWriter($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->save($app->storagePath('app/contoh/data-dihapus.xlsx'));
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }

    public function unggahPenempatanSDM(Rule $rule)
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
            $reader = new ExcelReader();
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

                $data = array_combine(range(($startRow - 1), count($dataexcel) + ($startRow - 2)), array_values($dataexcel));

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
