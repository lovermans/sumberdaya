<?php
$route = app('router');

$route->get('/', 'Umum@mulai')->name('mulai');
$route->get('/foto-profil/{berkas_foto_profil?}', 'Berkas@fotoProfil')->name('tautan-foto-profil');
$route->get('/berkas/{berkas?}', 'Berkas@berkas')->name('berkas');
$route->get('/panduan', 'Berkas@panduan')->name('panduan');
$route->get('/akun/{uuid?}', 'Umum@akun')->name('akun');
$route->match(['get', 'post'], '/ubah-akun/{uuid?}', 'Umum@ubahAkun')->name('ubah-akun');
$route->match(['get', 'post'], '/ubah-sandi', 'Umum@ubahSandi')->name('ubah-sandi');
$route->match(['get', 'post'], '/unggah', 'Umum@unggah')->name('unggah');
$route->get('/contoh-unggah', 'Berkas@contohUnggahProfilSDM')->name('contoh-unggah');
$route->get('/unduh-kartu-sdm/{uuid?}', 'Berkas@unduhKartuSDM')->name('unduh.kartu-sdm');
$route->get('/formulir-serahterimasdm/{uuid?}', 'Berkas@formulirSerahTerimaSDMBaru')->name('formulir-serah-terima-sdm-baru');
$route->get('/formulir-persetujuangaji/{uuid?}', 'Berkas@formulirPersetujuanGaji')->name('formulir-persetujuan-gaji');
$route->get('/formulir-ttdokumentitipan/{uuid?}', 'Berkas@formulirTTDokumenTitipan')->name('formulir-tt-dokumen-titipan');
$route->get('/formulir-ttinventaris/{uuid?}', 'Berkas@formulirTTInventaris')->name('formulir-tt-inventaris');
$route->get('/formulir-pelepasan-sdm/{uuid?}', 'Berkas@formulirPelepasanSDM')->name('formulir-pelepasan-sdm');
$route->get('/surat-keterangan-sdm/{uuid?}', 'Berkas@suratKeteranganSDM')->name('surat-keterangan-sdm');


$route->group(['prefix' => 'permintaan-tambah-sdm', 'as' => 'permintaan-tambah-sdm.'], base_path('routes/sdm/permintaan-tambah-sdm.php'));
$route->group(['prefix' => 'posisi', 'as' => 'posisi.'], base_path('routes/sdm/posisi.php'));
$route->group(['prefix' => 'penempatan', 'as' => 'penempatan.'], base_path('routes/sdm/penempatan.php'));
$route->group(['prefix' => 'sanksi', 'as' => 'sanksi.'], base_path('routes/sdm/sanksi.php'));
$route->group(['prefix' => 'pelanggaran', 'as' => 'pelanggaran.'], base_path('routes/sdm/pelanggaran.php'));
$route->group(['prefix' => 'penilaian', 'as' => 'penilaian.'], base_path('routes/sdm/penilaian.php'));
