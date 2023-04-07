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
$route->get('/sdm/akun/{uuid?}', 'App\Http\Controllers\SumberDaya@akun')->name('akun');
$route->match(['get','post'],'/sdm/ubah-akun/{uuid?}', 'App\Http\Controllers\SumberDaya@ubahAkun')->name('ubah-akun');
$route->match(['get','post'],'/sdm/ubah-sandi', 'App\Http\Controllers\SumberDaya@ubahSandi')->name('ubah-sandi');
$route->get('/tentang-aplikasi', 'App\Http\Controllers\SumberDaya@tentangAplikasi')->name('tentang-aplikasi');
$route->view('/antarmuka','antarmuka')->name('antarmuka');
$route->get('/unduh/{berkas?}', 'App\Http\Controllers\SumberDaya@unduh')->name('unduh');
$route->get('/unduh-panduan/{berkas?}', 'App\Http\Controllers\SumberDaya@unduhPanduan')->where('berkas', '.*')->name('unduh.panduan')->middleware('signed');
$route->get('/sdm/unduh-kartu-sdm/{uuid?}', 'App\Http\Controllers\SumberDaya@unduhKartuSDM')->name('unduh.kartu-sdm');
$route->get('/sdm/formulir-serahterimasdm/{uuid?}','App\Http\Controllers\SumberDaya@formulirSerahTerimaSDMBaru')->name('formulir-serah-terima-sdm-baru');
$route->get('/sdm/formulir-persetujuangaji/{uuid?}','App\Http\Controllers\SumberDaya@formulirPersetujuanGaji')->name('formulir-persetujuan-gaji');
$route->get('/sdm/formulir-ttdokumentitipan/{uuid?}','App\Http\Controllers\SumberDaya@formulirTTDokumenTitipan')->name('formulir-tt-dokumen-titipan');
$route->get('/sdm/formulir-ttinventaris/{uuid?}','App\Http\Controllers\SumberDaya@formulirTTInventaris')->name('formulir-tt-inventaris');
$route->get('/sdm/formulir-pelepasan-sdm/{uuid?}','App\Http\Controllers\SumberDaya@formulirPelepasanSDM')->name('formulir-pelepasan-sdm');
$route->get('/sdm/surat-keterangan-sdm/{uuid?}','App\Http\Controllers\SumberDaya@suratKeteranganSDM')->name('surat-keterangan-sdm');
$route->get('/sdm/contoh-unggah', 'App\Http\Controllers\SumberDaya@contohUnggah')->name('contoh-unggah');
$route->match(['get', 'post'], '/sdm/unggah', 'App\Http\Controllers\SumberDaya@unggah')->name('unggah');
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
