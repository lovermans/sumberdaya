<?php

namespace App\Interaksi;

class Rangka
{
    public static function obyekPermintaanRangka($butuhPengguna = false)
    {
        $app = app();
        $reqs = $app->request;

        return [
            'app' => $app,
            'reqs' => $reqs,
            'pengguna' => $butuhPengguna ? $reqs->user() : null,
        ];
    }

    public static function statusBerhasil()
    {
        return 'Data berhasil diperbarui. Mohon periksa ulang data.';
    }
}
