<?php

namespace App\Http\Controllers\Sdm;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class Berkas extends Controller
{
    public function fotoProfil($berkas_foto_profil)
    {   
        abort_if(!$berkas_foto_profil || !Storage::exists("sdm/foto-profil/{$berkas_foto_profil}"), 404);
        return response()->file(storage_path("app/sdm/foto-profil/{$berkas_foto_profil}"));
    }
}
