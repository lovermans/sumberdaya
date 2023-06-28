<?php
$route = app('router');

$route->get('/', 'Sanksi@index')->name('data');
$route->get('/lihat/{uuid?}', 'Sanksi@lihat')->name('lihat');
$route->match(['get', 'post'], '/tambah/{lap_uuid?}', 'Sanksi@tambah')->name('tambah');
$route->match(['get', 'post'], '/ubah/{uuid?}', 'Sanksi@ubah')->name('ubah');
$route->match(['get', 'post'], '/unggah', 'Berkas@unggahSanksiSDM')->name('unggah');
$route->get('/contoh-unggah', 'Berkas@contohUnggahSanksiSDM')->name('contoh-unggah');
$route->get('/berkas/{berkas?}', 'Berkas@berkas')->name('berkas');
