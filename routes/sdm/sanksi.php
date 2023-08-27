<?php
$route = app('router');

$route->get('/data/{uuid?}', 'Sanksi@index')->name('data');
$route->get('/lihat/{uuid?}', 'Sanksi@lihat')->name('lihat');
$route->match(['get', 'post'], '/tambah/{lap_uuid?}', 'Sanksi@tambah')->name('tambah');
$route->match(['get', 'post'], '/ubah/{uuid?}', 'Sanksi@ubah')->name('ubah');
$route->match(['get', 'post'], '/unggah', 'Sanksi@unggahSanksiSDM')->name('unggah');
$route->get('/contoh-unggah', 'Sanksi@contohUnggahSanksiSDM')->name('contoh-unggah');
$route->match(['get', 'post'], '/hapus/{uuid?}', 'Sanksi@hapus')->name('hapus');
