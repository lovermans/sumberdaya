<?php

namespace App\Interaksi;

require_once __DIR__ . '/../Tambahan/PHPQRCode/qrlib.php';

use QRcode;

class KodeQR
{
    public static function buatKontakQRSDM($kontak)
    {
        return QRcode::text($kontak, false, QR_ECLEVEL_M);
    }
}
