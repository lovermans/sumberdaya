<?php

$route = app('router');

$route->get('/data/{uuid?}', 'Kepuasan@index')->name('data');
$route->get('/lihat/{uuid?}', 'Kepuasan@lihat')->name('lihat');
$route->match(['get', 'post'], '/tambah/{lap_uuid?}', 'Kepuasan@tambah')->name('tambah');
$route->match(['get', 'post'], '/ubah/{uuid?}', 'Kepuasan@ubah')->name('ubah');
$route->match(['get', 'post'], '/unggah', 'Kepuasan@unggahKepuasanSDM')->name('unggah');
$route->get('/contoh-unggah', 'Kepuasan@contohUnggahKepuasanSDM')->name('contoh-unggah');
