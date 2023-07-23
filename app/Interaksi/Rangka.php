<?php

namespace App\Interaksi;

class Rangka
{
    public static function obyekPermintaanRangka()
    {
        $app = app();
        $reqs = $app->request;

        return [
            'app' => $app,
            'reqs' => $reqs,
            'pengguna' => $reqs->user(),
        ];
    }
}
