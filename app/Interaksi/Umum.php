<?php

namespace App\Interaksi;

class Umum
{
    public static function obyekPermintaanUmum()
    {
        return Rangka::obyekPermintaanRangka();
    }

    public static function statusBerhasil()
    {
        return 'Data berhasil diperbarui. Mohon periksa ulang data.';
    }
}
