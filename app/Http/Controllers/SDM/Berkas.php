<?php

namespace App\Http\Controllers\SDM;

use QRcode;
use App\Tambahan\FungsiStatis;
use Illuminate\Validation\Rule;
use App\Tambahan\ChunkReadFilter;
use App\Tambahan\CustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Http\Controllers\SDM\PermintaanTambahSDM;
use App\Http\Controllers\SDM\Penempatan;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;

class Berkas
{
    public function berkas($berkas = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $permintaanBerkas = urldecode($reqs->path());

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

    public function contohUnggahPosisiSDM()
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

        $cari = $app->db->query()->select('posisi_nama', 'posisi_atasan', 'posisi_wlkp', 'posisi_status', 'posisi_keterangan')->from('posisis')->orderBy('id');

        $argumen = [
            'namaBerkas' => 'unggahjabatansdm-',
            'dataEkspor' => $cari,
            'pengecualian' => ['posisi_uuid'],
            'pesanData' =>  ' data jabatan SDM',
            'app' => $app,
            'binder' => $binder,
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet
        ];

        return $this->eksporExcelStream(...$argumen);
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
        $reader = IOFactory::createReader('Xlsx');
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

    public function contohUnggahSanksiSDM()
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
        $reader = IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($app->storagePath("app/contoh/{$berkasContoh}"));
        $worksheet = $spreadsheet->getSheet(1);

        $database = $app->db;

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $kontrak = $database->query()->select('penempatan_uuid', 'penempatan_no_absen', 'penempatan_posisi', 'penempatan_lokasi', 'penempatan_kontrak', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_keterangan')
            ->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
                $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
            });

        $cari = $database->query()->select('sdm_nama', 'sanksi_no_absen', 'sanksi_jenis', 'sanksi_mulai', 'sanksi_selesai', 'sanksi_lap_no', 'sanksi_tambahan', 'sanksi_keterangan')
            ->from('sanksisdms')
            ->join('sdms', 'sanksi_no_absen', '=', 'sdm_no_absen')
            ->leftJoinSub($kontrak, 'kontrak_t', function ($join) {
                $join->on('sanksi_no_absen', '=', 'kontrak_t.penempatan_no_absen');
            })
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->whereIn('kontrak_t.penempatan_lokasi', $lingkupIjin);
                });
            })
            ->orderBy('sanksisdms.id');

        $argumen = [
            'namaBerkas' => 'unggahsanksinsdm-',
            'dataEkspor' => $cari->clone(),
            'pengecualian' => ['id', 'sanksi_lap_no'],
            'pesanData' =>  ' data sanksi SDM',
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

    public function unduhIndexPosisiSDMExcel($cari, $app)
    {
        abort_unless($app->request->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

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

    public function unduhIndexPermintaanTambahSDMExcel($cari, $app)
    {
        abort_unless($app->request->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

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

    public function unduhIndexPenempatanSDM($cari, $app)
    {
        abort_unless($app->request->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $binder = new CustomValueBinder();
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporriwayatpenempatan-',
            'dataEkspor' => $cari->clone(),
            'pengecualian' => ['id', 'sdm_uuid', 'penempatan_uuid'],
            'pesanData' =>  ' data riwayat penempatan SDM',
            'app' => $app,
            'binder' => $binder,
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 500
        ];

        return $this->eksporExcelStream(...$argumen);
    }

    public function unduhIndexMasaKerjaNyata($cari, $app)
    {
        abort_unless($app->request->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $binder = new CustomValueBinder();
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'ekspormasakerjanyata-',
            'dataEkspor' => $cari->clone(),
            'pengecualian' => ['id', 'sdm_uuid', 'penempatan_uuid'],
            'pesanData' =>  ' data riwayat penempatan SDM',
            'app' => $app,
            'binder' => $binder,
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 500
        ];

        return $this->eksporExcelStream(...$argumen);
    }

    public function unduhIndexPenempatanSDMAktif($cari, $app)
    {
        abort_unless($app->request->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $binder = new CustomValueBinder();
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporsdmaktif-',
            'dataEkspor' => $cari->clone(),
            'pengecualian' => ['id', 'sdm_uuid', 'penempatan_uuid'],
            'pesanData' =>  ' data riwayat penempatan SDM',
            'app' => $app,
            'binder' => $binder,
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 500
        ];

        return $this->eksporExcelStream(...$argumen);
    }

    public function unduhIndexPenempatanSDMNonAktif($cari, $app)
    {
        abort_unless($app->request->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $binder = new CustomValueBinder();
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporsdmnonaktif-',
            'dataEkspor' => $cari->clone(),
            'pengecualian' => ['id', 'sdm_uuid', 'penempatan_uuid'],
            'pesanData' =>  ' data riwayat penempatan SDM',
            'app' => $app,
            'binder' => $binder,
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 500
        ];

        return $this->eksporExcelStream(...$argumen);
    }

    public function unduhIndexPenempatanSDMAkanHabis($cari, $app)
    {
        abort_unless($app->request->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $binder = new CustomValueBinder();
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporsdmakanhabis-',
            'dataEkspor' => $cari->clone(),
            'pengecualian' => ['id', 'sdm_uuid', 'penempatan_uuid'],
            'pesanData' =>  ' data riwayat penempatan SDM',
            'app' => $app,
            'binder' => $binder,
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 500
        ];

        return $this->eksporExcelStream(...$argumen);
    }

    public function unduhIndexPenempatanSDMKadaluarsa($cari, $app)
    {
        abort_unless($app->request->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $binder = new CustomValueBinder();
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporsdmkadaluarsa-',
            'dataEkspor' => $cari->clone(),
            'pengecualian' => ['id', 'sdm_uuid', 'penempatan_uuid'],
            'pesanData' =>  ' data riwayat penempatan SDM',
            'app' => $app,
            'binder' => $binder,
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 500
        ];

        return $this->eksporExcelStream(...$argumen);
    }

    public function unduhIndexPenempatanSDMBaru($cari, $app)
    {
        abort_unless($app->request->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $binder = new CustomValueBinder();
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporsdmbaru-',
            'dataEkspor' => $cari->clone(),
            'pengecualian' => ['id', 'sdm_uuid', 'penempatan_uuid'],
            'pesanData' =>  ' data riwayat penempatan SDM',
            'app' => $app,
            'binder' => $binder,
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 500
        ];

        return $this->eksporExcelStream(...$argumen);
    }

    public function unduhIndexPenempatanSDMBatal($cari, $app)
    {
        abort_unless($app->request->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $binder = new CustomValueBinder();
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporsdmbatal-',
            'dataEkspor' => $cari->clone(),
            'pengecualian' => ['id', 'sdm_uuid', 'penempatan_uuid'],
            'pesanData' =>  ' data riwayat penempatan SDM',
            'app' => $app,
            'binder' => $binder,
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 500
        ];

        return $this->eksporExcelStream(...$argumen);
    }

    public function unduhIndexSanksiSDM($cari, $app)
    {
        abort_unless($app->request->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $binder = new CustomValueBinder();
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporsanksisdm-',
            'dataEkspor' => $cari->clone(),
            'pengecualian' => ['sanksi_uuid', 'langgar_tsdm_uuid', 'langgar_psdm_uuid'],
            'pesanData' =>  ' data sanksi SDM',
            'app' => $app,
            'binder' => $binder,
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 100
        ];

        return $this->eksporExcelStream(...$argumen);
    }

    public function unduhIndexPenilaianSDM($cari, $app)
    {
        abort_unless($app->request->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $binder = new CustomValueBinder();
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporpenilaiansdm-',
            'dataEkspor' => $cari->clone(),
            'pengecualian' => ['nilaisdm_uuid'],
            'pesanData' =>  ' data penilaian SDM',
            'app' => $app,
            'binder' => $binder,
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 100
        ];

        return $this->eksporExcelStream(...$argumen);
    }

    public function statistikPenempatanSDM(Penempatan $penempatan)
    {
        $app = app();
        $pengguna = $app->request->user();
        $str = str();

        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $reader = IOFactory::createReader('Xlsx');
        $spreadsheet2 = $reader->load($app->storagePath('app/contoh/statistik-sdm.xlsx'));
        $spreadsheet = new Spreadsheet();
        $binder = new CustomValueBinder();
        $worksheet = $spreadsheet->getActiveSheet();

        // $rumusMasaKerja = '=IF([@sdm_tgl_berhenti]="",DATEDIF([@sdm_tgl_gabung],TODAY(),"Y"),DATEDIF([@sdm_tgl_gabung],[@sdm_tgl_berhenti],"Y"))';
        // $rumusUsia = '=IF([@sdm_tgl_berhenti]="",DATEDIF([@sdm_tgl_lahir],TODAY(),"Y"),DATEDIF([@sdm_tgl_lahir],[@sdm_tgl_berhenti],"Y"))';

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $cari = $penempatan->dataPenempatanTerkini()->clone()->addSelect('posisi_wlkp', 'sdm_uuid', 'sdm_no_absen', 'sdm_tgl_lahir', 'sdm_tgl_gabung', 'sdm_no_ktp', 'sdm_nama', 'sdm_kelamin', 'sdm_tgl_berhenti', 'sdm_jenis_berhenti', 'sdm_ket_berhenti', 'sdm_kelamin', 'sdm_disabilitas', 'sdm_agama', 'sdm_status_kawin', 'sdm_pendidikan', 'sdm_warganegara', $app->db->raw('IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, sdm_tgl_berhenti)) as masa_kerja, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, sdm_tgl_berhenti)) as usia'))
            ->joinSub($penempatan->dataSDM(), 'sdm', function ($join) {
                $join->on('penempatan_no_absen', '=', 'sdm.sdm_no_absen');
            })
            ->leftJoinSub($penempatan->dataPosisi(), 'pos', function ($join) {
                $join->on('penempatan_posisi', '=', 'pos.posisi_nama');
            })
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->whereIn('penempatan_lokasi', $lingkupIjin);
            })
            ->orderBy('sdm.id');

        $argumen = [
            'namaBerkas' => 'statistik-sdm-',
            'dataEkspor' => $cari->clone(),
            'pengecualian' => ['id', 'sdm_uuid', 'penempatan_uuid'],
            'pesanData' =>  ' data statistik penempatan SDM',
            'app' => $app,
            'binder' => $binder,
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 500,
            'tabelStart' => 'A1:',
            'namaTabel' => 'Penempatan',
            'spreadsheet2' => $spreadsheet2
        ];

        return $this->eksporExcelStream(...$argumen);
    }

    public function unduhIndexPelanggaranSDM($cari, $app)
    {
        abort_unless($app->request->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $binder = new CustomValueBinder();
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporpelanggaransdm-',
            'dataEkspor' => $cari->clone(),
            'pengecualian' => ['langgar_uuid', 'langgar_tsdm_uuid', 'langgar_psdm_uuid', 'final_sanksi_uuid
            '],
            'pesanData' =>  ' data laporan pelanggaran SDM',
            'app' => $app,
            'binder' => $binder,
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 500
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

    public function formulirPenilaianSDM(Penempatan $penempatan, $uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $uuid && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $permin = $penempatan->dataDasar()->clone()->addSelect('sdm_nama')
            ->joinSub($penempatan->dataSDM(), 'sdm', function ($join) {
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

    public function formulirPerubahanStatusSDM(Penempatan $penempatan, $uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $uuid && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $permin = $penempatan->dataDasar()->clone()->addSelect('sdm_nama')
            ->joinSub($penempatan->dataSDM(), 'sdm', function ($join) {
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

    public function PKWTSDM(Penempatan $penempatan, $uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $uuid && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $permin = $penempatan->dataDasar()->clone()->addSelect('sdm_nama', 'sdm_tgl_lahir', 'sdm_tempat_lahir', 'sdm_kelamin', 'sdm_alamat', 'sdm_alamat_kelurahan', 'sdm_alamat_kecamatan', 'sdm_alamat_kota', 'sdm_alamat_provinsi')
            ->joinSub($penempatan->dataSDM(), 'sdm', function ($join) {
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

        $filename = 'serah-terima-sdm-baru-' . $akun->sdm_no_absen . '.docx';

        $data = [
            'sdm_nama' => str($akun->sdm_nama)->limit(30),
            'sdm_no_absen' => $akun->sdm_no_absen,
            'sdm_tgl_gabung' => strtoupper($app->date->make($akun->sdm_tgl_gabung)?->translatedFormat('d F Y'))
        ];

        $argumen = [
            'app' => $app,
            'contoh' => 'serah-terima-sdm-baru.docx',
            'data' => $data,
            'filename' => $filename
        ];

        return $this->isiFormulir(...$argumen);
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

        $filename = 'pelepasan-karyawan-' . $akun->sdm_no_absen . '.docx';

        $data = [
            'sdm_nama' => str($akun->sdm_nama)->limit(30),
            'sdm_no_absen' => $akun->sdm_no_absen,
            'sdm_tgl_berhenti' => strtoupper($app->date->make($akun->sdm_tgl_berhenti)?->translatedFormat('d F Y')),
            'sdm_jenis_berhenti' => $akun->sdm_jenis_berhenti,
        ];

        $argumen = [
            'app' => $app,
            'contoh' => 'pelepasan-karyawan.docx',
            'data' => $data,
            'filename' => $filename
        ];

        return $this->isiFormulir(...$argumen);
    }

    public function formulirPermintaanTambahSDM(PermintaanTambahSDM $permintaanTambahSDM, $uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $permin = $permintaanTambahSDM->dataDasar()->clone()->addSelect('tambahsdm_uuid', 'b.sdm_nama')
            ->join('sdms as b', 'tambahsdm_sdm_id', '=', 'b.sdm_no_absen')->where('tambahsdm_uuid', $uuid)->when($lingkupIjin, function ($query, $lingkupIjin) {
                $query->whereIn('tambahsdm_penempatan', $lingkupIjin);
            })->first();

        abort_unless($permin, 404, 'Data Permintaan Tambah SDM tidak ditemukan.');

        $filename = 'permintaan-tambah-sdm-' . $permin->tambahsdm_no . '.docx';

        $date = $app->date;
        $str = str();

        $data = [
            'tambahsdm_no' => $permin->tambahsdm_no,
            'sdm_nama' => $str->limit($permin->sdm_nama, 30),
            'tambahsdm_sdm_id' => $permin->tambahsdm_sdm_id,
            'tambahsdm_posisi' => $str->limit($permin->tambahsdm_posisi, 30),
            'tambahsdm_jumlah' => $permin->tambahsdm_jumlah,
            'tambahsdm_alasan' => $str->limit($permin->tambahsdm_alasan, 100),
            'tambahsdm_tgl_diusulkan' => strtoupper($date->make($permin->tambahsdm_tgl_diusulkan)?->translatedFormat('d F Y')),
            'tambahsdm_tgl_dibutuhkan' => strtoupper($date->make($permin->tambahsdm_tgl_dibutuhkan)?->translatedFormat('d F Y'))
        ];

        $argumen = [
            'app' => $app,
            'contoh' => 'permintaan-tambah-sdm.docx',
            'data' => $data,
            'filename' => $filename
        ];

        return $this->isiFormulir(...$argumen);
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

        $date = $app->date;

        $filename = 'tanda-terima-dokumen-titipan-' . $akun->sdm_no_absen . '.docx';

        $data = [
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
        ];

        $argumen = [
            'app' => $app,
            'contoh' => 'tanda-terima-dokumen-titipan.docx',
            'data' => $data,
            'filename' => $filename
        ];

        return $this->isiFormulir(...$argumen);
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

        $filename = 'tanda-terima-inventaris-' . $akun->sdm_no_absen . '.docx';

        $data = [
            'sdm_uk_seragam' => $akun->sdm_uk_seragam,
            'sdm_uk_sepatu' => $akun->sdm_uk_sepatu,
            'ket_tanda_terima' => "UNTUK KARYAWAN A.N {$akun->sdm_nama} - ({$akun->sdm_no_absen})",
        ];

        $argumen = [
            'app' => $app,
            'contoh' => 'tanda-terima-inventaris.docx',
            'data' => $data,
            'filename' => $filename
        ];

        return $this->isiFormulir(...$argumen);
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

        $date = $app->date;

        $filename = 'persetujuan-gaji-' . $akun->sdm_no_absen . '.docx';

        $data = [
            'sdm_nama' => $akun->sdm_nama,
            'sdm_no_absen' => $akun->sdm_no_absen,
            'sdm_tgl_gabung' => strtoupper($date->make($akun->sdm_tgl_gabung)?->translatedFormat('d F Y')),
            'sdm_tgl_lahir' => $akun->sdm_tempat_lahir . ', ' . strtoupper($date->make($akun->sdm_tgl_lahir)?->translatedFormat('d F Y')),
            'sdm_kelamin' => $akun->sdm_kelamin == 'L' ? 'LAKI - LAKI' : 'PEREMPUAN',
            'sdm_alamat' => "{$akun->sdm_alamat}, {$akun->sdm_alamat_kelurahan}, {$akun->sdm_alamat_kecamatan}, {$akun->sdm_alamat_kota}, {$akun->sdm_alamat_provinsi}.",
        ];

        $argumen = [
            'app' => $app,
            'contoh' => 'persetujuan-gaji.docx',
            'data' => $data,
            'filename' => $filename
        ];

        return $this->isiFormulir(...$argumen);
    }

    public function rekamHapusDataSDM($app, $dataHapus)
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

        $date = $app->date;

        $filename = 'keterangan-kerja-' . $akun->sdm_no_absen . '.docx';

        $data = [
            'sdm_nama' => $akun->sdm_nama,
            'sdm_no_absen' => $akun->sdm_no_absen,
            'sdm_tgl_gabung' => strtoupper($date->make($akun->sdm_tgl_gabung)?->translatedFormat('d F Y')),
            'sdm_tgl_lahir' => $akun->sdm_tempat_lahir . ', ' . strtoupper($date->make($akun->sdm_tgl_lahir)?->translatedFormat('d F Y')),
            'sdm_kelamin' => $akun->sdm_kelamin == 'L' ? 'LAKI - LAKI' : 'PEREMPUAN',
            'sdm_alamat' => "{$akun->sdm_alamat}, {$akun->sdm_alamat_kelurahan}, {$akun->sdm_alamat_kecamatan}, {$akun->sdm_alamat_kota}, {$akun->sdm_alamat_provinsi}.",
            'sdm_tgl_berhenti' => strtoupper($date->make($akun->sdm_tgl_berhenti)?->translatedFormat('d F Y')) ?: 'saat surat ini diterbitkan',
            'paragraf_keterangan' => $akun->sdm_tgl_berhenti ? 'PT. Kepuh Kencana Arum mengapresiasi kontribusi dan dedikasi yang diberikan, selama bekerja yang bersangkutan menunjukkan kinerja yang baik untuk menunjang kesuksesan kerjanya' : 'Surat keterangan ini dibuat untuk ...[isi keterangan]',
        ];

        $argumen = [
            'app' => $app,
            'contoh' => 'keterangan-kerja.docx',
            'data' => $data,
            'filename' => $filename
        ];

        return $this->isiFormulir(...$argumen);
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

    public function unggahSanksiSDM(Rule $rule)
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
                    'unggah_sanksi_sdm' => ['required', 'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
                ],
                [],
                [
                    'unggah_sanksi_sdm' => 'Berkas Yang Diunggah'
                ]
            );

            $validasifile->validate();
            $file = $validasifile->safe()->only('unggah_sanksi_sdm')['unggah_sanksi_sdm'];
            $namafile = 'unggahsanksisdm-' . date('YmdHis') . '.xlsx';

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

                $headingArray = $worksheet->rangeToArray('A1:' . $kolomTertinggi . '1', null, false, TRUE, FALSE);
                $dataArray = $worksheet->rangeToArray('A' . $startRow . ':' . $kolomTertinggi . $barisTertinggi, NULL, FALSE, TRUE, FALSE);
                $tabel = array_merge($headingArray, $dataArray);
                $isitabel = array_shift($tabel);

                $datas = array_map(function ($x) use ($isitabel) {
                    return array_combine($isitabel, $x);
                }, $tabel);

                $dataexcel = array_map(function ($x) use ($idPengunggah) {
                    return $x + ['sanksi_id_pengunggah' => $idPengunggah] + ['sanksi_id_pembuat' => $idPengunggah] + ['sanksi_id_pengubah' => $idPengunggah] + ['sanksi_diunggah' => date('Y-m-d H:i:s')];
                }, $datas);

                $data = array_combine(range(($startRow - 1), count($dataexcel) + ($startRow - 2)), array_values($dataexcel));

                $validasi = $validator->make(
                    $data,
                    [
                        '*.sanksi_no_absen' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                        '*.sanksi_jenis' => ['required', 'string', 'max:40', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'SANKSI SDM');
                        })],
                        '*.sanksi_mulai' => ['required', 'date'],
                        '*.sanksi_selesai' => ['required', 'date', 'after:sanksi_mulai'],
                        '*.sanksi_tambahan' => ['sometimes', 'nullable', 'string'],
                        '*.sanksi_keterangan' => ['sometimes', 'nullable', 'string'],
                        '*.sanksi_id_pengunggah' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                        '*.sanksi_id_pembuat' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                        '*.sanksi_id_pengubah' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                        '*.sanksi_diunggah' => ['required', 'nullable', 'date']
                    ],
                    [
                        '*.sanksi_no_absen.*' => 'Nomor Absen baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                        '*.sanksi_jenis.*' => 'Jenis Sanksi baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                        '*.sanksi_mulai.*' => 'Tanggal Mulai Sanksi baris ke-:position wajib berupa tanggal.',
                        '*.sanksi_selesai.*' => 'Tanggal Selesai Sanksi baris ke-:position wajib berupa tanggal setelah Tanggal Mulai Sanksi.',
                        '*.sanksi_tambahan.*' => 'Tambahan Sanksi baris ke-:position wajib berupa karakter.',
                        '*.sanksi_keterangan.*' => 'Keterangan Sanksi baris ke-:position wajib berupa karakter.',
                        '*.sanksi_id_pengunggah.*' => 'ID Pengunggah baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                        '*.sanksi_id_pembuat.*' => 'ID Pembuat baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                        '*.sanksi_id_pengubah.*' => 'ID Pengubah baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                        '*.sanksi_diunggah.*' => 'Waktu Unggah baris ke-:position wajib berupa tanggal.'
                    ]
                );

                if ($validasi->fails()) {
                    return $app->redirect->back()->withErrors($validasi);
                }

                $app->db->table('sanksisdms')->upsert(
                    $validasi->validated(),
                    ['sanksi_no_absen', 'sanksi_jenis', 'sanksi_mulai'],
                    ['sanksi_selesai', 'sanksi_tambahan', 'sanksi_keterangan', 'sanksi_id_pengunggah', 'sanksi_id_pengubah', 'sanksi_diunggah']
                );

                echo $pesansimpan;
            };

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            $storage->delete($fileexcel);

            FungsiStatis::hapusCachePelanggaranSDM();
            FungsiStatis::hapusCacheSanksiSDM();

            echo '<p>Selesai menyimpan data excel. Mohon <a class="isi-xhr" href="' . $app->url->route('sdm.sanksi.data') . '">periksa ulang data</a>.</p>';

            exit();
        };

        $HtmlPenuh = $app->view->make('sdm.sanksi.unggah');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }
}
