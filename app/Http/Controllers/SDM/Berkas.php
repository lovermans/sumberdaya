<?php

namespace App\Http\Controllers\SDM;

use QRcode;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class Berkas
{
    public function berkas($berkas = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        
        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');
        
        abort_unless($berkas && $app->filesystem->exists("sdm/berkas/{$berkas}"), 404, 'Berkas tidak ditemukan.');
        
        $jalur = $app->storagePath("app/sdm/berkas/{$berkas}");
        
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

    public function contohUnggah() {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();
        
        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $storage = $app->filesystem;
        
        abort_unless($storage->exists("contoh/unggah-umum.xlsx"), 404, 'Berkas Contoh Ekspor Tidak Ditemukan.');
        
        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');
        
        $reader = IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($app->storagePath('app/contoh/unggah-umum.xlsx'));
        $filename = 'unggahprofilsdm-' . date('YmdHis') . '.xlsx';
        Cell::setValueBinder(new StringValueBinder());
        $worksheet = $spreadsheet->getSheet(1);
        $x = 1;

        $database = $app->db;

        $kontrak = $database->query()->select('penempatan_no_absen', 'penempatan_lokasi')
        ->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
            $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
        });

        $lingkup = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $database->query()->select('sdm_no_permintaan', 'sdm_no_absen', 'sdm_tgl_gabung', 'sdm_warganegara', 'sdm_no_ktp', 'sdm_nama', 'sdm_tempat_lahir', 'sdm_tgl_lahir', 'sdm_kelamin', 'sdm_gol_darah', 'sdm_alamat', 'sdm_alamat_rt', 'sdm_alamat_rw', 'sdm_alamat_kelurahan', 'sdm_alamat_kecamatan', 'sdm_alamat_kota', 'sdm_alamat_provinsi', 'sdm_alamat_kodepos', 'sdm_agama', 'sdm_no_kk', 'sdm_status_kawin', 'sdm_jml_anak', 'sdm_pendidikan', 'sdm_jurusan', 'sdm_telepon', 'email', 'sdm_disabilitas', 'sdm_no_bpjs', 'sdm_no_jamsostek', 'sdm_no_npwp', 'sdm_nama_bank', 'sdm_cabang_bank', 'sdm_rek_bank', 'sdm_an_rek', 'sdm_nama_dok', 'sdm_nomor_dok', 'sdm_penerbit_dok', 'sdm_an_dok', 'sdm_kadaluarsa_dok', 'sdm_uk_seragam', 'sdm_uk_sepatu', 'sdm_ket_kary', 'sdm_tgl_berhenti', 'sdm_jenis_berhenti', 'sdm_ket_berhenti', 'sdm_id_atasan')
        ->from('sdms')
        ->leftJoinSub($kontrak, 'kontrak', function ($join) {
            $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
        })
        ->whereNull('sdm_tgl_berhenti')
        ->when($lingkup, function ($c) use ($lingkup) {
            return $c->whereIn('penempatan_lokasi', [null, ...$lingkup]);
        })
        ->orderBy('sdm_no_absen')->chunk(100, function ($hasil) use (&$x, $worksheet) {
            if ($x == 1) {
                $list = $hasil->map(function ($x) {
                    return collect($x)->except(['penempatan_lokasi']);
                })->toArray();
                array_unshift($list, array_keys($list[0]));
                $worksheet->fromArray($list, NULL, 'A' . $x);
                $x++;
            } else {
                $list = $hasil->map(function ($x) {
                    return collect($x)->except(['penempatan_lokasi']);
                })->toArray();
                $worksheet->fromArray($list, NULL, 'A' . $x);
            };
            $x += count($hasil);
            echo '<p>Status : Memproses ' . ($x - 2) . ' data profil SDM.</p>';
        });
        
        echo '<p>Status : Menyiapkan berkas excel.</p>';
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->setPreCalculateFormulas(false);
        $writer->save($app->storagePath("app/unduh/{$filename}"));
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        
        return $app->redirect->to($storage->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)));
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
        $kataAkhir = ' '.$str->limit(end($sukuKata), 1, '');
        
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
        
        $filename = 'serah-terima-sdm-baru-'.$akun->sdm_no_absen.'.docx';
        
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
        
        $filename = 'pelepasan-karyawan-'.$akun->sdm_no_absen.'.docx';
        
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
        
        $filename = 'tanda-terima-dokumen-titipan-'.$akun->sdm_no_absen.'.docx';
        
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
        
        $filename = 'tanda-terima-inventaris-'.$akun->sdm_no_absen.'.docx';
        
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
        
        $filename = 'persetujuan-gaji-'.$akun->sdm_no_absen.'.docx';
        
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
        
        $filename = 'keterangan-kerja-'.$akun->sdm_no_absen.'.docx';
        
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
}
