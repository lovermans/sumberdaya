<?php
$route = app('router');

$route->get('/', 'Sanksi@index')->name('data');
$route->get('/lihat/{uuid?}', 'Sanksi@lihat')->name('lihat');
$route->match(['get', 'post'], '/tambah', 'Sanksi@tambah')->name('tambah');
$route->match(['get', 'post'], '/ubah/{uuid?}', 'Sanksi@ubah')->name('ubah');
$route->match(['get', 'post'], '/unggah', 'Sanksi@unggah')->name('unggah');
$route->get('/contoh-unggah', 'Sanksi@contohUnggah')->name('contoh-unggah');
