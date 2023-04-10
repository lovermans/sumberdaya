<?php
$route = app('router');

$route->get('/', 'Posisi@index')->name('data');
$route->get('/lihat/{uuid?}', 'Posisi@lihat')->name('lihat');
$route->match(['get', 'post'], '/tambah', 'Posisi@tambah')->name('tambah');
$route->match(['get', 'post'], '/ubah/{uuid?}', 'Posisi@ubah')->name('ubah');
$route->match(['get', 'post'], '/unggah', 'Berkas@unggahPosisiSDM')->name('unggah');
$route->get('/contoh-unggah', 'Berkas@contohUnggahPosisiSDM')->name('contoh-unggah');
