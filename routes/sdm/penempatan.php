<?php
$route = app('router');

$route->get('/data-baru', 'Penempatan@indexBaru')->name('data-baru');
$route->get('/data-batal', 'Penempatan@indexBatal')->name('data-batal');
$route->get('/data-aktif', 'Penempatan@indexAktif')->name('data-aktif');
$route->get('/data-nonaktif', 'Penempatan@indexNonAktif')->name('data-nonaktif');
$route->get('/data-kadaluarsa', 'Penempatan@indexKadaluarsa')->name('data-kadaluarsa');
$route->get('/data-akanhabis', 'Penempatan@indexAkanHabis')->name('data-akanhabis');
$route->get('/riwayat/{uuid?}', 'Penempatan@index')->name('riwayat');
$route->get('/riwayat-nyata', 'Penempatan@indexMasaKerjaNyata')->name('riwayat-nyata');
$route->get('/lihat/{uuid?}', 'Penempatan@lihat')->name('lihat');
$route->match(['get', 'post'], '/tambah/{uuid?}', 'Penempatan@tambah')->name('tambah');
$route->match(['get', 'post'], '/ubah/{uuid?}', 'Penempatan@ubah')->name('ubah');
$route->get('/berkas/{berkas?}', 'Penempatan@berkas')->name('berkas');
$route->get('/formulir-penilaian-sdm/{uuid?}', 'Penempatan@formulirPenilaianSDM')->name('formulir-penilaian-sdm');
$route->get('/formulir-perubahan-status-sdm/{uuid?}', 'Penempatan@formulirPerubahanStatusSDM')->name('formulir-perubahan-status-sdm');
$route->get('/pkwt-sdm/{uuid?}', 'Penempatan@PKWTSDM')->name('pkwt-sdm');
$route->get('/statistik', 'Penempatan@statistik')->name('statistik');
$route->match(['get', 'post'], '/hapus/{uuid?}', 'Penempatan@hapus')->name('hapus');
$route->match(['get', 'post'], '/unggah', 'Penempatan@unggah')->name('unggah');
$route->get('/contoh-unggah', 'Penempatan@contohUnggah')->name('contoh-unggah');
