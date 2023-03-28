<?php

namespace App\Http\Controllers;

use QRcode;
use App\Tambahan\FungsiStatis;
use Illuminate\Validation\Rule;
use App\Tambahan\ChunkReadFilter;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Validation\Rules\Password;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class SumberDaya
{
    public function mulai()
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();

        if ($pengguna) {
            $sandi = $pengguna->password;
            $hash = $app->hash;
            $sandiKtp = $hash->check($pengguna->sdm_no_ktp, $sandi);
            $sandiBawaan = $hash->check('penggunaportalsdm', $sandi);
            
            if ($sandiKtp || $sandiBawaan) {
                $reqs->session()->put(['spanduk' => 'Sandi Anda kurang aman.']);
            }
        }

        $HtmlPenuh = $app->view->make('mulai');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']) : $HtmlPenuh;
    }

    public function akun(FungsiStatis $fungsiStatis, $uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        
        abort_unless($pengguna, 401);
        
        $database = $app->db;

        $kontrak = $database->query()->select('penempatan_no_absen', 'penempatan_lokasi', 'penempatan_posisi')->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
            $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
        });

        $dasar = $database->query()->select('a.sdm_uuid', 'a.sdm_no_permintaan', 'a.sdm_no_absen', 'a.sdm_tgl_gabung', 'a.sdm_warganegara', 'a.sdm_no_ktp', 'a.sdm_nama', 'a.sdm_tempat_lahir', 'a.sdm_tgl_lahir', 'a.sdm_kelamin', 'a.sdm_gol_darah', 'a.sdm_alamat', 'a.sdm_alamat_rt', 'a.sdm_alamat_rw', 'a.sdm_alamat_kelurahan', 'a.sdm_alamat_kecamatan', 'a.sdm_alamat_kota', 'a.sdm_alamat_provinsi', 'a.sdm_alamat_kodepos', 'a.sdm_agama', 'a.sdm_no_kk', 'a.sdm_status_kawin', 'a.sdm_jml_anak', 'a.sdm_pendidikan', 'a.sdm_jurusan', 'a.sdm_telepon', 'a.email', 'a.sdm_disabilitas', 'a.sdm_no_bpjs', 'a.sdm_no_jamsostek', 'a.sdm_no_npwp', 'a.sdm_nama_bank', 'a.sdm_cabang_bank', 'a.sdm_rek_bank', 'a.sdm_an_rek', 'a.sdm_nama_dok', 'a.sdm_nomor_dok', 'a.sdm_penerbit_dok', 'a.sdm_an_dok', 'a.sdm_kadaluarsa_dok', 'a.sdm_uk_seragam', 'a.sdm_uk_sepatu', 'a.sdm_ket_kary', 'a.sdm_tgl_berhenti', 'a.sdm_jenis_berhenti', 'a.sdm_ket_berhenti', 'a.sdm_id_atasan', 'a.sdm_hak_akses', 'a.sdm_ijin_akses')->from('sdms', 'a');
        
        $akun = $dasar->clone()->addSelect('b.sdm_uuid as uuid_atasan', 'b.sdm_nama as nama_atasan', 'b.sdm_tgl_berhenti as tgl_berhenti_atasan', 'penempatan_lokasi', 'penempatan_posisi')->leftJoin('sdms as b', 'a.sdm_id_atasan', '=', 'b.sdm_no_absen')
        ->leftJoinSub($kontrak, 'kontrak', function ($join) {
            $join->on('b.sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
        })
        ->where('a.sdm_uuid', $uuid)->first();
        
        abort_unless($akun, 404, 'Profil yang dicari tidak ada.');

        $penempatans = $database->query()->select('sdm_uuid', 'sdm_no_absen', 'sdm_tgl_gabung', 'sdm_tgl_lahir', 'sdm_no_ktp', 'sdm_nama', 'sdm_kelamin', 'sdm_tgl_berhenti', 'penempatan_uuid', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_lokasi', 'penempatan_posisi', 'penempatan_kategori', 'penempatan_kontrak', 'penempatan_pangkat', 'penempatan_golongan', 'penempatan_grup', 'penempatan_keterangan', 'posisi_wlkp', $app->db->raw('IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_gabung, sdm_tgl_berhenti)) as masa_kerja, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, penempatan_mulai, NOW()),TIMESTAMPDIFF(YEAR, penempatan_mulai, sdm_tgl_berhenti)) as masa_aktif, IF(sdm_tgl_berhenti IS NULL,TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, NOW()),TIMESTAMPDIFF(YEAR, sdm_tgl_lahir, sdm_tgl_berhenti)) as usia'))
            ->from('penempatans')->joinSub($dasar, 'dasar', function ($join) {
                $join->on('penempatan_no_absen', '=', 'dasar.sdm_no_absen');
            })
            ->leftJoin('posisis', 'penempatan_posisi', '=', 'posisi_nama')
            ->where('sdm_uuid', $uuid)->latest('penempatan_mulai')->get();

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $no_absen_sdm = $pengguna->sdm_no_absen;
        $no_absen_atasan = $pengguna->sdm_id_atasan;
        $lingkup = array_filter(explode(',', $ijin_akses));
        $lingkup_lokasi = $penempatans->pluck('penempatan_lokasi');
        $lingkup_akses = $lingkup_lokasi->unique()->intersect($lingkup)->count();
        $str = str();

        abort_unless(($str->contains($pengguna->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']) && (blank($ijin_akses) || $lingkup_lokasi->isEmpty() || ($lingkup_akses > 0))) || ($no_absen_sdm == $akun->sdm_no_absen) || ($akun->sdm_no_absen == $no_absen_atasan) || ($akun->sdm_id_atasan == $no_absen_sdm) || (!blank($no_absen_atasan) && ($akun->sdm_id_atasan == $no_absen_atasan)), 403, 'Ijin akses dibatasi.');
        
        $no_wa_ts = $str->replace('-', '', $akun->sdm_telepon);
        $no_wa_tst = $str->replace(' ', '', $no_wa_ts);
        $no_wa_tn = $str->startsWith($no_wa_tst, '0');
        $no_wa_td = $str->startsWith($no_wa_tst, '8');
        if ($no_wa_tn) {
            $no_wa = $str->replaceFirst('0', '62', $no_wa_tst);
        } elseif ($no_wa_td) {
            $no_wa = $str->start($no_wa_tst, '62');
        } else {
            $no_wa = $no_wa_tst;
        }

        $cacheSDM = $fungsiStatis->ambilCacheSDM();

        $data = [
            'akun' => $akun,
            'penempatans' => $penempatans,
            'personils' => $cacheSDM->where('sdm_id_atasan', $akun->sdm_no_absen),
            'no_wa' =>  $no_wa ?: '0',
            'batasi' => $str->contains($pengguna->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']) || $no_absen_sdm == $akun->sdm_no_absen,
        ];
        
        $HtmlPenuh = $app->view->make('akun', $data);
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

    public function tentangAplikasi()
    {
        $app = app();
        $HtmlPenuh = $app->view->make('tentang-aplikasi');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $app->request->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }

    public function ubahSandi()
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        abort_unless($pengguna, 401);

        $database = $app->db;
        $idPenguna = $pengguna->id;
        
        $akun = $database->query()->select('id')->from('sdms')->where('id', $idPenguna)->first();
        
        abort_unless($idPenguna == $akun->id, 403, 'Identitas pengguna berbeda.');

        $session = $reqs->session();
        $hash = $app->hash;
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        $halaman = $app->view;
        
        if ($reqs->isMethod('post')) {
            abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

            $validasiSandi = $app->validator->make(
                $reqs->all(),
                [
                    'password_lama' => ['required', 'string', 'current_password'],
                    'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
                ],
                [],
                [
                    'password_lama' => 'Kata Sandi Lama',
                    'password' => 'Kata Sandi Baru',
                ]
            );
            
            $validasiSandi->validate();
            
            $sandiBaru = $hash->make($validasiSandi->safe()->only('password')['password']);
            
            $database->table('sdms')->where('id', $idPenguna)->update(['password' => $sandiBaru]);
            
            $session->forget('spanduk');
            
            return $app->redirect->route('mulai')->with('pesan', 'Sandi berhasil diubah.');
        }

        $HtmlPenuh = $halaman->make('ubah-sandi');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $tanggapan->make($HtmlIsi)->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']) : $HtmlPenuh;
    }

    public function ubahAkun(FungsiStatis $fungsiStatis, $uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        abort_unless($pengguna && $uuid, 401);
        $database = $app->db;
        
        $penem = $database->query()->select('penempatan_no_absen', 'penempatan_lokasi', 'penempatan_mulai')->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
            $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
        });
        
        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $ijin_akses));
        
        $akun = $database->query()->select('sdms.*', 'penempatan_lokasi')
            ->from('sdms')
            ->leftJoinSub($penem, 'kontrak', function ($join) {
                $join->on('sdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->where('sdm_uuid', $uuid)
            ->first();
            
        $no_absen_sdm = $pengguna->sdm_no_absen;
        $lingkup_lokasi = $akun->penempatan_lokasi;
        $lingkup_akses = collect($lingkup_lokasi)->intersect($lingkup)->count();

        abort_unless(blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0) || ($no_absen_sdm == $akun->sdm_no_absen), 403, 'Akses pengguna dibatasi.');

        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        $halaman = $app->view;

        if ($reqs->isMethod('post')) {
            abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');
            
            $reqs->whenFilled('sdm_hak_akses', function ($input) use ($reqs) {
                $reqs->except('sdm_hak_akses');
                $reqs->merge(['sdm_hak_akses' => implode(',', $input)]);
            }, function () use ($reqs) {
                $reqs->merge(['sdm_hak_akses' => null]);
            });
            
            $reqs->whenFilled('sdm_ijin_akses', function ($input) use ($reqs) {
                $reqs->except('sdm_ijin_akses');
                $reqs->merge(['sdm_ijin_akses' => implode(',', $input)]);
            }, function () use ($reqs) {
                $reqs->merge(['sdm_ijin_akses' => null]);
            });
            
            $reqs->merge(['sdm_id_pengubah' => $pengguna->sdm_no_absen]);
            
            $validasi = $app->validator->make(
                $reqs->all(),
                [
                    'foto_profil' => ['sometimes', 'image', 'dimensions:min_width=299,min_height=399'],
                    'sdm_berkas' => ['sometimes', 'file', 'mimetypes:application/pdf'],
                    'sdm_no_permintaan' => ['sometimes', 'nullable', 'string', 'max:20', 'exists:tambahsdms,tambahsdm_no'],
                    'sdm_no_absen' => ['required', 'string', 'max:10', Rule::unique('sdms')->where(fn ($query) => $query->whereNot('sdm_uuid', $uuid))],
                    'sdm_id_atasan' => ['sometimes', 'nullable', 'string', 'max:10', 'different:sdm_no_absen'],
                    'sdm_tgl_gabung' => ['required', 'date'],
                    'sdm_warganegara' => ['required', 'string', 'max:40'],
                    'sdm_no_ktp' => ['required', 'string', 'max:20'],
                    'sdm_nama' => ['required', 'string', 'max:80'],
                    'sdm_tempat_lahir' => ['required', 'string', 'max:40'],
                    'sdm_tgl_lahir' => ['required', 'date'],
                    'sdm_kelamin' => ['required', 'string', 'max:2'],
                    'sdm_gol_darah' => ['sometimes', 'nullable', 'string', 'max:2'],
                    'sdm_alamat' => ['required', 'string', 'max:120'],
                    'sdm_alamat_rt' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                    'sdm_alamat_rw' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                    'sdm_alamat_kelurahan' => ['required', 'string', 'max:40'],
                    'sdm_alamat_kecamatan' => ['required', 'string', 'max:40'],
                    'sdm_alamat_kota' => ['required', 'string', 'max:40'],
                    'sdm_alamat_provinsi' => ['required', 'string', 'max:40'],
                    'sdm_alamat_kodepos' => ['sometimes', 'nullable', 'string', 'max:10'],
                    'sdm_agama' => ['required', 'string', 'max:20'],
                    'sdm_no_kk' => ['sometimes', 'nullable', 'string', 'max:20'],
                    'sdm_status_kawin' => ['required', 'string', 'max:10'],
                    'sdm_jml_anak' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                    'sdm_pendidikan' => ['required', 'string', 'max:10'],
                    'sdm_jurusan' => ['sometimes', 'nullable', 'string', 'max:60'],
                    'sdm_telepon' => ['required', 'string', 'max:40'],
                    'email' => ['required', 'email'],
                    'sdm_disabilitas' => ['required', 'string', 'max:30'],
                    'sdm_no_bpjs' => ['sometimes', 'nullable', 'string', 'max:30'],
                    'sdm_no_jamsostek' => ['sometimes', 'nullable', 'string', 'max:30'],
                    'sdm_no_npwp' => ['sometimes', 'nullable', 'string', 'max:30'],
                    'sdm_nama_bank' => ['sometimes', 'nullable', 'string', 'max:20'],
                    'sdm_cabang_bank' => ['sometimes', 'nullable', 'string', 'max:50'],
                    'sdm_rek_bank' => ['sometimes', 'nullable', 'string', 'max:40'],
                    'sdm_an_rek' => ['sometimes', 'nullable', 'string', 'max:80'],
                    'sdm_nama_dok' => ['sometimes', 'nullable', 'string', 'max:50'],
                    'sdm_nomor_dok' => ['sometimes', 'nullable', 'string', 'max:40'],
                    'sdm_penerbit_dok' => ['sometimes', 'nullable', 'string', 'max:60'],
                    'sdm_an_dok' => ['sometimes', 'nullable', 'string', 'max:80'],
                    'sdm_kadaluarsa_dok' => ['sometimes', 'nullable', 'date'],
                    'sdm_uk_seragam' => ['sometimes', 'nullable', 'string', 'max:10'],
                    'sdm_uk_sepatu' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                    'sdm_ket_kary' => ['sometimes', 'nullable', 'string'],
                    'sdm_id_pengubah' => ['sometimes', 'nullable', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                    'sdm_hak_akses' => ['sometimes', 'nullable', 'string'],
                    'sdm_ijin_akses' => ['sometimes', 'nullable', 'string'],
                    'sdm_tgl_berhenti' => ['sometimes', 'nullable', 'date', 'required_unless:sdm_jenis_berhenti,null'],
                    'sdm_jenis_berhenti' => ['sometimes', 'nullable', 'string', 'required_unless:sdm_tgl_berhenti,null'],
                    'sdm_ket_berhenti' => ['sometimes', 'nullable', 'string'],
                ],
                [],
                [
                    'foto_profil' => 'Foto Profil',
                    'sdm_berkas' => 'Berkas Yang Diunggah',
                    'sdm_no_permintaan' => 'Nomor Permintaan Tambah SDM',
                    'sdm_no_absen' => 'Nomor Absen SDM',
                    'sdm_id_atasan' => 'Nomor Absen Atasan',
                    'sdm_tgl_gabung' => 'Tanggal Bergabung SDM',
                    'sdm_warganegara' => 'Warganegara',
                    'sdm_no_ktp' => 'Nomor E-KTP/Passport',
                    'sdm_nama' => 'Nama SDM',
                    'sdm_tempat_lahir' => 'Tempat Lahir',
                    'sdm_tgl_lahir' => 'Tanggal Lahir',
                    'sdm_kelamin' => 'Kelamin',
                    'sdm_gol_darah' => 'Golongan Darah',
                    'sdm_alamat' => 'Alamat',
                    'sdm_alamat_rt' => 'Alamat RT',
                    'sdm_alamat_rw' => 'Alamat RW',
                    'sdm_alamat_kelurahan' => 'Alamat Kelurahan',
                    'sdm_alamat_kecamatan' => 'Alamat Kecamatan',
                    'sdm_alamat_kota' => 'Alamat Kota/Kabupaten',
                    'sdm_alamat_provinsi' => 'Alamat Provinsi',
                    'sdm_alamat_kodepos' => 'Alamat Kode Pos',
                    'sdm_agama' => 'Agama',
                    'sdm_no_kk' => 'Nomor KK',
                    'sdm_status_kawin' => 'Status Menikah',
                    'sdm_jml_anak' => 'Jumlah Anak',
                    'sdm_pendidikan' => 'Pendidikan',
                    'sdm_jurusan' => 'Jurusan',
                    'sdm_telepon' => 'Telepon',
                    'email' => 'Email',
                    'sdm_disabilitas' => 'Disabilitas',
                    'sdm_no_bpjs' => 'Nomor BPJS',
                    'sdm_no_jamsostek' => 'Nomor Jamsostek',
                    'sdm_no_npwp' => 'NPWM',
                    'sdm_nama_bank' => 'Nama Bank',
                    'sdm_cabang_bank' => 'Cabang Bank',
                    'sdm_rek_bank' => 'Nomor Rekening Bank',
                    'sdm_an_bank' => 'Nama Rekening Bank',
                    'sdm_nama_dok' => 'Nama/Judul Dokumen Titipan',
                    'sdm_nomor_dok' => 'Nomor Dokumen Titipan',
                    'sdm_penerbit_dok' => 'Penerbit Dokumen Titipan',
                    'sdm_an_dok' => 'A.n Dokumen Titipan',
                    'sdm_kadaluarsa_dok' => 'Tanggal Kadaluarsa Dokumen Titipan',
                    'sdm_uk_seragam' => 'Ukuran Seragam',
                    'sdm_uk_sepatu' => 'Ukuran Sepatu',
                    'sdm_ket_kary' => 'Keterangan Karyawan',
                    'sdm_id_pengubah' => 'No Absen Pengurus',
                    'sdm_hak_akses' => 'Hak Akses Aplikasi',
                    'sdm_ijin_akses' => 'Ijin Akses Aplikasi',
                    'sdm_tgl_berhenti' => 'Tanggal Berhenti',
                    'sdm_jenis_berhenti' => 'Jenis Berhenti SDM',
                    'sdm_ket_berhenti' => 'Keterangan Berhenti SDM',
                    ]
                );
                
            $validasi->validate();
            
            $valid = $validasi->safe();
            $str = str();
            $pengurus = $str->contains($pengguna->sdm_hak_akses, 'SDM-PENGURUS');
            
            if ($pengurus && blank($ijin_akses)) {
                $data = $valid->except(['foto_profil', 'sdm_berkas']);
            } elseif ($pengurus & !blank($ijin_akses)) {
                $data = $valid->except(['foto_profil', 'sdm_berkas', 'sdm_hak_akses', 'sdm_ijin_akses']);
            } else {
                $data = $valid->except(['foto_profil', 'sdm_berkas', 'sdm_hak_akses', 'sdm_ijin_akses', 'sdm_tgl_berhenti', 'sdm_jenis_berhenti', 'sdm_ket_berhenti', 'sdm_ket_kary', 'sdm_nama_dok', 'sdm_nomor_dok', 'sdm_penerbit_dok', 'sdm_an_dok', 'sdm_kadaluarsa_dok', 'sdm_no_permintaan', 'sdm_no_absen', 'sdm_id_atasan', 'sdm_tgl_gabung', 'sdm_warganegara', 'sdm_disabilitas', 'sdm_nama_bank', 'sdm_cabang_bank', 'sdm_rek_bank', 'sdm_an_bank']);
            }
            
            $database->table('sdms')->where('sdm_uuid', $uuid)->update($data);
            
            $foto = $valid->only('foto_profil')['foto_profil'] ?? false;
            $berkas = $valid->only('sdm_berkas')['sdm_berkas'] ?? false;
            $no_absen = $valid->only('sdm_no_absen')['sdm_no_absen'];
            
            if ($foto) {
                $foto->storeAs('sdm/foto-profil', $no_absen . '.webp');
            }
            
            if ($berkas && $pengurus && (blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0) || ($no_absen_sdm == $akun->sdm_no_absen))) {
                $berkas->storeAs('sdm/berkas', $no_absen . '.pdf');
            }
            
            $fungsiStatis->hapusCacheSDMUmum();
            
            $pesan = $fungsiStatis->statusBerhasil();

            $perujuk = $reqs->session()->get('tautan_perujuk');

            $redirect = $app->redirect;

            return $perujuk ? $redirect->to($perujuk)->with('pesan', $pesan) : $redirect->route('sdm.mulai')->with('pesan', $pesan);
        }

        $aturs = $fungsiStatis->ambilCacheAtur();
        $permintaanSdms = $fungsiStatis->ambilCachePermintaanTambahSDM();
        $atasan = $fungsiStatis->ambilCacheSDM();

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

        $HtmlPenuh = $halaman->make('tambah-ubah-akun', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $tanggapan->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }

    public function unduh($berkas = null)
    {
        $app = app();
        abort_unless($berkas && $app->filesystem->exists("unduh/{$berkas}"), 404, 'Berkas Tidak Ditemukan.');
        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->download($app->storagePath("app/unduh/{$berkas}"));
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

    public function unduhPanduan(FungsiStatis $fungsiStatis, $berkas)
    {
        $fungsiStatis->hapusBerkasLama();
        
        $app = app();

        abort_unless($berkas && $app->filesystem->exists("{$berkas}"), 404, 'Berkas Tidak Ditemukan.');

        $jalur = $app->storagePath("app/{$berkas}");
        
        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->file($jalur, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Expires' => '0',
            'Pragma' => 'no-cache',
            'Content-Disposition' => 'inline',
            'Content-Type' => $app->files->mimeType($jalur),
        ]);
    }

    public function unggah(Rule $rule, FungsiStatis $fungsiStatis) {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();
        
        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        $halaman = $app->view;

        if ($reqs->isMethod('post')) {
            abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

            set_time_limit(0);
            ob_implicit_flush();
            ob_end_flush();
            header('X-Accel-Buffering: no');

            $validator = $app->validator;
            
            $validasifile = $validator->make(
                $reqs->all(),
                [
                    'unggah_profil_sdm' => ['required', 'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
                ],
                [],
                [
                    'unggah_profil_sdm' => 'Berkas Yang Diunggah'
                ]
            );
            
            $validasifile->validate();

            $file = $validasifile->safe()->only('unggah_profil_sdm')['unggah_profil_sdm'];
            $namafile = 'unggahprofilsdm-' . date('YmdHis') . '.xlsx';
            
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
            $hash = $app->hash;
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
                
                $dataexcel = array_map(function ($x) use ($idPengunggah, $hash) {
                    return $x + ['sdm_id_pengunggah' => $idPengunggah] + ['sdm_id_pembuat' => $idPengunggah] + ['sdm_id_pengubah' => $idPengunggah] + ['sdm_diunggah' => date('Y-m-d H:i:s')] + ['password' => $hash->make($x['sdm_no_ktp'])];
                }, $datas);

                $data = array_combine(range(($startRow - 1), count($dataexcel) + ($startRow - 2) ), array_values($dataexcel));
                
                $validasi = $validator->make(
                    $data,
                    [
                        '*.sdm_no_permintaan' => ['sometimes', 'nullable', 'string', 'max:20', 'exists:tambahsdms,tambahsdm_no'],
                        '*.sdm_no_absen' => ['required', 'string', 'max:10'],
                        '*.sdm_tgl_gabung' => ['required', 'date'],
                        '*.sdm_warganegara' => ['required', 'string', 'max:40', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'NEGARA');
                        })],
                        '*.sdm_no_ktp' => ['required', 'string', 'max:20'],
                        '*.sdm_nama' => ['required', 'string', 'max:80'],
                        '*.sdm_tempat_lahir' => ['required', 'string', 'max:40'],
                        '*.sdm_tgl_lahir' => ['required', 'date'],
                        '*.sdm_kelamin' => ['required', 'string', 'max:2', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'KELAMIN');
                        })],
                        '*.sdm_gol_darah' => ['sometimes', 'nullable', 'string', 'max:2', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'GOLONGAN DARAH');
                        })],
                        '*.sdm_alamat' => ['required', 'string', 'max:120'],
                        '*.sdm_alamat_rt' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                        '*.sdm_alamat_rw' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                        '*.sdm_alamat_kelurahan' => ['required', 'string', 'max:40'],
                        '*.sdm_alamat_kecamatan' => ['required', 'string', 'max:40'],
                        '*.sdm_alamat_kota' => ['required', 'string', 'max:40'],
                        '*.sdm_alamat_provinsi' => ['required', 'string', 'max:40'],
                        '*.sdm_alamat_kodepos' => ['sometimes', 'nullable', 'string'],
                        '*.sdm_agama' => ['required', 'string', 'max:20', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'AGAMA');
                        })],
                        '*.sdm_no_kk' => ['sometimes', 'nullable', 'string', 'max:20'],
                        '*.sdm_status_kawin' => ['required', 'string', 'max:10', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'STATUS MENIKAH');
                        })],
                        '*.sdm_jml_anak' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                        '*.sdm_pendidikan' => ['required', 'string', 'max:10', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'PENDIDIKAN');
                        })],
                        '*.sdm_jurusan' => ['sometimes', 'nullable', 'string', 'max:60'],
                        '*.sdm_telepon' => ['required', 'string', 'max:40'],
                        '*.email' => ['required', 'email'],
                        '*.sdm_disabilitas' => ['required', 'string', 'max:30', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'DISABILITAS');
                        })],
                        '*.sdm_no_bpjs' => ['sometimes', 'nullable', 'string', 'max:30'],
                        '*.sdm_no_jamsostek' => ['sometimes', 'nullable', 'string', 'max:30'],
                        '*.sdm_no_npwp' => ['sometimes', 'nullable', 'string', 'max:30'],
                        '*.sdm_nama_bank' => ['sometimes', 'nullable', 'string', 'max:20', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'BANK');
                        })],
                        '*.sdm_cabang_bank' => ['sometimes', 'nullable', 'string', 'max:50'],
                        '*.sdm_rek_bank' => ['sometimes', 'nullable', 'string', 'max:40'],
                        '*.sdm_an_rek' => ['sometimes', 'nullable', 'string', 'max:80'],
                        '*.sdm_nama_dok' => ['sometimes', 'nullable', 'string', 'max:50'],
                        '*.sdm_nomor_dok' => ['sometimes', 'nullable', 'string', 'max:40'],
                        '*.sdm_penerbit_dok' => ['sometimes', 'nullable', 'string', 'max:60'],
                        '*.sdm_an_dok' => ['sometimes', 'nullable', 'string', 'max:80'],
                        '*.sdm_kadaluarsa_dok' => ['sometimes', 'nullable', 'date'],
                        '*.sdm_uk_seragam' => ['sometimes', 'nullable', 'string', 'max:10', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                            return $query->where('atur_jenis', 'UKURAN SERAGAM');
                        })],
                        '*.sdm_uk_sepatu' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                        '*.sdm_ket_kary' => ['sometimes', 'nullable', 'string'],
                        '*.sdm_id_pengunggah' => ['sometimes', 'nullable', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                        '*.sdm_tgl_berhenti' => ['sometimes', 'nullable', 'date', 'required_unless:sdm_jenis_berhenti,null'],
                        '*.sdm_jenis_berhenti' => ['sometimes', 'nullable', 'string', 'required_unless:sdm_tgl_berhenti,null'],
                        '*.sdm_ket_berhenti' => ['sometimes', 'nullable', 'string'],
                        '*.sdm_id_atasan' => ['sometimes', 'nullable', 'string', 'max:10', 'different:sdm_no_absen', 'exists:sdms,sdm_no_absen'],
                        '*.sdm_id_pengunggah' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                        '*.sdm_id_pembuat' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                        '*.sdm_id_pengubah' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                        '*.sdm_diunggah' => ['required', 'nullable', 'date'],
                    ],
                    [
                        '*.sdm_no_permintaan.*' => 'Nomor Permintaan baris ke-:position maksimal 20 karakter dan wajib terdaftar Permintaan SDM.',
                        '*.sdm_no_absen.*' => 'Nomor Absen baris ke-:position maksimal 10 karakter.',
                        '*.sdm_tgl_gabung.*' => 'Tanggal Bergabung baris ke-:position wajib berupa tanggal.',
                        '*.sdm_warganegara.*' => 'Warga Negara baris ke-:position maksimal 20 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_no_ktp.*' => 'No KTP Passport baris ke-:position maksimal 20 karakter.',
                        '*.sdm_nama.*' => 'Nama SDM baris ke-:position maksimal 80 karakter.',
                        '*.sdm_tempat_lahir.*' => 'Tempat Lahir baris ke-:position maksimal 40 karakter.',
                        '*.sdm_tgl_lahir.*' => 'Tanggal Lahir baris ke-:position wajib berupa tanggal.',
                        '*.sdm_kelamin.*' => 'Kelamin baris ke-:position maksimal 2 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_gol_darah.*' => 'Golongan Darah baris ke-:position maksimal 2 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_alamat.*' => 'Alamat baris ke-:position maksimal 120 karakter.',
                        '*.sdm_alamat_rt.*' => 'Alamat RT baris ke-:position wajib berupa angka lebih dari 0.',
                        '*.sdm_alamat_rw.*' => 'Alamat RW baris ke-:position wajib berupa angka lebih dari 0.',
                        '*.sdm_alamat_kelurahan.*' => 'Kelurahan baris ke-:position maksimal 40 karakter.',
                        '*.sdm_alamat_kecamatan.*' => 'Kecamatan baris ke-:position maksimal 40 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_alamat_kota.*' => 'Kota baris ke-:position maksimal 40 karakter.',
                        '*.sdm_alamat_provinsi.*' => 'Provinsi baris ke-:position maksimal 40 karakter.',
                        '*.sdm_alamat_kodepos.*' => 'Kode Pos baris ke-:position maksimal 10 karakter.',
                        '*.sdm_agama.*' => 'Agama baris ke-:position maksimal 20 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_no_kk.*' => 'Nomor KK baris ke-:position maksimal 20 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_status_kawin.*' => 'Status Menikah baris ke-:position maksimal 10 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_jml_anak.*' => 'Jumlah Anak baris ke-:position wajib berupa angka lebih dari 0.',
                        '*.sdm_pendidikan.*' => 'Pendidikan baris ke-:position maksimal 10 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_jurusan.*' => 'Jurusan baris ke-:position maksimal 60 karakter.',
                        '*.sdm_telepon.*' => 'Telepon baris ke-:position maksimal 40 karakter.',
                        '*.email.*' => 'Email baris ke-:position wajib berupa email.',
                        '*.sdm_disabilitas.*' => 'Disabilitas baris ke-:position maksimal 30 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_no_bpjs.*' => 'No BPJS baris ke-:position maksimal 30 karakter.',
                        '*.sdm_no_jamsostek.*' => 'No Jamsostek baris ke-:position maksimal 30 karakter.',
                        '*.sdm_no_npwp.*' => 'NPWP baris ke-:position maksimal 30 karakter.',
                        '*.sdm_nama_bank.*' => 'Nama Bank baris ke-:position maksimal 20 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_cabang_bank.*' => 'Cabang Bank baris ke-:position maksimal 50 karakter.',
                        '*.sdm_rek_bank.*' => 'Nomor Rekening Bank baris ke-:position maksimal 40 karakter.',
                        '*.sdm_an_rek.*' => 'A.n Rekening baris ke-:position maksimal 40 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_nama_dok.*' => 'Judul Dokumen Titipan baris ke-:position maksimal 50 karakter.',
                        '*.sdm_nomor_dok.*' => 'Nomor Dokumen Titipan baris ke-:position maksimal 40 karakter.',
                        '*.sdm_penerbit_dok.*' => 'Penembit Dokumen Titipan baris ke-:position maksimal 60 karakter.',
                        '*.sdm_an_dok.*' => 'A.n Dokumen Titipan baris ke-:position maksimal 80 karakter.',
                        '*.sdm_kadaluarsa_dok.*' => 'Kadaluarsa Dokumen Titipan baris ke-:position wajib berupa tanggal.',
                        '*.sdm_uk_seragam.*' => 'Ukuran Seragam baris ke-:position maksimal 10 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_uk_sepatu.*' => 'Ukuran Sepatu baris ke-:position wajib berupa angka lebih dari 0.',
                        '*.sdm_ket_kary.*' => 'Keterangan SDM baris ke-:position wajib berupa karakter.',
                        '*.sdm_id_pengunggah.*' => 'ID Pengunggah baris ke-:position maksimal 10 karakter dan wajib terdaftar Pengaturan Umum.',
                        '*.sdm_tgl_berhenti.*' => 'ID Pengunggah baris ke-:position wajib berupa tanggal jika Jenis Berhenti terisi.',
                        '*.sdm_jenis_berhenti.*' => 'ID Pengunggah baris ke-:position wajib berupa tanggal jika Jenis Berhenti terisi.',
                        '*.sdm_ket_berhenti.*' => 'Keterangan Berhenti baris ke-:position wajib berupa karakter.',
                        '*.sdm_id_atasan.*' => 'ID Atasan baris ke-:position maksimal 10 karakter, berbeda dengan No Absen SDM dan terdaftar di data SDM.',
                        '*.sdm_id_pengunggah.*' => 'ID Pengunggah baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                        '*.sdm_id_pembuat.*' => 'ID Pembuat baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                        '*.sdm_id_pengubah.*' => 'ID Pengubah baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                        '*.sdm_diunggah.*' => 'Waktu Unggah baris ke-:position wajib berupa tanggal.',
                    ]
                );
                
                $validasi->validate();
                                
                $app->db->table('sdms')->upsert(
                    $validasi->validated(),
                    ['sdm_no_absen'],
                    ['sdm_no_permintaan', 'sdm_tgl_gabung', 'sdm_warganegara', 'sdm_no_ktp', 'sdm_nama', 'sdm_tempat_lahir', 'sdm_tgl_lahir', 'sdm_kelamin', 'sdm_gol_darah', 'sdm_alamat', 'sdm_alamat_rt', 'sdm_alamat_rw', 'sdm_alamat_kelurahan', 'sdm_alamat_kecamatan', 'sdm_alamat_kota', 'sdm_alamat_provinsi', 'sdm_alamat_kodepos', 'sdm_agama', 'sdm_no_kk', 'sdm_status_kawin', 'sdm_jml_anak', 'sdm_pendidikan', 'sdm_jurusan', 'sdm_telepon', 'email', 'sdm_disabilitas', 'sdm_no_bpjs', 'sdm_no_jamsostek', 'sdm_no_npwp', 'sdm_nama_bank', 'sdm_cabang_bank', 'sdm_rek_bank', 'sdm_an_rek', 'sdm_nama_dok', 'sdm_nomor_dok', 'sdm_penerbit_dok', 'sdm_an_dok', 'sdm_kadaluarsa_dok', 'sdm_uk_seragam', 'sdm_uk_sepatu', 'sdm_ket_kary', 'sdm_tgl_berhenti', 'sdm_jenis_berhenti', 'sdm_ket_berhenti', 'sdm_id_atasan', 'sdm_id_pengunggah', 'sdm_id_pengubah', 'sdm_diunggah']
                );
                
                echo $pesansimpan;
            };

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            
            $storage->delete($fileexcel);
            
            $fungsiStatis->hapusCacheSDMUmum();
            $pesan = $fungsiStatis->statusBerhasil();
            
            return $app->redirect->route('atur.data')->with('pesan', $pesan);
        }

        $HtmlPenuh = $app->view->make('unggah');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }

    public function formatFoto()
    {
        // foreach (Storage::files('sdm/foto-profil/backup') as $path) {
        //     Storage::copy($path, 'sdm/foto-profil/'.substr($path, -13));
        // }

        // return 'selesai';
    }

}
