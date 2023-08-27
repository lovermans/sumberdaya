<?php
$route = app('router');

$route->get('/data/{uuid?}', 'Penilaian@index')->name('data');
$route->get('/lihat/{uuid?}', 'Penilaian@lihat')->name('lihat');
$route->match(['get', 'post'], '/tambah/{lap_uuid?}', 'Penilaian@tambah')->name('tambah');
$route->match(['get', 'post'], '/ubah/{uuid?}', 'Penilaian@ubah')->name('ubah');
$route->match(['get', 'post'], '/unggah', 'Penilaian@unggahPenilaianSDM')->name('unggah');
$route->get('/contoh-unggah', 'Penilaian@contohUnggahPenilaianSDM')->name('contoh-unggah');
