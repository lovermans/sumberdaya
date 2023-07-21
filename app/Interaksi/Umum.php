<?php

namespace App\Interaksi;

class Umum
{
    public static function obyekLaravel()
    {
        $app = app();
        $reqs = $app->request;

        return [
            'app' => $app,
            'reqs' => $reqs,
            'pengguna' => $reqs->user(),
            'respon' => $app->make('Illuminate\Contracts\Routing\ResponseFactory'),
            'view' => $app->view,
            'str' => str()
        ];
    }

    public static function hapusBerkasUnduhanLama()
    {
        extract(static::obyekLaravel());

        $storage = $app->filesystem;

        $berkas_unduh = array_filter($storage->files('unduh'), function ($berkas) {
            return $berkas !== 'unduh/.gitignore';
        });

        if ($berkas_unduh) {
            $date = $app->date;

            foreach ($berkas_unduh as $lama) {
                $tgl_berkas = $date->parse($storage->lastModified($lama));
                $batas_waktu = $date->today()->subDays(2);
                if ($tgl_berkas->lte($batas_waktu)) {
                    $storage->delete($lama);
                }
            }
        }
    }
}
