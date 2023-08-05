<?php
$route = app('router');

$route->get('/', 'PermintaanTambahSDM@index')->name('data');
$route->get('/lihat/{uuid?}', 'PermintaanTambahSDM@lihat')->name('lihat');
$route->match(['get', 'post'], '/tambah', 'PermintaanTambahSDM@tambah')->name('tambah');
$route->match(['get', 'post'], '/ubah/{uuid?}', 'PermintaanTambahSDM@ubah')->name('ubah');
$route->get('/formulir/{uuid?}', 'PermintaanTambahSDM@formulirPermintaanTambahSDM')->name('formulir');
$route->match(['get', 'post'], '/hapus/{uuid?}', 'PermintaanTambahSDM@hapus')->name('hapus');
