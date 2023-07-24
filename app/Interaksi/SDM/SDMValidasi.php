<?php

namespace App\Interaksi\SDM;

use App\Interaksi\Rangka;
use Illuminate\Validation\Rule;

trait SDMValidasi
{
    public function validasiTambahDataSDM($permintaan)
    {
        extract(Rangka::obyekPermintaanRangka());

        return $app->validator->make(
            $permintaan,
            [
                '*.foto_profil' => ['sometimes', 'image'],
                '*.sdm_berkas' => ['sometimes', 'file', 'mimetypes:application/pdf'],
                '*.sdm_no_permintaan' => ['nullable', 'string', 'max:20', 'exists:tambahsdms,tambahsdm_no'],
                '*.sdm_no_absen' => ['required', 'string', 'max:10', 'unique:sdms,sdm_no_absen'],
                '*.sdm_id_atasan' => ['nullable', 'string', 'max:10', 'different:sdm_no_absen', 'exists:sdms,sdm_no_absen'],
                '*.sdm_tgl_gabung' => ['required', 'date'],
                '*.sdm_warganegara' => ['required', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                    return $query->where('atur_jenis', 'NEGARA');
                })],
                '*.sdm_no_ktp' => ['required', 'string', 'max:20'],
                '*.sdm_nama' => ['required', 'string', 'max:80'],
                '*.sdm_tempat_lahir' => ['required', 'string', 'max:40'],
                '*.sdm_tgl_lahir' => ['required', 'date'],
                '*.sdm_kelamin' => ['required', 'string', 'max:2', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                    return $query->where('atur_jenis', 'KELAMIN');
                })],
                '*.sdm_gol_darah' => ['nullable', 'string', 'max:2', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                    return $query->where('atur_jenis', 'GOLONGAN DARAH');
                })],
                '*.sdm_alamat' => ['required', 'string', 'max:120'],
                '*.sdm_alamat_rt' => ['nullable', 'numeric', 'min:0'],
                '*.sdm_alamat_rw' => ['nullable', 'numeric', 'min:0'],
                '*.sdm_alamat_kelurahan' => ['required', 'string', 'max:40'],
                '*.sdm_alamat_kecamatan' => ['required', 'string', 'max:40'],
                '*.sdm_alamat_kota' => ['required', 'string', 'max:40'],
                '*.sdm_alamat_provinsi' => ['required', 'string', 'max:40'],
                '*.sdm_alamat_kodepos' => ['nullable', 'string', 'max:10'],
                '*.sdm_agama' => ['required', 'string', 'max:20', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                    return $query->where('atur_jenis', 'AGAMA');
                })],
                '*.sdm_no_kk' => ['nullable', 'string', 'max:20'],
                '*.sdm_status_kawin' => ['required', 'string', 'max:10', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                    return $query->where('atur_jenis', 'STATUS MENIKAH');
                })],
                '*.sdm_jml_anak' => ['nullable', 'numeric', 'min:0'],
                '*.sdm_pendidikan' => ['required', 'string', 'max:10', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                    return $query->where('atur_jenis', 'PENDIDIKAN');
                })],
                '*.sdm_jurusan' => ['nullable', 'string', 'max:60'],
                '*.sdm_telepon' => ['required', 'string', 'max:40'],
                '*.email' => ['required', 'email'],
                '*.sdm_disabilitas' => ['required', 'string', 'max:30', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                    return $query->where('atur_jenis', 'DISABILITAS');
                })],
                '*.sdm_no_bpjs' => ['nullable', 'string', 'max:30'],
                '*.sdm_no_jamsostek' => ['nullable', 'string', 'max:30'],
                '*.sdm_no_npwp' => ['nullable', 'string', 'max:30'],
                '*.sdm_nama_bank' => ['nullable', 'string', 'max:20', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                    return $query->where('atur_jenis', 'BANK');
                })],
                '*.sdm_cabang_bank' => ['nullable', 'string', 'max:50'],
                '*.sdm_rek_bank' => ['nullable', 'string', 'max:40'],
                '*.sdm_nama_dok' => ['nullable', 'string', 'max:50'],
                '*.sdm_nomor_dok' => ['nullable', 'string', 'max:40'],
                '*.sdm_penerbit_dok' => ['nullable', 'string', 'max:60'],
                '*.sdm_an_dok' => ['nullable', 'string', 'max:80'],
                '*.sdm_kadaluarsa_dok' => ['nullable', 'date'],
                '*.sdm_uk_seragam' => ['nullable', 'string', 'max:10', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                    return $query->where('atur_jenis', 'UKURAN SERAGAM');
                })],
                '*.sdm_uk_sepatu' => ['nullable', 'numeric', 'min:0'],
                '*.sdm_ket_kary' => ['nullable', 'string'],
                '*.password' => ['nullable', 'string'],
                '*.sdm_id_pembuat' => ['nullable', 'string', 'max:10'],
            ],
            [
                '*.foto_profil.*' => 'Foto Profil wajib brupa berkas gambar atau hasil tangkap kamera.',
                '*.sdm_berkas.*' => 'Berkas yang diunggah wajib berupa berkas PDF.',
                '*.sdm_no_permintaan.*' => 'Nomor Permintaan baris ke-:position maksimal 20 karakter dan wajib terdaftar Permintaan SDM.',
                '*.sdm_no_absen.*' => 'Nomor Absen baris ke-:position maksimal 10 karakter.',
                '*.sdm_id_atasan.*' => 'ID Atasan baris ke-:position maksimal 10 karakter, berbeda dengan No Absen SDM dan terdaftar di data SDM.',
                '*.sdm_tgl_gabung.*' => 'Tanggal Bergabung baris ke-:position wajib berupa tanggal.',
                '*.sdm_warganegara.*' => 'Warga Negara baris ke-:position maksimal 20 karakter dan wajib terdaftar Pengaturan Umum.',
                '*.sdm_no_ktp.*' => 'No KTP Passport baris ke-:position maksimal 20 karakter.',
                '*.sdm_nama.*' => 'Nama SDM baris ke-:position maksimal 80 karakter.',
                '*.sdm_tempat_lahir.*' => 'Tempat Lahir baris ke-:position maksimal 40 karakter.',
                '*.sdm_tgl_lahir.*' => 'Tanggal Lahir baris ke-:position wajib berupa tanggal.',
                '*.sdm_kelamin.*' => 'Kelamin baris ke-:position maksimal 2 karakter dan wajib terdaftar Pengaturan Umum.',
                '*.sdm_gol_darah.*' => 'Golongan Darah baris ke-:position maksimal 2 karakter dan wajib terdaftar Pengaturan Umum.',
                '*.sdm_alamat.*' => 'Alamat baris ke-:position maksimal 120 karakter.',
                '*.sdm_alamat_rt.*' => 'Alamat RT baris ke-:position wajib berupa angka lebih dari 0.',
                '*.sdm_alamat_rw.*' => 'Alamat RW baris ke-:position wajib berupa angka lebih dari 0.',
                '*.sdm_alamat_kelurahan.*' => 'Kelurahan baris ke-:position maksimal 40 karakter.',
                '*.sdm_alamat_kecamatan.*' => 'Kecamatan baris ke-:position maksimal 40 karakter dan wajib terdaftar Pengaturan Umum.',
                '*.sdm_alamat_kota.*' => 'Kota baris ke-:position maksimal 40 karakter.',
                '*.sdm_alamat_provinsi.*' => 'Provinsi baris ke-:position maksimal 40 karakter.',
                '*.sdm_alamat_kodepos.*' => 'Kode Pos baris ke-:position maksimal 10 karakter.',
                '*.sdm_agama.*' => 'Agama baris ke-:position maksimal 20 karakter dan wajib terdaftar Pengaturan Umum.',
                '*.sdm_no_kk.*' => 'Nomor KK baris ke-:position maksimal 20 karakter dan wajib terdaftar Pengaturan Umum.',
                '*.sdm_status_kawin.*' => 'Status Menikah baris ke-:position maksimal 10 karakter dan wajib terdaftar Pengaturan Umum.',
                '*.sdm_jml_anak.*' => 'Jumlah Anak baris ke-:position wajib berupa angka lebih dari 0.',
                '*.sdm_pendidikan.*' => 'Pendidikan baris ke-:position maksimal 10 karakter dan wajib terdaftar Pengaturan Umum.',
                '*.sdm_jurusan.*' => 'Jurusan baris ke-:position maksimal 60 karakter.',
                '*.sdm_telepon.*' => 'Telepon baris ke-:position maksimal 40 karakter.',
                '*.email.*' => 'Email baris ke-:position wajib berupa email.',
                '*.sdm_disabilitas.*' => 'Disabilitas baris ke-:position maksimal 30 karakter dan wajib terdaftar Pengaturan Umum.',
                '*.sdm_no_bpjs.*' => 'No BPJS baris ke-:position maksimal 30 karakter.',
                '*.sdm_no_jamsostek.*' => 'No Jamsostek baris ke-:position maksimal 30 karakter.',
                '*.sdm_no_npwp.*' => 'NPWP baris ke-:position maksimal 30 karakter.',
                '*.sdm_nama_bank.*' => 'Nama Bank baris ke-:position maksimal 20 karakter dan wajib terdaftar Pengaturan Umum.',
                '*.sdm_cabang_bank.*' => 'Cabang Bank baris ke-:position maksimal 50 karakter.',
                '*.sdm_rek_bank.*' => 'Nomor Rekening Bank baris ke-:position maksimal 40 karakter.',
                '*.sdm_an_rek.*' => 'A.n Rekening baris ke-:position maksimal 40 karakter dan wajib terdaftar Pengaturan Umum.',
                '*.sdm_nama_dok.*' => 'Judul Dokumen Titipan baris ke-:position maksimal 50 karakter.',
                '*.sdm_nomor_dok.*' => 'Nomor Dokumen Titipan baris ke-:position maksimal 40 karakter.',
                '*.sdm_penerbit_dok.*' => 'Penembit Dokumen Titipan baris ke-:position maksimal 60 karakter.',
                '*.sdm_an_dok.*' => 'A.n Dokumen Titipan baris ke-:position maksimal 80 karakter.',
                '*.sdm_kadaluarsa_dok.*' => 'Kadaluarsa Dokumen Titipan baris ke-:position wajib berupa tanggal.',
                '*.sdm_uk_seragam.*' => 'Ukuran Seragam baris ke-:position maksimal 10 karakter dan wajib terdaftar Pengaturan Umum.',
                '*.sdm_uk_sepatu.*' => 'Ukuran Sepatu baris ke-:position wajib berupa angka lebih dari 0.',
                '*.sdm_ket_kary.*' => 'Keterangan SDM baris ke-:position wajib berupa karakter.',
                '*.password.*' => 'Kata Sandi tidak sesuaiformat.',
                '*.sdm_tgl_berhenti.*' => 'ID Pengunggah baris ke-:position wajib berupa tanggal jika Jenis Berhenti terisi.',
                '*.sdm_jenis_berhenti.*' => 'ID Pengunggah baris ke-:position wajib berupa tanggal jika Jenis Berhenti terisi.',
                '*.sdm_ket_berhenti.*' => 'Keterangan Berhenti baris ke-:position wajib berupa karakter.',
                '*.sdm_id_pembuat.*' => 'ID Pembuat baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                '*.sdm_id_pengunggah.*' => 'ID Pengunggah baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                '*.sdm_id_pengubah.*' => 'ID Pengubah baris ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                '*.sdm_diunggah.*' => 'Waktu Unggah baris ke-:position wajib berupa tanggal.',
            ]
        );
    }
}
