<?php
$route = app('router');

$route->get('/', 'Posisi@index')->name('data');
$route->get('/lihat/{uuid?}', 'Posisi@lihat')->name('lihat');
$route->match(['get', 'post'], '/tambah', 'Posisi@tambah')->name('tambah');
$route->match(['get', 'post'], '/ubah/{uuid?}', 'Posisi@ubah')->name('ubah');
$route->match(['get', 'post'], '/unggah', 'Posisi@unggahPosisiSDM')->name('unggah');
$route->get('/contoh-unggah', 'Posisi@contohUnggahPosisiSDM')->name('contoh-unggah');
