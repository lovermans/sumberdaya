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

    public static function suratKeteranganSDM($uuid = null)
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

    public static function formulirPermintaanTambahSDM($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $permin = SDMDBQuery::ambilDBPermintaanTambahSDM()
            ->when($lingkupIjin, function ($query, $lingkupIjin) {
                $query->whereIn('tambahsdm_penempatan', $lingkupIjin);
            })->where('tambahsdm_uuid', $uuid)->first();

        abort_unless($permin, 404, 'Data Permintaan Tambah SDM tidak ditemukan.');

        $no_permin = $permin->tambahsdm_no;
        $date = $app->date;
        $str = str();

        $data = [
            'tambahsdm_no' => $no_permin,
            'sdm_nama' => $str->limit($permin->sdm_nama, 30),
            'tambahsdm_sdm_id' => $permin->tambahsdm_sdm_id,
            'tambahsdm_posisi' => $str->limit($permin->tambahsdm_posisi, 30),
            'tambahsdm_jumlah' => $permin->tambahsdm_jumlah,
            'tambahsdm_alasan' => $str->limit($permin->tambahsdm_alasan, 100),
            'tambahsdm_tgl_diusulkan' => strtoupper($date->make($permin->tambahsdm_tgl_diusulkan)?->translatedFormat('d F Y')),
            'tambahsdm_tgl_dibutuhkan' => strtoupper($date->make($permin->tambahsdm_tgl_dibutuhkan)?->translatedFormat('d F Y'))
        ];

        $argumen = [
            'contoh' => 'permintaan-tambah-sdm.docx',
            'data' => $data,
            'filename' => 'permintaan-tambah-sdm-' . $no_permin . '.docx'
        ];

        return EksporWord::eksporWordStream(...$argumen);
    }

    public static function formulirPenilaianSDM($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $permin = SDMDBQuery::ambilDataPenempatanSDM(array_filter(explode(',', $pengguna->sdm_ijin_akses)), $uuid);

        abort_unless($permin, 404, 'Data Permintaan Tambah SDM tidak ditemukan.');

        $no_absen = $permin->penempatan_no_absen;

        $data = [
            'sdm_nama' => str($permin->sdm_nama)->limit(30),
            'sdm_no_absen' => $no_absen,
            'sdm_jabatan' => $permin->penempatan_posisi
        ];

        $argumen = [
            'contoh' => 'penilaian-kinerja.docx',
            'data' => $data,
            'filename' => 'penilaian-kinerja-' . $no_absen . '.docx'
        ];

        return EksporWord::eksporWordStream(...$argumen);
    }

    public static function formulirPerubahanStatusSDM($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $permin = SDMDBQuery::ambilDataPenempatanSDM(array_filter(explode(',', $pengguna->sdm_ijin_akses)), $uuid);

        abort_unless($permin, 404, 'Data Permintaan Tambah SDM tidak ditemukan.');

        $no_absen = $permin->penempatan_no_absen;

        $data = [
            'sdm_nama' => str($permin->sdm_nama)->limit(30),
            'sdm_no_absen' => $no_absen,
            'sdm_jabatan' => $permin->penempatan_posisi,
            'sdm_kontrak' => $permin->penempatan_kontrak,
            'sdm_ke' => $permin->penempatan_ke,
            'sdm_golongan' => $permin->penempatan_golongan,
            'sdm_lokasi' => $permin->penempatan_lokasi,
        ];

        $argumen = [
            'contoh' => 'perubahan-status-sdm.docx',
            'data' => $data,
            'filename' => 'perubahan-status-sdm-' . $no_absen . '.docx'
        ];

        return EksporWord::eksporWordStream(...$argumen);
    }

    public static function PKWTSDM($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $permin = SDMDBQuery::ambilDataPenempatanSDM(array_filter(explode(',', $pengguna->sdm_ijin_akses)), $uuid);

        abort_unless($permin, 404, 'Data Permintaan Tambah SDM tidak ditemukan.');

        $date = $app->date;

        $data = [
            'sdm_nama' => str($permin->sdm_nama)->limit(30),
            'sdm_jabatan' => $permin->penempatan_posisi,
            'sdm_tgl_lahir' => $permin->sdm_tempat_lahir . ', ' . strtoupper($date->make($permin->sdm_tgl_lahir)?->translatedFormat('d F Y')),
            'sdm_kelamin' => $permin->sdm_kelamin == 'L' ? 'LAKI - LAKI' : 'PEREMPUAN',
            'sdm_alamat' => "{$permin->sdm_alamat}, {$permin->sdm_alamat_kelurahan}, {$permin->sdm_alamat_kecamatan}, {$permin->sdm_alamat_kota}, {$permin->sdm_alamat_provinsi}.",
            'sdm_mulai' => strtoupper($date->make($permin->penempatan_mulai)?->translatedFormat('d F Y')),
            'sdm_sampai' => strtoupper($date->make($permin->penempatan_selesai)?->translatedFormat('d F Y')),
        ];

        $argumen = [
            'contoh' => 'pkwt.docx',
            'data' => $data,
            'filename' => 'pkwt-' . $permin->penempatan_no_absen . '.docx'
        ];

        return EksporWord::eksporWordStream(...$argumen);
    }
}
