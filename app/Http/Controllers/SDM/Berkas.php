<?php

namespace App\Http\Controllers\SDM;

class Berkas
{
    public function berkas($berkas = null)
    {
        $app = app();
        $pengguna = $app->request->user();
        
        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pengurus SDM');
        
        abort_unless($berkas && $app->filesystem->exists("sdm/berkas/{$berkas}"), 404, 'Berkas tidak ditemukan');
        
        $jalur = $app->storagePath("app/sdm/berkas/{$berkas}");
        
        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->file($jalur, [
            'Content-Disposition' => 'inline',
            'Content-Type' => $app->files->mimeType($jalur),
        ]);
    }
    
    public function fotoProfil($berkas_foto_profil = null)
    {
        $app = app();
        
        abort_unless($berkas_foto_profil && $app->filesystem->exists("sdm/foto-profil/{$berkas_foto_profil}"), 404, 'Foto Profil tidak ditemukan');
        
        $jalur = $app->storagePath("app/sdm/foto-profil/{$berkas_foto_profil}");
        
        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->file($jalur, [
            'Cache-Control' => 'max-age=31536000',
            'Content-Disposition' => 'inline',
            'Content-Type' => $app->files->mimeType($jalur),
        ]);
    }

    public function panduan()
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        
        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, 'SDM'), 403, 'Akses dibatasi hanya untuk Pengguna Aplikasi SDM');

        $storage = $app->filesystem;
        
        $dokumenPengurus = match ($pengguna->sdm_hak_akses) {
            'SDM-PENGURUS', 'SDM-MANAJEMEN' => $app->filesystem->directories('sdm/panduan-pengurus'),
            default => null
        };
        
        $dokumenUmum = $storage->directories('sdm/panduan-umum');
        
        $data = [
            'dokumenUmum' => $dokumenUmum,
            'dokumenPengurus' => $dokumenPengurus,
        ];
        
        $HtmlPenuh = $app->view->make('sdm.dokumen-resmi', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }
}
