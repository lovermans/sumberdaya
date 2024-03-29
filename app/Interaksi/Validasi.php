<?php

namespace App\Interaksi;

use Illuminate\Validation\Rule;

class Validasi
{
    public static function validasiUmum($sumber, $aturan, $pesanKesalahan)
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->validator->make($sumber, $aturan, $pesanKesalahan);
    }

    public static function validasiPermintaanDataPengaturan($permintaan)
    {
        return static::validasiUmum(
            $permintaan,
            [
                '*.kata_kunci' => ['sometimes', 'nullable', 'string'],
                '*.atur_jenis.*' => ['sometimes', 'nullable', 'string', 'max:20'],
                '*.atur_butir.*' => ['sometimes', 'nullable', 'string', 'max:40'],
                '*.atur_status.*' => ['sometimes', 'nullable', 'string', Rule::in(['', 'AKTIF', 'NON-AKTIF'])],
                '*.bph' => ['sometimes', 'nullable', 'numeric', Rule::in([25, 50, 75, 100])],
                '*.urut.*' => ['sometimes', 'nullable', 'string'],
            ],
            static::pesanValidasiArrayPengaturan()
        );
    }

    public static function validasiTambahDataPengaturan($permintaan)
    {
        return static::validasiUmum(
            $permintaan,
            [
                '*.atur_butir' => ['required', 'string', 'max:40', Rule::unique('aturs')->where(function ($query) use ($permintaan) {
                    $query->where('atur_jenis', $permintaan[0]['atur_jenis']);
                })],
                '*.atur_id_pembuat' => ['required', 'string', 'exists:sdms,sdm_no_absen'],
                ...static::dasarValidasiPengaturan(),
            ],
            static::pesanValidasiArrayPengaturan()
        );
    }

    public static function validasiUbahDataPengaturan($uuid, $permintaan)
    {
        return static::validasiUmum(
            $permintaan,
            [
                '*.atur_butir' => ['required', 'string', 'max:40', Rule::unique('aturs')->where(function ($query) use ($permintaan, $uuid) {
                    $query->where('atur_jenis', $permintaan[0]['atur_jenis'])->whereNot('atur_uuid', $uuid);
                })],
                '*.atur_id_pengubah' => ['required', 'string', 'exists:sdms,sdm_no_absen'],
                ...static::dasarValidasiPengaturan(),
            ],
            static::pesanValidasiArrayPengaturan()
        );
    }

    public static function pesanValidasiArrayPengaturan()
    {
        return [
            '*.atur_jenis.*' => 'Jenis Pengaturan urutan ke-:position wajib berupa karakter maksimal 20 karakter.',
            '*.atur_butir.*' => 'Butir Pengaturan urutan ke-:position wajib berupa karakter maksimal 40 karakter.',
            '*.atur_status.*' => 'Status Pengaturan urutan ke-:position wajib berupa karakter dan terdaftar.',
            '*.atur_detail.*' => 'Keterangan Pengaturan urutan ke-:position wajib berupa karakter.',
            '*.kata_kunci.*' => 'Kata Kunci Pencarian harus berupa karakter',
            '*.bph.*' => 'Baris Per halaman tidak sesuai daftar.',
            '*.urut.*' => 'Butir Pengaturan urutan ke-:position wajib berupa karakter.',
            '*.atur_id_pengunggah.*' => 'ID Pengunggah urutan ke-:position wajib berupa karakter dan terdaftar.',
            '*.atur_id_pembuat.*' => 'ID Pembuat urutan ke-:position wajib berupa karakter dan terdaftar.',
            '*.atur_id_pengubah.*' => 'ID Pengubah urutan ke-:position wajib berupa karakter dan terdaftar.',
            '*.atur_diunggah.*' => 'Waktu Unggah urutan ke-:position wajib berupa tanggal.',
        ];
    }

    public static function dasarValidasiPengaturan()
    {
        return [
            '*.atur_jenis' => ['required', 'string', 'max:20'],
            '*.atur_detail' => ['sometimes', 'nullable', 'string'],
            '*.atur_status' => ['required', 'string', Rule::in(['AKTIF', 'NON-AKTIF'])],
        ];
    }

    public static function validasiBerkasImporDataPengaturan($permintaan)
    {
        return static::validasiUmum(
            $permintaan,
            [
                'atur_unggah' => ['required', 'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            ],
            [
                'atur_unggah.*' => 'Berkas yang diunggah wajib berupa file excel (.xlsx).',
            ]
        );
    }

    public static function validasiImporDataPengaturan($permintaan)
    {
        return static::validasiUmum(
            $permintaan,
            [
                '*.atur_butir' => ['required', 'string', 'max:40'],
                '*.atur_id_pengunggah' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                '*.atur_diunggah' => ['sometimes', 'nullable', 'date'],
                ...static::dasarValidasiPengaturan(),
            ],
            static::pesanValidasiArrayPengaturan()
        );
    }

    public static function validasiHapusDataDB($permintaan)
    {
        return static::validasiUmum(
            $permintaan,
            [
                'alasan' => ['required', 'string'],
                'id_penghapus' => ['required', 'string'],
                'waktu_dihapus' => ['required', 'date'],
            ],
            [
                'alasan' => 'Alasan Penghapusan wajib diisi.',
                'id_penghapus' => 'ID Penghapus tidak tersedia.',
                'waktu_dihapus' => 'Waktu Dihapus tidak tersedia.',
            ]
        );
    }
}
