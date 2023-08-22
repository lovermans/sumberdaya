<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

$route = app('router');

$route->get('/', 'App\Http\Controllers\SumberDaya@mulai')->name('mulai');
$route->view('/perlu-javascript', 'perlu-javascript')->name('perlu-javascript');
$route->get('/tentang-aplikasi', 'App\Http\Controllers\SumberDaya@tentangAplikasi')->name('tentang-aplikasi');
$route->get('/mulai-aplikasi', 'App\Http\Controllers\SumberDaya@mulaiAplikasi')->name('mulai-aplikasi');
$route->get('/pwa-manifest.json', 'App\Http\Controllers\SumberDaya@pwaManifest')->name('pwa-manifest');
$route->get('/service-worker.js', 'App\Http\Controllers\SumberDaya@serviceWorker')->name('service-worker');
$route->view('/antarmuka', 'antarmuka')->name('antarmuka');
$route->view('/offline', 'offline')->name('offline');
$route->get('/unduh/{berkas?}', 'App\Http\Controllers\SumberDaya@unduh')->name('unduh');
$route->get('/unduh-panduan/{berkas?}', 'App\Http\Controllers\SumberDaya@unduhPanduan')->where('berkas', '.*')->name('unduh.panduan')->middleware('signed');
// $route->get('/format-foto', 'App\Http\Controllers\SumberDaya@formatFoto')->name('format-foto');

require __DIR__ . '/auth.php';

$route->get('/komponen', 'App\Http\Controllers\SumberDaya@komponen')->name('komponen');

$route->get('/periksa-pengguna', 'App\Http\Controllers\SumberDaya@PeriksaPengguna')->name('periksa-pengguna');

$route->group(['prefix' => 'atur', 'as' => 'atur.', 'namespace' => 'App\Http\Controllers'], function () use ($route) {
    $route->get('/', 'Pengaturan@index')->name('data');
    $route->get('/lihat/{uuid?}', 'Pengaturan@lihat')->name('lihat');
    $route->match(['get', 'post'], '/tambah', 'Pengaturan@tambah')->name('tambah');
    $route->match(['get', 'post'], '/ubah/{uuid?}', 'Pengaturan@ubah')->name('ubah');
    $route->match(['get', 'post'], '/unggah', 'Pengaturan@unggah')->name('unggah');
    $route->get('/contoh-unggah', 'Pengaturan@contohUnggah')->name('contoh-unggah');
});

$route->group(['prefix' => 'sdm', 'as' => 'sdm.', 'namespace' => 'App\Http\Controllers\SDM'], base_path('routes/sdm/rangka.php'));
