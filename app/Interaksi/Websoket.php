<?php

namespace App\Interaksi;

use Throwable;
use App\Events\Umum;

class Websoket
{
    public static function siaranUmum($pesan)
    {
        try {
            Umum::broadcast($pesan)->toOthers();
        } catch (Throwable $e) {
        }
    }
}
