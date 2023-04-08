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

$route->get('/','App\Http\Controllers\SumberDaya@mulai')->name('mulai');
$route->view('/perlu-javascript','perlu-javascript')->name('perlu-javascript');
$route->get('/tentang-aplikasi', 'App\Http\Controllers\SumberDaya@tentangAplikasi')->name('tentang-aplikasi');
$route->view('/antarmuka','antarmuka')->name('antarmuka');
$route->get('/unduh/{berkas?}', 'App\Http\Controllers\SumberDaya@unduh')->name('unduh');
$route->get('/unduh-panduan/{berkas?}', 'App\Http\Controllers\SumberDaya@unduhPanduan')->where('berkas', '.*')->name('unduh.panduan')->middleware('signed');
// $route->get('/format-foto', 'App\Http\Controllers\SumberDaya@formatFoto')->name('format-foto');

require __DIR__.'/auth.php';

$route->group(['prefix' => 'komponen', 'as' => 'komponen.'], function () use ($route) {
    $route->get('/avatar', function () {
        return response(view('menu')->fragment('avatar'))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'tbl-menu']);
    })->name('avatar');
    $route->get('/menu-avatar', function () {
        return response(view('menu')->fragment('menu-avatar'))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'menu-avatar']);
    })->name('menu-avatar');
    $route->get('/menu-aplikasi', function () {
        return response(view('menu')->fragment('menu-aplikasi'))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'menu-aplikasi']);
    })->name('menu-aplikasi');
    $route->get('/menu-pengaturan', function () {
        return response(view('pengaturan.navigasi'))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'menu-pengaturan']);
    })->name('menu-pengaturan');
    $route->get('/pilih-sumberdaya', function () {
        return response(view('menu')->fragment('pilih-sumber_daya'))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'pilih-sumber_daya']);
    })->name('pilih-sumberdaya');
    $route->get('/menu-sdm', function () {
        return response(view('sdm.navigasi'))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'navigasi-sdm']);
    })->name('menu-sdm');
})->middleware('auth');

$route->group(['prefix' => 'atur', 'as' => 'atur.', 'namespace' => 'App\Http\Controllers'], function () use ($route){
    $route->get('/', 'Pengaturan@index')->name('data');
    $route->get('/lihat/{uuid?}', 'Pengaturan@lihat')->name('lihat');
    $route->match(['get','post'],'/tambah','Pengaturan@tambah')->name('tambah');
    $route->match(['get','post'], '/ubah/{uuid?}', 'Pengaturan@ubah')->name('ubah');
    $route->match(['get','post'], '/unggah', 'Pengaturan@unggah')->name('unggah');
    $route->get('/contoh-unggah', 'Pengaturan@contohUnggah')->name('contoh-unggah');
});

$route->group(['prefix' => 'sdm', 'as' => 'sdm.', 'namespace' => 'App\Http\Controllers\SDM'], base_path('routes/sdm/rangka.php'));
