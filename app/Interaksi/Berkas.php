<?php

namespace App\Interaksi;

class Berkas
{
    public static function unduhBerkasUmum($berkas = null)
    {
        extract(Rangka::obyekPermintaanRangka());

        static::hapusBerkasUnduhanLama();

        abort_unless($berkas && $app->filesystem->exists("unduh/{$berkas}"), 404, 'Berkas Tidak Ditemukan.');

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->download($app->storagePath("app/unduh/{$berkas}"));
    }

    public static function unduhBerkasTerbatas($berkas = null)
    {
        extract(Rangka::obyekPermintaanRangka());

        static::hapusBerkasUnduhanLama();

        abort_unless($berkas && $app->filesystem->exists("{$berkas}"), 404, 'Berkas Tidak Ditemukan.');

        $jalur = $app->storagePath("app/{$berkas}");

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->file($jalur, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Expires' => '0',
            'Pragma' => 'no-cache',
            'Content-Disposition' => 'inline',
            'Content-Type' => $app->files->mimeType($jalur),
        ]);
    }

    public static function hapusBerkasUnduhanLama()
    {
        extract(Rangka::obyekPermintaanRangka());

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

    public static function hapusBerkasUnggahanLama()
    {
        extract(Rangka::obyekPermintaanRangka());

        $storage = $app->filesystem;

        $berkas_unggah = array_filter($storage->files('unggah'), function ($berkas) {
            return $berkas !== 'unggah/.gitignore';
        });

        if ($berkas_unggah) {
            $date = $app->date;

            foreach ($berkas_unggah as $lama) {
                $tgl_berkas = $date->parse($storage->lastModified($lama));
                $batas_waktu = $date->today()->subDays(2);
                if ($tgl_berkas->lte($batas_waktu)) {
                    $storage->delete($lama);
                }
            }
        }
    }

    public static function simpanBerkasImporExcelSementara($file, $namafile)
    {
        extract(Rangka::obyekPermintaanRangka());

        static::hapusBerkasUnggahanLama();

        $app->filesystem->putFileAs('unggah', $file, $namafile);
    }

    public static function ambilBerkasImporExcelSementara($namafile)
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->storagePath("app/unggah/{$namafile}");
    }
}
