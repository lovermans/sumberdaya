<?php

namespace App\Http\Controllers\SDM;

use QRcode;
use App\Tambahan\FungsiStatis;
use Illuminate\Validation\Rule;
use App\Tambahan\ChunkReadFilter;
use App\Tambahan\CustomValueBinder;
use App\Http\Controllers\SDM\Posisi;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Http\Controllers\SDM\PermintaanTambahSDM;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class Berkas
{
    public function berkas($berkas = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $permintaanBerkas = $reqs->path();

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        abort_unless($berkas && $app->filesystem->exists("{$permintaanBerkas}"), 404, 'Berkas tidak ditemukan.');

        $jalur = $app->storagePath("app/{$permintaanBerkas}");

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->file($jalur, [
            'Content-Disposition' => 'inline',
            'Content-Type' => $app->files->mimeType($jalur),
        ]);
    }

    public function fotoProfil($berkas_foto_profil = null)
    {
        $app = app();

        abort_unless($berkas_foto_profil && $app->filesystem->exists("sdm/foto-profil/{$berkas_foto_profil}"), 404, 'Foto Profil tidak ditemukan.');

        $jalur = $app->storagePath("app/sdm/foto-profil/{$berkas_foto_profil}");

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->file($jalur, [
            'Cache-Control' => 'max-age=31536000',
            'Content-Disposition' => 'inline',
            'Content-Type' => $app->files->mimeType($jalur),
        ]);
    }

    public function panduan()
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, 'SDM'), 403, 'Akses dibatasi hanya untuk Pengguna Aplikasi SDM.');

        $storage = $app->filesystem;

        $dokumenPengurus = match ($pengguna->sdm_hak_akses) {
            'SDM-PENGURUS', 'SDM-MANAJEMEN' => $app->filesystem->directories('sdm/panduan-pengurus'),
            default => null
        };

        $dokumenUmum = $storage->directories('sdm/panduan-umum');

        $data = [
            'dokumenUmum' => $dokumenUmum,
            'dokumenPengurus' => $dokumenPengurus,
        ];

        $HtmlPenuh = $app->view->make('sdm.dokumen-resmi', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }

    public function eksporExcelStream($namaBerkas, $dataEkspor, $pengecualian, $pesanData, $app, $binder, $spreadsheet, $worksheet)
    {
        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');

        $filename = $namaBerkas . date('YmdHis') . '.xlsx';
        Cell::setValueBinder($binder);
        $x = 1;

        $dataEkspor->chunk(100, function ($hasil) use (&$x, $worksheet, $pengecualian, $pesanData) {
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

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->setPreCalculateFormulas(false);
        $writer->save($app->storagePath("app/unduh/{$filename}"));
        $spreadsheet->disconnectWorksheets();

        unset($spreadsheet);

        echo '<p>Status : Menyiapkan berkas excel.</p>';

        return $app->redirect->to($app->filesystem->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)));

        exit();
    }

    public function contohUnggahProfilSDM()
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $storage = $app->filesystem;

        $berkasContoh = 'unggah-umum.xlsx';

        abort_unless($storage->exists("contoh/{$berkasContoh}"), 404, 'Berkas Contoh Ekspor Tidak Ditemukan.');

        $database = $app->db;

        $kontrak = $database->query()->select('penempatan_no_absen', 'penempatan_lokasi')
            ->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
                $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
            });

        $lingkup = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $dataEkspor = $database->query()->select('sdm_no_permintaan', 'sdm_no_absen', 'sdm_tgl_gabung', 'sdm_warganegara', 'sdm_no_ktp', 'sdm_nama', 'sdm_tempat_lahir', 'sdm_tgl_lahir', 'sdm_kelamin', 'sdm_gol_darah', 'sdm_alamat', 'sdm_alamat_rt', 'sdm_alamat_rw', 'sdm_alamat_kelurahan', 'sdm_alamat_kecamatan', 'sdm_alamat_kota', 'sdm_alamat_provinsi', 'sdm_alamat_kodepos', 'sdm_agama', 'sdm_no_kk', 'sdm_status_kawin', 'sdm_jml_anak', 'sdm_pendidikan', 'sdm_jurusan', 'sdm_telepon', 'email', 'sdm_disabilitas', 'sdm_no_bpjs', 'sdm_no_jamsostek', 'sdm_no_npwp', 'sdm_nama_bank', 'sdm_cabang_bank', 'sdm_rek_bank', 'sdm_an_rek', 'sdm_nama_dok', 'sdm_nomor_dok', 'sdm_penerbit_dok', 'sdm_an_dok', 'sdm_kadaluarsa_dok', 'sdm_uk_seragam', 'sdm_uk_sepatu', 'sdm_ket_kary', 'sdm_tgl_berhenti', 'sdm_jenis_berhenti', 'sdm_ket_berhenti', 'sdm_id_atasan')
            ->from('sdms')
            ->leftJoinSub($kontrak, 'kontrak', function ($join) {
                $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->whereNull('sdm_tgl_berhenti')
            ->when($lingkup, function ($c) use ($lingkup) {
                return $c->whereIn('penempatan_lokasi', [null, ...$lingkup]);
            })
            ->orderBy('sdm_no_absen');

        $binder = new StringValueBinder();
        $reader = IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($app->storagePath("app/contoh/{$berkasContoh}"));
        $worksheet = $spreadsheet->getSheet(1);

        $argumen = [
            'namaBerkas' => 'unggahprofilsdm-',
            'dataEkspor' => $dataEkspor,
            'pengecualian' => ['penempatan_lokasi'],
            'pesanData' =>  ' data profil SDM',
            'app' => $app,
            'binder' => $binder,
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet
        ];

        return $this->eksporExcelStream(...$argumen);
    }

    public function contohUnggahPosisiSDM(Posisi $posisi)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $storage = $app->filesystem;

        $berkasContoh = 'unggah-umum.xlsx';

        abort_unless($storage->exists("contoh/{$berkasContoh}"), 404, 'Berkas Contoh Ekspor Tidak Ditemukan.');

        $binder = new CustomValueBinder();
        $reader = IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($app->storagePath("app/contoh/{$berkasContoh}"));
        $worksheet = $spreadsheet->getSheet(1);

        $argumen = [
            'namaBerkas' => 'unggahjabatansdm-',
            'dataEkspor' => $posisi->dataDasar()->clone()->latest('posisi_dibuat'),
            'pengecualian' => ['posisi_uuid'],
            'pesanData' =>  ' data jabatan SDM',
            'app' => $app,
            'binder' => $binder,
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet
        ];

        return $this->eksporExcelStream(...$argumen);
    }

    public function unduhKartuSDM($uuid = null)
    {
        $app = app();
        $pengguna = $app->request->user();
        abort_unless($pengguna && $uuid, 401);

        $database = $app->db;
        $storage = $app->filesystem;

        $penem = $database->query()->select('penempatan_no_absen', 'penempatan_lokasi', 'penempatan_mulai')->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
            $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
        });

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $ijin_akses));

        $akun = $database->query()->select('sdm_uuid', 'sdm_no_absen', 'sdm_nama', 'sdm_telepon', 'email', 'penempatan_lokasi')
            ->from('sdms')
            ->leftJoinSub($penem, 'kontrak', function ($join) {
                $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->where('sdm_uuid', $uuid)
            ->first();

        $no_absen_sdm = $pengguna->sdm_no_absen;
        $no_absen = $akun?->sdm_no_absen;
        $lingkup_lokasi = $akun?->penempatan_lokasi;
        $lingkup_akses = collect($lingkup_lokasi)->intersect($lingkup)->count();

        abort_unless(blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0) || ($no_absen_sdm == $no_absen), 403, 'Akses pengguna dibatasi.');

        $namaSdm = $akun->sdm_nama;
        $str = str();

        $batasi = $str->limit($namaSdm, 20, '');
        $sukuKata = preg_split('/\s/', $batasi, -1, PREG_SPLIT_NO_EMPTY);
        $kataAkhir = ' ' . $str->limit(end($sukuKata), 1, '');

        if ($batasi === $namaSdm) {
            $namaKartu = $namaSdm;
        } elseif (count($sukuKata) === 1) {
            $namaKartu = $sukuKata[0];
        } else {
            $namaKartu = $str->finish(implode(" ", array_slice($sukuKata, 0, -1)), $kataAkhir);
        }

        $nama = $str->of($namaKartu)->before(',')->title();
        $kontak = 'BEGIN:VCARD' . "\n";
        $kontak .= 'VERSION:2.1' . "\n";
        $kontak .= 'FN:' . $nama . "\n";
        $kontak .= 'TEL;WORK;VOICE:' . $akun->sdm_telepon . "\n";
        $kontak .= 'EMAIL:' . $akun->email . "\n";
        $kontak .= 'END:VCARD';
        $outerFrame = 4;

        include($app->path('Tambahan/PHPQRCode/qrlib.php'));
        $frame = QRcode::text($kontak, false, QR_ECLEVEL_M);

        $h = count($frame);
        $w = strlen($frame[0]);
        $imgW = $w + 2 * $outerFrame;
        $imgH = $h + 2 * $outerFrame;
        $base_image = imagecreate($imgW, $imgH);
        $col[0] = imagecolorallocate($base_image, 255, 255, 255);
        $col[1] = imagecolorallocate($base_image, 0, 0, 0);

        imagefill($base_image, 0, 0, $col[0]);

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                if ($frame[$y][$x] == '1') {
                    imagesetpixel($base_image, $x + $outerFrame, $y + $outerFrame, $col[1]);
                }
            }
        }

        $target_image = imagecreate(246, 246);

        imagecopyresized($target_image, $base_image, 0, 0, 0, 0, 246, 246, $imgW, $imgH);
        imagedestroy($base_image);

        if ($storage->exists("sdm/kartu-identitas-digital/{$akun->penempatan_lokasi}.jpg")) {
            $dest = imagecreatefromjpeg($app->storagePath("app/sdm/kartu-identitas-digital/{$akun->penempatan_lokasi}.jpg"));
        } else {
            $dest = imagecreatefromjpeg($app->storagePath("app/sdm/kartu-identitas-digital/KKA-WR.jpg"));
        }

        if ($storage->exists("sdm/foto-profil/{$no_absen}.webp")) {
            $src = imagecreatefromwebp($app->storagePath("app/sdm/foto-profil/{$no_absen}.webp"));
        } else {
            $src = imagecreatefromjpeg($app->storagePath("app/sdm/foto-profil/blank.jpg"));
        }

        $color = imagecolorallocatealpha($dest, 0, 0, 0, 13);

        $font = $app->resourcePath('css\font\Roboto-Regular.ttf');
        $font2 = $app->resourcePath('css\font\Roboto-Medium.ttf');

        $box = imagettfbbox(32, 0, $font, $no_absen);
        $text_width = abs($box[2]) - abs($box[0]);
        $x = (550 - $text_width) / 2;

        $box2 = imagettfbbox(37.5, 0, $font2, $nama);
        $text_width2 = abs($box2[2]) - abs($box2[0]);
        $x2 = (550 - $text_width2) / 2;

        imagettftext($dest, 32, 0, $x, 660, $color, $font, $no_absen);
        imagettftext($dest, 37.5, 0, $x2, 720, $color, $font2, $nama);
        imagecopymerge($dest, $src, 125, 200, 0, 0, 300, 400, 100);
        imagecopymerge($dest,  $target_image, 835, 480, 0, 0, 246, 246, 100);

        header('Content-Type: image/jpg');
        header('Content-Disposition: attachment; filename=kartu-sdm-' . $no_absen . '.jpg');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Expires: 0');
        header('Pragma: no-cache');

        imagejpeg($dest);
        imagedestroy($dest);
        imagedestroy($src);
        imagedestroy($target_image);
        exit();
    }

    public function unduhIndexPosisiSDMExcel($cari, $reqs, $app)
    {
        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $binder = new CustomValueBinder();
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporjabatansdm-',
            'dataEkspor' => $cari->clone(),
            'pengecualian' => ['posisi_uuid'],
            'pesanData' =>  ' data jabatan SDM',
            'app' => $app,
            'binder' => $binder,
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet
        ];

        return $this->eksporExcelStream(...$argumen);
    }

    public function unduhIndexPermintaanTambahSDMExcel($cari, $reqs, $app)
    {
        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $binder = new CustomValueBinder();
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporpermintaansdm-',
            'dataEkspor' => $cari->clone(),
            'pengecualian' => ['sdm_uuid', 'tambahsdm_uuid'],
            'pesanData' =>  ' data permintaan tambah SDM',
            'app' => $app,
            'binder' => $binder,
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet
        ];

        return $this->eksporExcelStream(...$argumen);
    }

    public function formulirSerahTerimaSDMBaru($uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();

        abort_unless($pengguna && $uuid, 401);

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $database = $app->db;

        $penem = $database->query()->select('penempatan_no_absen', 'penempatan_lokasi', 'penempatan_mulai')->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
            $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
        });

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $ijin_akses));

        $akun = $database->query()->select('sdm_no_absen', 'sdm_tgl_gabung', 'sdm_nama', 'penempatan_lokasi')
            ->from('sdms')
            ->leftJoinSub($penem, 'kontrak', function ($join) {
                $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->where('sdm_uuid', $uuid)
            ->first();

        $no_absen_sdm = $pengguna->sdm_no_absen;
        $lingkup_lokasi = $akun?->penempatan_lokasi;
        $lingkup_akses = collect($lingkup_lokasi)->intersect($lingkup)->count();

        abort_unless((blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0)) && ($no_absen_sdm !== $akun->sdm_no_absen), 403, 'Akses pengguna dibatasi.');

        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');

        echo '<p>Memeriksa formulir.</p>';

        $storage = $app->filesystem;
        $date = $app->date;

        abort_unless($storage->exists("contoh/serah-terima-sdm-baru.docx"), 404, 'Berkas Contoh Formulir Tidak Ditemukan.');

        $filename = 'serah-terima-sdm-baru-' . $akun->sdm_no_absen . '.docx';

        $templateProcessor = new TemplateProcessor($app->storagePath('app/contoh/serah-terima-sdm-baru.docx'));

        echo '<p>Menyiapkan formulir.</p>';

        $templateProcessor->setValues([
            'sdm_nama' => str($akun->sdm_nama)->limit(30),
            'sdm_no_absen' => $akun->sdm_no_absen,
            'sdm_tgl_gabung' => strtoupper($date->make($akun->sdm_tgl_gabung)?->translatedFormat('d F Y'))
        ]);

        $templateProcessor->saveAs($app->storagePath("app/unduh/{$filename}"));

        return $app->redirect->to($storage->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)));
    }

    public function formulirPelepasanSDM($uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();

        abort_unless($pengguna && $uuid, 401);
        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $database = $app->db;

        $penem = $database->query()->select('penempatan_no_absen', 'penempatan_lokasi', 'penempatan_mulai')->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
            $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
        });

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $ijin_akses));

        $akun = $database->query()->select('sdm_no_absen', 'sdm_tgl_berhenti', 'sdm_nama', 'sdm_jenis_berhenti', 'penempatan_lokasi')
            ->from('sdms')
            ->leftJoinSub($penem, 'kontrak', function ($join) {
                $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->where('sdm_uuid', $uuid)
            ->first();

        $no_absen_sdm = $pengguna->sdm_no_absen;
        $lingkup_lokasi = $akun?->penempatan_lokasi;
        $lingkup_akses = collect($lingkup_lokasi)->intersect($lingkup)->count();

        abort_unless((blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0)) && ($no_absen_sdm !== $akun->sdm_no_absen), 403, 'Akses pengguna dibatasi.');

        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');

        echo '<p>Memeriksa formulir.</p>';

        $storage = $app->filesystem;
        $date = $app->date;

        abort_unless($storage->exists("contoh/pelepasan-karyawan.docx"), 404, 'Berkas Contoh Formulir Tidak Ditemukan.');

        $filename = 'pelepasan-karyawan-' . $akun->sdm_no_absen . '.docx';

        $templateProcessor = new TemplateProcessor($app->storagePath('app/contoh/pelepasan-karyawan.docx'));

        echo '<p>Menyiapkan formulir.</p>';

        $templateProcessor->setValues([
            'sdm_nama' => str($akun->sdm_nama)->limit(30),
            'sdm_no_absen' => $akun->sdm_no_absen,
            'sdm_tgl_berhenti' => strtoupper($date->make($akun->sdm_tgl_berhenti)?->translatedFormat('d F Y')),
            'sdm_jenis_berhenti' => $akun->sdm_jenis_berhenti,
        ]);

        $templateProcessor->saveAs($app->storagePath("app/unduh/{$filename}"));

        return $app->redirect->to($storage->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)));
    }

    public function formulirPermintaanTambahSDM(PermintaanTambahSDM $permintaanTambahSDM, $uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');

        echo '<p>Memeriksa formulir.</p>';

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $permin = $permintaanTambahSDM->dataDasar()->clone()->addSelect('tambahsdm_uuid', 'b.sdm_nama')
            ->join('sdms as b', 'tambahsdm_sdm_id', '=', 'b.sdm_no_absen')->where('tambahsdm_uuid', $uuid)->when($lingkupIjin, function ($query, $lingkupIjin) {
                $query->whereIn('tambahsdm_penempatan', $lingkupIjin);
            })->first();

        abort_unless($permin, 404, 'Data Permintaan Tambah SDM tidak ditemukan.');

        $storage = $app->filesystem;

        abort_unless($storage->exists("contoh/permintaan-tambah-sdm.docx"), 404, 'Berkas Contoh Formulir Tidak Ditemukan.');

        $filename = 'permintaan-tambah-sdm-' . $permin->tambahsdm_no . '.docx';
        // \PhpOffice\PhpWord\Settings::setZipClass(\PhpOffice\PhpWord\Settings::PCLZIP);

        $templateProcessor = new TemplateProcessor($app->storagePath('app/contoh/permintaan-tambah-sdm.docx'));

        echo '<p>Menyiapkan formulir.</p>';

        $date = $app->date;
        $str = str();

        $templateProcessor->setValues([
            'tambahsdm_no' => $permin->tambahsdm_no,
            'sdm_nama' => $str->limit($permin->sdm_nama, 30),
            'tambahsdm_sdm_id' => $permin->tambahsdm_sdm_id,
            'tambahsdm_posisi' => $str->limit($permin->tambahsdm_posisi, 30),
            'tambahsdm_jumlah' => $permin->tambahsdm_jumlah,
            'tambahsdm_alasan' => $str->limit($permin->tambahsdm_alasan, 100),
            'tambahsdm_tgl_diusulkan' => strtoupper($date->make($permin->tambahsdm_tgl_diusulkan)?->translatedFormat('d F Y')),
            'tambahsdm_tgl_dibutuhkan' => strtoupper($date->make($permin->tambahsdm_tgl_dibutuhkan)?->translatedFormat('d F Y'))
        ]);

        $templateProcessor->saveAs($app->storagePath("app/unduh/{$filename}"));

        echo '<p>Selesai menyiapkan berkas formulir. <a href="' . $storage->disk('local')->temporaryUrl("unduh/{$filename}", $date->now()->addMinutes(5)) . '">Unduh</a>.</p>';

        exit();
    }

    public function formulirTTDokumenTitipan($uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();

        abort_unless($pengguna && $uuid, 401);
        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $database = $app->db;

        $penem = $database->query()->select('penempatan_no_absen', 'penempatan_lokasi', 'penempatan_mulai')->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
            $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
        });

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $ijin_akses));

        $akun = $database->query()->select('sdm_no_absen', 'sdm_tgl_gabung', 'sdm_nama', 'sdm_tempat_lahir', 'sdm_kelamin', 'sdm_tgl_lahir', 'sdm_alamat', 'sdm_alamat_kelurahan', 'sdm_alamat_kecamatan', 'sdm_alamat_kota', 'sdm_alamat_provinsi', 'sdm_nama_dok', 'sdm_nomor_dok', 'sdm_penerbit_dok', 'sdm_an_dok', 'sdm_kadaluarsa_dok', 'penempatan_lokasi')
            ->from('sdms')
            ->leftJoinSub($penem, 'kontrak', function ($join) {
                $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->where('sdm_uuid', $uuid)
            ->first();

        $no_absen_sdm = $pengguna->sdm_no_absen;
        $lingkup_lokasi = $akun?->penempatan_lokasi;
        $lingkup_akses = collect($lingkup_lokasi)->intersect($lingkup)->count();

        abort_unless((blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0)) && ($no_absen_sdm !== $akun->sdm_no_absen), 403, 'Akses pengguna dibatasi.');

        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');

        echo '<p>Memeriksa formulir.</p>';

        $storage = $app->filesystem;
        $date = $app->date;

        abort_unless($storage->exists("contoh/tanda-terima-dokumen-titipan.docx"), 404, 'Berkas Contoh Formulir Tidak Ditemukan.');

        $filename = 'tanda-terima-dokumen-titipan-' . $akun->sdm_no_absen . '.docx';

        $templateProcessor = new TemplateProcessor($app->storagePath('app/contoh/tanda-terima-dokumen-titipan.docx'));

        echo '<p>Menyiapkan formulir.</p>';

        $templateProcessor->setValues([
            'sdm_nama' => $akun->sdm_nama,
            'sdm_no_absen' => $akun->sdm_no_absen,
            'sdm_tgl_gabung' => strtoupper($date->make($akun->sdm_tgl_gabung)?->translatedFormat('d F Y')),
            'sdm_tgl_lahir' => $akun->sdm_tempat_lahir . ', ' . strtoupper($date->make($akun->sdm_tgl_lahir)?->translatedFormat('d F Y')),
            'sdm_kelamin' => $akun->sdm_kelamin == 'L' ? 'LAKI - LAKI' : 'PEREMPUAN',
            'sdm_alamat' => "{$akun->sdm_alamat}, {$akun->sdm_alamat_kelurahan}, {$akun->sdm_alamat_kecamatan}, {$akun->sdm_alamat_kota}, {$akun->sdm_alamat_provinsi}.",
            'sdm_nama_dok' => $akun->sdm_nama_dok,
            'sdm_nomor_dok' => $akun->sdm_nomor_dok,
            'sdm_penerbit_dok' => $akun->sdm_penerbit_dok,
            'sdm_an_dok' => $akun->sdm_an_dok,
            'sdm_kadaluarsa_dok' => strtoupper($date->make($akun->sdm_kadaluarsa_dok)?->translatedFormat('d F Y')),
        ]);

        $templateProcessor->saveAs($app->storagePath("app/unduh/{$filename}"));

        return $app->redirect->to($storage->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)));
    }

    public function formulirTTInventaris($uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();

        abort_unless($pengguna && $uuid, 401);
        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $database = $app->db;

        $penem = $database->query()->select('penempatan_no_absen', 'penempatan_lokasi', 'penempatan_mulai')->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
            $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
        });

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $ijin_akses));

        $akun = $database->query()->select('sdm_no_absen', 'sdm_nama', 'sdm_uk_seragam', 'sdm_uk_sepatu', 'penempatan_lokasi')
            ->from('sdms')
            ->leftJoinSub($penem, 'kontrak', function ($join) {
                $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->where('sdm_uuid', $uuid)
            ->first();

        $no_absen_sdm = $pengguna->sdm_no_absen;
        $lingkup_lokasi = $akun?->penempatan_lokasi;
        $lingkup_akses = collect($lingkup_lokasi)->intersect($lingkup)->count();

        abort_unless((blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0)) && ($no_absen_sdm !== $akun->sdm_no_absen), 403, 'Akses pengguna dibatasi.');

        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');

        $storage = $app->filesystem;
        $date = $app->date;

        echo '<p>Memeriksa formulir.</p>';


        abort_unless($storage->exists("contoh/tanda-terima-inventaris.docx"), 404, 'Berkas Contoh Formulir Tidak Ditemukan.');

        $filename = 'tanda-terima-inventaris-' . $akun->sdm_no_absen . '.docx';

        $templateProcessor = new TemplateProcessor($app->storagePath('app/contoh/tanda-terima-inventaris.docx'));

        echo '<p>Menyiapkan formulir.</p>';

        $templateProcessor->setValues([
            'sdm_uk_seragam' => $akun->sdm_uk_seragam,
            'sdm_uk_sepatu' => $akun->sdm_uk_sepatu,
            'ket_tanda_terima' => "UNTUK KARYAWAN A.N {$akun->sdm_nama} - ({$akun->sdm_no_absen})",
        ]);

        $templateProcessor->saveAs($app->storagePath("app/unduh/{$filename}"));

        return $app->redirect->to($storage->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)));
    }

    public function formulirPersetujuanGaji($uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();

        abort_unless($pengguna && $uuid, 401);
        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $database = $app->db;

        $penem = $database->query()->select('penempatan_no_absen', 'penempatan_lokasi', 'penempatan_mulai')->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
            $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
        });

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $ijin_akses));

        $akun = $database->query()->select('sdm_no_absen', 'sdm_tgl_gabung', 'sdm_nama', 'sdm_tempat_lahir', 'sdm_kelamin', 'sdm_tgl_lahir', 'sdm_alamat', 'sdm_alamat_kelurahan', 'sdm_alamat_kecamatan', 'sdm_alamat_kota', 'sdm_alamat_provinsi', 'penempatan_lokasi')
            ->from('sdms')
            ->leftJoinSub($penem, 'kontrak', function ($join) {
                $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->where('sdm_uuid', $uuid)
            ->first();

        $no_absen_sdm = $pengguna->sdm_no_absen;
        $lingkup_lokasi = $akun?->penempatan_lokasi;
        $lingkup_akses = collect($lingkup_lokasi)->intersect($lingkup)->count();

        abort_unless((blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0)) && ($no_absen_sdm !== $akun->sdm_no_absen), 403, 'Akses pengguna dibatasi.');

        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');

        echo '<p>Memeriksa formulir.</p>';

        $storage = $app->filesystem;
        $date = $app->date;

        abort_unless($storage->exists("contoh/persetujuan-gaji.docx"), 404, 'Berkas Contoh Formulir Tidak Ditemukan.');

        $filename = 'persetujuan-gaji-' . $akun->sdm_no_absen . '.docx';

        $templateProcessor = new TemplateProcessor($app->storagePath('app/contoh/persetujuan-gaji.docx'));

        echo '<p>Menyiapkan formulir.</p>';

        $templateProcessor->setValues([
            'sdm_nama' => $akun->sdm_nama,
            'sdm_no_absen' => $akun->sdm_no_absen,
            'sdm_tgl_gabung' => strtoupper($date->make($akun->sdm_tgl_gabung)?->translatedFormat('d F Y')),
            'sdm_tgl_lahir' => $akun->sdm_tempat_lahir . ', ' . strtoupper($date->make($akun->sdm_tgl_lahir)?->translatedFormat('d F Y')),
            'sdm_kelamin' => $akun->sdm_kelamin == 'L' ? 'LAKI - LAKI' : 'PEREMPUAN',
            'sdm_alamat' => "{$akun->sdm_alamat}, {$akun->sdm_alamat_kelurahan}, {$akun->sdm_alamat_kecamatan}, {$akun->sdm_alamat_kota}, {$akun->sdm_alamat_provinsi}.",
        ]);

        $templateProcessor->saveAs($app->storagePath("app/unduh/{$filename}"));

        return $app->redirect->to($storage->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)));
    }

    public function rekamHapusDataPermintaanSDM($app, $dataHapus)
    {
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
    }

    public function suratKeteranganSDM($uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();

        abort_unless($pengguna && $uuid, 401);
        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $database = $app->db;

        $penem = $database->query()->select('penempatan_no_absen', 'penempatan_lokasi', 'penempatan_mulai')->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
            $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
        });

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $ijin_akses));

        $akun = $database->query()->select('sdm_no_absen', 'sdm_tgl_berhenti', 'sdm_tgl_gabung', 'sdm_nama', 'sdm_tempat_lahir', 'sdm_kelamin', 'sdm_tgl_lahir', 'sdm_alamat', 'sdm_alamat_kelurahan', 'sdm_alamat_kecamatan', 'sdm_alamat_kota', 'sdm_alamat_provinsi', 'penempatan_lokasi')
            ->from('sdms')
            ->leftJoinSub($penem, 'kontrak', function ($join) {
                $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->where('sdm_uuid', $uuid)
            ->first();

        $no_absen_sdm = $pengguna->sdm_no_absen;
        $lingkup_lokasi = $akun?->penempatan_lokasi;
        $lingkup_akses = collect($lingkup_lokasi)->intersect($lingkup)->count();

        abort_unless((blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0)) && ($no_absen_sdm !== $akun->sdm_no_absen), 403, 'Akses pengguna dibatasi.');

        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');

        echo '<p>Memeriksa formulir.</p>';

        $storage = $app->filesystem;
        $date = $app->date;

        abort_unless($storage->exists("contoh/keterangan-kerja.docx"), 404, 'Berkas Contoh Formulir Tidak Ditemukan.');

        $filename = 'keterangan-kerja-' . $akun->sdm_no_absen . '.docx';

        $templateProcessor = new TemplateProcessor($app->storagePath('app/contoh/keterangan-kerja.docx'));

        echo '<p>Menyiapkan formulir.</p>';

        $templateProcessor->setValues([
            'sdm_nama' => $akun->sdm_nama,
            'sdm_no_absen' => $akun->sdm_no_absen,
            'sdm_tgl_gabung' => strtoupper($date->make($akun->sdm_tgl_gabung)?->translatedFormat('d F Y')),
            'sdm_tgl_lahir' => $akun->sdm_tempat_lahir . ', ' . strtoupper($date->make($akun->sdm_tgl_lahir)?->translatedFormat('d F Y')),
            'sdm_kelamin' => $akun->sdm_kelamin == 'L' ? 'LAKI - LAKI' : 'PEREMPUAN',
            'sdm_alamat' => "{$akun->sdm_alamat}, {$akun->sdm_alamat_kelurahan}, {$akun->sdm_alamat_kecamatan}, {$akun->sdm_alamat_kota}, {$akun->sdm_alamat_provinsi}.",
            'sdm_tgl_berhenti' => strtoupper($date->make($akun->sdm_tgl_berhenti)?->translatedFormat('d F Y')) ?: 'saat surat ini diterbitkan',
            'paragraf_keterangan' => $akun->sdm_tgl_berhenti ? 'PT. Kepuh Kencana Arum mengapresiasi kontribusi dan dedikasi yang diberikan, selama bekerja yang bersangkutan menunjukkan kinerja yang baik untuk menunjang kesuksesan kerjanya' : 'Surat keterangan ini dibuat untuk ...[isi keterangan]',
        ]);

        $templateProcessor->saveAs($app->storagePath("app/unduh/{$filename}"));

        return $app->redirect->to($storage->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)));
    }

    public function unggahPosisiSDM()
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

                $data = array_combine(range(($startRow - 1), count($dataexcel) + ($startRow - 2)), array_values($dataexcel));

                $validasi = $validator->make(
                    $data,
                    [
                        '*.posisi_nama' => ['required', 'string', 'max:40'],
                        '*.posisi_atasan' => ['nullable', 'string', 'max:40', 'different:*.posisi_nama'],
                        '*.posisi_wlkp' => ['nullable'],
                        '*.posisi_status' => ['required', 'string', $kategori_status],
                        '*.posisi_keterangan' => ['nullable'],
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
                        '*.posisi_keterangan.*' => 'Kode Jabatan WLKP baris ke-:position wajib diisi, berupa karakter.',
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
                    ['posisi_wlkp', 'posisi_status', 'posisi_keterangan', 'posisi_id_pengunggah', 'posisi_diunggah', 'posisi_id_pengubah']
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
