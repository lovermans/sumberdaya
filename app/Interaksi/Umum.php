<?php

namespace App\Interaksi;

trait Umum
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
