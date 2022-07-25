<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'sdm', 'as' => 'sdm.'], function () {
    Route::group(['middleware' => 'can:sdm-pengurus'], function () {
        Route::view('/', 'sdm.mulai')->name('mulai');
        Route::get('/foto-profil/{berkas_foto_profil}', 'App\Http\Controllers\Sdm\Berkas@fotoProfil')->name('tautan-foto-profil');
    });
});