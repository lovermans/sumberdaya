<?php

namespace App\Interaksi\SDM;

use App\Interaksi\KodeQR;
use App\Interaksi\Rangka;

class SDMBerkas
{
    public static function simpanFotoSDM($foto, $no_absen)
    {
        extract(Rangka::obyekPermintaanRangka());

        $foto->storeAs('sdm/foto-profil', $no_absen . '.webp');
    }

    public static function simpanBerkasSDM($berkas, $no_absen)
    {
        extract(Rangka::obyekPermintaanRangka());

        $berkas->storeAs('sdm/berkas', $no_absen . '.pdf');
    }

    public static function ambilFotoSDM($berkas_foto_profil)
    {
        extract(Rangka::obyekPermintaanRangka());

        abort_unless($berkas_foto_profil && $app->filesystem->exists("sdm/foto-profil/{$berkas_foto_profil}"), 404, 'Foto Profil tidak ditemukan.');

        $jalur = $app->storagePath("app/sdm/foto-profil/{$berkas_foto_profil}");

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->file($jalur, [
            'Cache-Control' => 'max-age=31536000',
            'Content-Disposition' => 'inline',
            'Content-Type' => $app->files->mimeType($jalur),
        ]);
    }

    public static function buatKartuIDSDM($akun)
    {
        extract(Rangka::obyekPermintaanRangka());

        $namaSdm = $akun->sdm_nama;
        $str = str();

        $batasi = $str->limit($namaSdm, 20, '');
        $sukuKata = preg_split('/\s/', $batasi, -1, PREG_SPLIT_NO_EMPTY);
        $kataAkhir = ' ' . $str->limit(end($sukuKata), 1, '');

        if ($batasi === $namaSdm) {
            $namaKartu = $namaSdm;
        } elseif (count($sukuKata) === 1) {
            $namaKartu = $sukuKata[0];
        } else {
            $namaKartu = $str->finish(implode(" ", array_slice($sukuKata, 0, -1)), $kataAkhir);
        }

        $nama = $str->of($namaKartu)->before(',')->title();
        $kontak = 'BEGIN:VCARD' . "\n";
        $kontak .= 'VERSION:2.1' . "\n";
        $kontak .= 'FN:' . $nama . "\n";
        $kontak .= 'TEL;WORK;VOICE:' . $akun->sdm_telepon . "\n";
        $kontak .= 'EMAIL:' . $akun->email . "\n";
        $kontak .= 'END:VCARD';
        $outerFrame = 4;

        $frame = KodeQR::buatKontakQRSDM($kontak);

        $h = count($frame);
        $w = strlen($frame[0]);
        $imgW = $w + 2 * $outerFrame;
        $imgH = $h + 2 * $outerFrame;
        $base_image = imagecreate($imgW, $imgH);
        $col[0] = imagecolorallocate($base_image, 255, 255, 255);
        $col[1] = imagecolorallocate($base_image, 0, 0, 0);

        imagefill($base_image, 0, 0, $col[0]);

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                if ($frame[$y][$x] == '1') {
                    imagesetpixel($base_image, $x + $outerFrame, $y + $outerFrame, $col[1]);
                }
            }
        }

        $target_image = imagecreate(246, 246);

        imagecopyresized($target_image, $base_image, 0, 0, 0, 0, 246, 246, $imgW, $imgH);
        imagedestroy($base_image);

        $storage = $app->filesystem;
        $no_absen = $akun->sdm_no_absen;

        if ($storage->exists("sdm/kartu-identitas-digital/{$akun->penempatan_lokasi}.jpg")) {
            $dest = imagecreatefromjpeg($app->storagePath("app/sdm/kartu-identitas-digital/{$akun->penempatan_lokasi}.jpg"));
        } else {
            $dest = imagecreatefromjpeg($app->storagePath("app/sdm/kartu-identitas-digital/KKA-WR.jpg"));
        }

        if ($storage->exists("sdm/foto-profil/{$no_absen}.webp")) {
            $src = imagecreatefromwebp($app->storagePath("app/sdm/foto-profil/{$no_absen}.webp"));
        } else {
            $src = imagecreatefromjpeg($app->storagePath("app/sdm/foto-profil/blank.jpg"));
        }

        $color = imagecolorallocatealpha($dest, 0, 0, 0, 13);

        $font = $app->resourcePath('css\font\Roboto-Regular.ttf');
        $font2 = $app->resourcePath('css\font\Roboto-Medium.ttf');

        $box = imagettfbbox(32, 0, $font, $no_absen);
        $text_width = abs($box[2]) - abs($box[0]);
        $x = (550 - $text_width) / 2;

        $box2 = imagettfbbox(37.5, 0, $font2, $nama);
        $text_width2 = abs($box2[2]) - abs($box2[0]);
        $x2 = (550 - $text_width2) / 2;

        imagettftext($dest, 32, 0, $x, 660, $color, $font, $no_absen);
        imagettftext($dest, 37.5, 0, $x2, 720, $color, $font2, $nama);
        imagecopymerge($dest, $src, 125, 200, 0, 0, 300, 400, 100);
        imagecopymerge($dest,  $target_image, 835, 480, 0, 0, 246, 246, 100);

        header('Content-Type: image/jpg');
        header('Content-Disposition: attachment; filename=kartu-sdm-' . $no_absen . '.jpg');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Expires: 0');
        header('Pragma: no-cache');

        imagejpeg($dest);
        imagedestroy($dest);
        imagedestroy($src);
        imagedestroy($target_image);

        exit();
    }

    public static function unduhBerkasProfilSDM($berkas = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        $permintaanBerkas = urldecode($reqs->path());

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        abort_unless($berkas && $app->filesystem->exists("{$permintaanBerkas}"), 404, 'Berkas tidak ditemukan.');

        $jalur = $app->storagePath("app/{$permintaanBerkas}");

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->file($jalur, [
            'Content-Disposition' => 'inline',
            'Content-Type' => $app->files->mimeType($jalur),
        ]);
    }
}
