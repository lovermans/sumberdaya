<?php
$route = app('router');

$route->get('/', 'Pelanggaran@index')->name('data');
$route->get('/lihat/{uuid?}', 'Pelanggaran@lihat')->name('lihat');
$route->match(['get', 'post'], '/tambah', 'Pelanggaran@tambah')->name('tambah');
$route->match(['get', 'post'], '/ubah/{uuid?}', 'Pelanggaran@ubah')->name('ubah');
$route->get('/berkas/{berkas?}', 'Berkas@berkas')->name('berkas');
