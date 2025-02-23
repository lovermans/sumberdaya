<?php

namespace App\Interaksi;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QROutputInterface;

class KodeQR
{
    public static function buatKontakQRSDM($kontak)
    {
        $options = new QROptions;

        // $outputType can be one of: GDIMAGE_BMP, GDIMAGE_GIF, GDIMAGE_JPG, GDIMAGE_PNG, GDIMAGE_WEBP
        $options->outputType          = QROutputInterface::GDIMAGE_WEBP;
        $options->quality             = 90;
        // the size of one qr module in pixels
        $options->scale               = 20;
        $options->bgColor             = [200, 150, 200];
        $options->returnResource = true;

        return (new QRCode($options))->render($kontak);
    }
}
