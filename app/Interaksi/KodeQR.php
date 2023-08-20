<?php

namespace App\Interaksi;

use PHPQRCode\QRcode;
use PHPQRCode\Constants;

class KodeQR
{
    public static function buatKontakQRSDM($kontak)
    {
        return QRcode::text($kontak, false, Constants::QR_ECLEVEL_M);
    }
}
