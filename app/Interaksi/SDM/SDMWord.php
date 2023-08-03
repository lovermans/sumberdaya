<?php

namespace App\Interaksi\SDM;

use App\Interaksi\EksporWord;
use App\Interaksi\Rangka;

class SDMWord
{
    public static function formulirSerahTerimaSDMBaru($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid, 401);

        $akun = SDMDBQuery::ambilDataAkun($uuid)->first();

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $ijin_akses));
        $no_absen_sdm = $pengguna->sdm_no_absen;
        $lingkup_lokasi = collect($akun?->penempatan_lokasi);
        $lingkup_akses = $lingkup_lokasi->intersect($lingkup)->count();
        $no_absen_akun = $akun->sdm_no_absen;

        abort_unless((blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0)) && ($no_absen_sdm !== $no_absen_akun), 403, 'Akses pengguna dibatasi.');

        $data = [
            'sdm_nama' => str($akun->sdm_nama)->limit(30),
            'sdm_no_absen' => $no_absen_akun,
            'sdm_tgl_gabung' => strtoupper($app->date->make($akun->sdm_tgl_gabung)?->translatedFormat('d F Y'))
        ];

        $argumen = [
            'contoh' => 'serah-terima-sdm-baru.docx',
            'data' => $data,
            'filename' => 'serah-terima-sdm-baru-' . $no_absen_akun . '.docx'
        ];

        return EksporWord::eksporWordStream(...$argumen);
    }

    public static function formulirPersetujuanGaji($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid, 401);

        $akun = $akun = SDMDBQuery::ambilDataAkun($uuid)->first();

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $ijin_akses));
        $no_absen_sdm = $pengguna->sdm_no_absen;
        $lingkup_lokasi = collect($akun?->penempatan_lokasi);
        $lingkup_akses = $lingkup_lokasi->intersect($lingkup)->count();
        $no_absen_akun = $akun->sdm_no_absen;

        abort_unless((blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0)) && ($no_absen_sdm !== $no_absen_akun), 403, 'Akses pengguna dibatasi.');

        $date = $app->date;

        $data = [
            'sdm_nama' => $akun->sdm_nama,
            'sdm_no_absen' => $no_absen_akun,
            'sdm_tgl_gabung' => strtoupper($date->make($akun->sdm_tgl_gabung)?->translatedFormat('d F Y')),
            'sdm_tgl_lahir' => $akun->sdm_tempat_lahir . ', ' . strtoupper($date->make($akun->sdm_tgl_lahir)?->translatedFormat('d F Y')),
            'sdm_kelamin' => $akun->sdm_kelamin == 'L' ? 'LAKI - LAKI' : 'PEREMPUAN',
            'sdm_alamat' => "{$akun->sdm_alamat}, {$akun->sdm_alamat_kelurahan}, {$akun->sdm_alamat_kecamatan}, {$akun->sdm_alamat_kota}, {$akun->sdm_alamat_provinsi}.",
        ];

        $argumen = [
            'contoh' => 'persetujuan-gaji.docx',
            'data' => $data,
            'filename' => 'persetujuan-gaji-' . $no_absen_akun . '.docx'
        ];

        return EksporWord::eksporWordStream(...$argumen);
    }

    public static function formulirTTDokumenTitipan($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid, 401);

        $akun = SDMDBQuery::ambilDataAkun($uuid)->first();

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $ijin_akses));
        $no_absen_sdm = $pengguna->sdm_no_absen;
        $lingkup_lokasi = collect($akun?->penempatan_lokasi);
        $lingkup_akses = $lingkup_lokasi->intersect($lingkup)->count();
        $no_absen_akun = $akun->sdm_no_absen;

        abort_unless((blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0)) && ($no_absen_sdm !== $no_absen_akun), 403, 'Akses pengguna dibatasi.');

        $date = $app->date;

        $data = [
            'sdm_nama' => $akun->sdm_nama,
            'sdm_no_absen' => $no_absen_akun,
            'sdm_tgl_gabung' => strtoupper($date->make($akun->sdm_tgl_gabung)?->translatedFormat('d F Y')),
            'sdm_tgl_lahir' => $akun->sdm_tempat_lahir . ', ' . strtoupper($date->make($akun->sdm_tgl_lahir)?->translatedFormat('d F Y')),
            'sdm_kelamin' => $akun->sdm_kelamin == 'L' ? 'LAKI - LAKI' : 'PEREMPUAN',
            'sdm_alamat' => "{$akun->sdm_alamat}, {$akun->sdm_alamat_kelurahan}, {$akun->sdm_alamat_kecamatan}, {$akun->sdm_alamat_kota}, {$akun->sdm_alamat_provinsi}.",
            'sdm_nama_dok' => $akun->sdm_nama_dok,
            'sdm_nomor_dok' => $akun->sdm_nomor_dok,
            'sdm_penerbit_dok' => $akun->sdm_penerbit_dok,
            'sdm_an_dok' => $akun->sdm_an_dok,
            'sdm_kadaluarsa_dok' => strtoupper($date->make($akun->sdm_kadaluarsa_dok)?->translatedFormat('d F Y')),
        ];

        $argumen = [
            'contoh' => 'tanda-terima-dokumen-titipan.docx',
            'data' => $data,
            'filename' => 'tanda-terima-dokumen-titipan-' . $no_absen_akun . '.docx'
        ];

        return EksporWord::eksporWordStream(...$argumen);
    }

    public static function formulirTTInventaris($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid, 401);

        $akun = SDMDBQuery::ambilDataAkun($uuid)->first();

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $ijin_akses));
        $no_absen_sdm = $pengguna->sdm_no_absen;
        $lingkup_lokasi = collect($akun?->penempatan_lokasi);
        $lingkup_akses = $lingkup_lokasi->intersect($lingkup)->count();
        $no_absen_akun = $akun->sdm_no_absen;

        abort_unless((blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0)) && ($no_absen_sdm !== $no_absen_akun), 403, 'Akses pengguna dibatasi.');

        $data = [
            'sdm_uk_seragam' => $akun->sdm_uk_seragam,
            'sdm_uk_sepatu' => $akun->sdm_uk_sepatu,
            'ket_tanda_terima' => "UNTUK KARYAWAN A.N {$akun->sdm_nama} - ({$no_absen_akun})",
        ];

        $argumen = [
            'contoh' => 'tanda-terima-inventaris.docx',
            'data' => $data,
            'filename' => 'tanda-terima-inventaris-' . $no_absen_akun . '.docx'
        ];

        return EksporWord::eksporWordStream(...$argumen);
    }

    public static function formulirPelepasanSDM($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid, 401);

        $akun = SDMDBQuery::ambilDataAkun($uuid)->first();

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $ijin_akses));
        $no_absen_sdm = $pengguna->sdm_no_absen;
        $lingkup_lokasi = collect($akun?->penempatan_lokasi);
        $lingkup_akses = $lingkup_lokasi->intersect($lingkup)->count();
        $no_absen_akun = $akun->sdm_no_absen;

        abort_unless((blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0)) && ($no_absen_sdm !== $no_absen_akun), 403, 'Akses pengguna dibatasi.');

        $data = [
            'sdm_nama' => str($akun->sdm_nama)->limit(30),
            'sdm_no_absen' => $no_absen_akun,
            'sdm_tgl_berhenti' => strtoupper($app->date->make($akun->sdm_tgl_berhenti)?->translatedFormat('d F Y')),
            'sdm_jenis_berhenti' => $akun->sdm_jenis_berhenti,
        ];

        $argumen = [
            'contoh' => 'pelepasan-karyawan.docx',
            'data' => $data,
            'filename' => 'pelepasan-karyawan-' . $no_absen_akun . '.docx'
        ];

        return EksporWord::eksporWordStream(...$argumen);
    }

    public function suratKeteranganSDM($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid, 401);

        $akun = SDMDBQuery::ambilDataAkun($uuid)->first();

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $ijin_akses));
        $no_absen_sdm = $pengguna->sdm_no_absen;
        $lingkup_lokasi = collect($akun?->penempatan_lokasi);
        $lingkup_akses = $lingkup_lokasi->intersect($lingkup)->count();
        $no_absen_akun = $akun->sdm_no_absen;

        abort_unless((blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0)) && ($no_absen_sdm !== $no_absen_akun), 403, 'Akses pengguna dibatasi.');

        $date = $app->date;

        $data = [
            'sdm_nama' => $akun->sdm_nama,
            'sdm_no_absen' => $no_absen_akun,
            'sdm_tgl_gabung' => strtoupper($date->make($akun->sdm_tgl_gabung)?->translatedFormat('d F Y')),
            'sdm_tgl_lahir' => $akun->sdm_tempat_lahir . ', ' . strtoupper($date->make($akun->sdm_tgl_lahir)?->translatedFormat('d F Y')),
            'sdm_kelamin' => $akun->sdm_kelamin == 'L' ? 'LAKI - LAKI' : 'PEREMPUAN',
            'sdm_alamat' => "{$akun->sdm_alamat}, {$akun->sdm_alamat_kelurahan}, {$akun->sdm_alamat_kecamatan}, {$akun->sdm_alamat_kota}, {$akun->sdm_alamat_provinsi}.",
            'sdm_tgl_berhenti' => strtoupper($date->make($akun->sdm_tgl_berhenti)?->translatedFormat('d F Y')) ?: 'saat surat ini diterbitkan',
            'paragraf_keterangan' => $akun->sdm_tgl_berhenti ? 'PT. Kepuh Kencana Arum mengapresiasi kontribusi dan dedikasi yang diberikan, selama bekerja yang bersangkutan menunjukkan kinerja yang baik untuk menunjang kesuksesan kerjanya' : 'Surat keterangan ini dibuat untuk ...[isi keterangan]',
        ];

        $argumen = [
            'contoh' => 'keterangan-kerja.docx',
            'data' => $data,
            'filename' => 'keterangan-kerja-' . $no_absen_akun . '.docx'
        ];

        return EksporWord::eksporWordStream(...$argumen);
    }
}
