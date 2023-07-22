<?php

namespace App\Interaksi;

use Illuminate\Validation\Rule;

class ValidasiPermintaan
{
    public static function validasiPermintaanDataPengaturan($permintaan)
    {
        extract(Umum::obyekPermintaanUmum());
        return $app->validator->make(
            $permintaan,
            [
                'kata_kunci' => ['sometimes', 'nullable', 'string'],
                'atur_jenis.*' => ['sometimes', 'nullable', 'string', 'max:20',],
                'atur_butir.*' => ['sometimes', 'nullable', 'string', 'max:40'],
                'atur_status.*' => ['sometimes', 'nullable', 'string', Rule::in(['', 'AKTIF', 'NON-AKTIF'])],
                'bph' => ['sometimes', 'nullable', 'numeric', Rule::in([25, 50, 75, 100])],
                'urut.*' => ['sometimes', 'nullable', 'string']
            ],
            [
                'atur_status.*.string' => 'Status Pengaturan urutan #:position wajib berupa karakter.',
                'atur_status.*.in' => 'Status Pengaturan urutan #:position tidak sesuai daftar.',
                'atur_butir.*.string' => 'Butir Pengaturan urutan #:position wajib berupa karakter.',
                'atur_butir.*.max' => 'Butir Pengaturan urutan #:position maksimal 40 karakter.',
                'atur_jenis.*.string' => 'Butir Pengaturan urutan #:position wajib berupa karakter.',
                'atur_jenis.*.max' => 'Butir Pengaturan urutan #:position maksimal 20 karakter.',
                'kata_kunci.*' => 'Kata Kunci Pencarian harus berupa karakter',
                'bph.*' => 'Baris Per halaman tidak sesuai daftar.',
                'urut.*.string' => 'Butir Pengaturan urutan #:position wajib berupa karakter.'
            ]
        );
    }
}
