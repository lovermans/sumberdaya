<?php

use Illuminate\Support\Facades\Route;

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
require __DIR__.'/auth.php';

Route::get('/','App\Http\Controllers\Pengelola@mulai')->name('mulai');
Route::view('/perlu-javascript','perlu-javascript')->name('perlu-javascript');
Route::get('/akun', 'App\Http\Controllers\Pengelola@akun')->middleware('auth')->name('akun');
Route::match(['get','post'],'/ubah-sandi', 'App\Http\Controllers\Pengelola@ubahSandi')->middleware('auth')->name('ubah-sandi');
Route::get('/tentang-aplikasi', 'App\Http\Controllers\Pengelola@tentangAplikasi')->name('tentang-aplikasi');
Route::view('/antarmuka','antarmuka')->name('antarmuka');
Route::group(['prefix' => 'atur', 'as' => 'atur.'], function () {
    Route::get('/', 'App\Http\Controllers\Pengaturan@index');
});

require __DIR__.'/sdm.php';
