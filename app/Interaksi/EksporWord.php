<?php

namespace App\Interaksi;

use PhpOffice\PhpWord\TemplateProcessor;

class EksporWord
{
    public static function eksporWordStream($contoh, $data, $filename)
    {
        extract(Rangka::obyekPermintaanRangka());

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');

        echo '<p>Memeriksa formulir.</p>';

        $storage = $app->filesystem;

        abort_unless($storage->exists("contoh/{$contoh}"), 404, 'Berkas Contoh Formulir Tidak Ditemukan.');

        $templateProcessor = new TemplateProcessor($app->storagePath("app/contoh/{$contoh}"));

        echo '<p>Menyiapkan formulir.</p>';

        $templateProcessor->setValues($data);

        $templateProcessor->saveAs($app->storagePath("app/unduh/{$filename}"));

        return $app->redirect->to($storage->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)));

        exit();
    }
}
