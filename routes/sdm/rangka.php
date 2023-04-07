<?php
$route = app('router');

$route->get('/', 'Umum@mulai')->name('mulai');
$route->get('/foto-profil/{berkas_foto_profil?}', 'Berkas@fotoProfil')->name('tautan-foto-profil');
$route->get('/berkas/{berkas?}', 'Berkas@berkas')->name('berkas');
$route->get('/panduan', 'Berkas@panduan')->name('panduan');

$route->group(['prefix' => 'permintaan-tambah-sdm', 'as' => 'permintaan-tambah-sdm.'], base_path('routes/sdm/permintaan-tambah-sdm.php'));
$route->group(['prefix' => 'posisi', 'as' => 'posisi.'], base_path('routes/sdm/posisi.php'));
$route->group(['prefix' => 'penempatan', 'as' => 'penempatan.'], base_path('routes/sdm/penempatan.php'));
$route->group(['prefix' => 'sanksi', 'as' => 'sanksi.'], base_path('routes/sdm/sanksi.php'));
$route->group(['prefix' => 'pelanggaran', 'as' => 'pelanggaran.'], base_path('routes/sdm/pelanggaran.php'));
