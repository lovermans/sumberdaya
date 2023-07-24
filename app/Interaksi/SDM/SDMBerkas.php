<?php

namespace App\Interaksi\SDM;

use App\Interaksi\Rangka;

trait SDMBerkas
{
    public function simpanFotoSDM($foto, $no_absen)
    {
        extract(Rangka::obyekPermintaanRangka());

        $foto->storeAs('sdm/foto-profil', $no_absen . '.webp');
    }

    public function simpanBerkasSDM($berkas, $no_absen)
    {
        extract(Rangka::obyekPermintaanRangka());

        $berkas->storeAs('sdm/berkas', $no_absen . '.pdf');
    }
}
