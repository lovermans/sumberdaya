<?php
$route = app('router');

$route->get('/', 'PermintaanTambahSDM@index')->name('data');
$route->get('/lihat/{uuid?}', 'PermintaanTambahSDM@lihat')->name('lihat');
$route->match(['get', 'post'], '/tambah', 'PermintaanTambahSDM@tambah')->name('tambah');
$route->match(['get', 'post'], '/ubah/{uuid?}', 'PermintaanTambahSDM@ubah')->name('ubah');
$route->get('/berkas/{berkas?}', 'PermintaanTambahSDM@berkas')->name('berkas');
$route->get('/formulir/{uuid?}', 'PermintaanTambahSDM@formulir')->name('formulir');
