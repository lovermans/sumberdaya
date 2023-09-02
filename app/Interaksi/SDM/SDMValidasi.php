<?php

namespace App\Interaksi\SDM;

use App\Interaksi\Validasi;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SDMValidasi
{
    public static function validasiTambahDataSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                '*.sdm_no_absen' => ['required', 'string', 'max:10', 'unique:sdms,sdm_no_absen'],
                '*.password' => ['required', 'string'],
                '*.sdm_id_pembuat' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                ...Arr::except(static::dasarValidasiSDM(), ['*.sdm_tgl_berhenti', '*.sdm_jenis_berhenti', '*.sdm_ket_berhenti'])
            ],
            static::pesanKesalahanValidasiSDM()
        );
    }

    public static function validasiUbahDataSDM($uuid, $permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                '*.sdm_no_absen' => ['required', 'string', 'max:10', Rule::unique('sdms')->where(fn ($query) => $query->whereNot('sdm_uuid', $uuid))],
                '*.sdm_hak_akses' => ['sometimes', 'nullable', 'string'],
                '*.sdm_ijin_akses' => ['sometimes', 'nullable', 'string'],
                '*.sdm_id_pengubah' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                ...static::dasarValidasiSDM()
            ],
            static::pesanKesalahanValidasiSDM()
        );
    }

    public static function validasiUbahSandi($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                'password_lama' => ['required', 'string', 'current_password'],
                'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            ],
            [
                'password_lama' => 'Kata Sandi Lama diperlukan.',
                'password' => 'Kata Sandi Baru tidak sesuai format',
            ]
        );
    }

    public static function validasiImporDataSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                '*.sdm_no_absen' => ['required', 'string', 'max:10'],
                '*.sdm_diunggah' => ['required', 'date'],
                '*.sdm_id_pengunggah' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                ...Arr::except(static::dasarValidasiSDM(), ['*.foto_profil', '*.sdm_berkas'])
            ],
            static::pesanKesalahanValidasiSDM()
        );
    }

    public static function validasiBerkasImporDataSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                'unggah_profil_sdm' => ['required', 'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            ],
            [
                'unggah_profil_sdm.*' => 'Berkas yang diunggah wajib berupa file excel (.xlsx).'
            ]
        );
    }

    public static function dasarValidasiSDM()
    {
        return [
            '*.foto_profil' => ['sometimes', 'image'],
            '*.sdm_berkas' => ['sometimes', 'file', 'mimetypes:application/pdf'],
            '*.sdm_no_permintaan' => ['sometimes', 'nullable', 'string', 'max:20', 'exists:tambahsdms,tambahsdm_no'],
            '*.sdm_id_atasan' => ['sometimes', 'nullable', 'string', 'max:10', 'different:sdm_no_absen', 'exists:sdms,sdm_no_absen'],
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
            '*.sdm_gol_darah' => ['sometimes', 'nullable', 'string', 'max:2', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                return $query->where('atur_jenis', 'GOLONGAN DARAH');
            })],
            '*.sdm_alamat' => ['required', 'string', 'max:120'],
            '*.sdm_alamat_rt' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            '*.sdm_alamat_rw' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            '*.sdm_alamat_kelurahan' => ['required', 'string', 'max:40'],
            '*.sdm_alamat_kecamatan' => ['required', 'string', 'max:40'],
            '*.sdm_alamat_kota' => ['required', 'string', 'max:40'],
            '*.sdm_alamat_provinsi' => ['required', 'string', 'max:40'],
            '*.sdm_alamat_kodepos' => ['sometimes', 'nullable', 'string', 'max:10'],
            '*.sdm_agama' => ['required', 'string', 'max:20', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                return $query->where('atur_jenis', 'AGAMA');
            })],
            '*.sdm_no_kk' => ['sometimes', 'nullable', 'string', 'max:20'],
            '*.sdm_status_kawin' => ['required', 'string', 'max:10', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                return $query->where('atur_jenis', 'STATUS MENIKAH');
            })],
            '*.sdm_jml_anak' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            '*.sdm_pendidikan' => ['required', 'string', 'max:10', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                return $query->where('atur_jenis', 'PENDIDIKAN');
            })],
            '*.sdm_jurusan' => ['sometimes', 'nullable', 'string', 'max:60'],
            '*.sdm_telepon' => ['required', 'string', 'max:40'],
            '*.email' => ['required', 'email'],
            '*.sdm_disabilitas' => ['required', 'string', 'max:30', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                return $query->where('atur_jenis', 'DISABILITAS');
            })],
            '*.sdm_no_bpjs' => ['sometimes', 'nullable', 'string', 'max:30'],
            '*.sdm_no_jamsostek' => ['sometimes', 'nullable', 'string', 'max:30'],
            '*.sdm_no_npwp' => ['sometimes', 'nullable', 'string', 'max:30'],
            '*.sdm_nama_bank' => ['sometimes', 'nullable', 'string', 'max:20', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                return $query->where('atur_jenis', 'BANK');
            })],
            '*.sdm_cabang_bank' => ['sometimes', 'nullable', 'string', 'max:50'],
            '*.sdm_rek_bank' => ['sometimes', 'nullable', 'string', 'max:40'],
            '*.sdm_nama_dok' => ['sometimes', 'nullable', 'string', 'max:50'],
            '*.sdm_nomor_dok' => ['sometimes', 'nullable', 'string', 'max:40'],
            '*.sdm_penerbit_dok' => ['sometimes', 'nullable', 'string', 'max:60'],
            '*.sdm_an_dok' => ['sometimes', 'nullable', 'string', 'max:80'],
            '*.sdm_kadaluarsa_dok' => ['sometimes', 'nullable', 'date'],
            '*.sdm_uk_seragam' => ['sometimes', 'nullable', 'string', 'max:10', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                return $query->where('atur_jenis', 'UKURAN SERAGAM');
            })],
            '*.sdm_uk_sepatu' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            '*.sdm_ket_kary' => ['sometimes', 'nullable', 'string'],
            '*.sdm_tgl_berhenti' => ['sometimes', 'nullable', 'date', 'required_unless:sdm_jenis_berhenti,null'],
            '*.sdm_jenis_berhenti' => ['sometimes', 'nullable', 'string', 'required_unless:sdm_tgl_berhenti,null'],
            '*.sdm_ket_berhenti' => ['sometimes', 'nullable', 'string'],
        ];
    }

    public static function pesanKesalahanValidasiSDM()
    {
        return [
            '*.foto_profil.*' => 'Foto Profil wajib brupa berkas gambar atau hasil tangkap kamera.',
            '*.sdm_berkas.*' => 'Berkas yang diunggah wajib berupa berkas PDF.',
            '*.sdm_no_permintaan.*' => 'Nomor Permintaan urutan ke-:position maksimal 20 karakter dan wajib terdaftar Permintaan SDM.',
            '*.sdm_no_absen.*' => 'Nomor Absen urutan ke-:position maksimal 10 karakter.',
            '*.sdm_id_atasan.*' => 'ID Atasan urutan ke-:position maksimal 10 karakter, berbeda dengan No Absen SDM dan terdaftar di data SDM.',
            '*.sdm_tgl_gabung.*' => 'Tanggal Bergabung urutan ke-:position wajib berupa tanggal.',
            '*.sdm_warganegara.*' => 'Warga Negara urutan ke-:position maksimal 20 karakter dan wajib terdaftar Pengaturan Umum.',
            '*.sdm_no_ktp.*' => 'No KTP Passport urutan ke-:position maksimal 20 karakter.',
            '*.sdm_nama.*' => 'Nama SDM urutan ke-:position maksimal 80 karakter.',
            '*.sdm_tempat_lahir.*' => 'Tempat Lahir urutan ke-:position maksimal 40 karakter.',
            '*.sdm_tgl_lahir.*' => 'Tanggal Lahir urutan ke-:position wajib berupa tanggal.',
            '*.sdm_kelamin.*' => 'Kelamin urutan ke-:position maksimal 2 karakter dan wajib terdaftar Pengaturan Umum.',
            '*.sdm_gol_darah.*' => 'Golongan Darah urutan ke-:position maksimal 2 karakter dan wajib terdaftar Pengaturan Umum.',
            '*.sdm_alamat.*' => 'Alamat urutan ke-:position maksimal 120 karakter.',
            '*.sdm_alamat_rt.*' => 'Alamat RT urutan ke-:position wajib berupa angka lebih dari 0.',
            '*.sdm_alamat_rw.*' => 'Alamat RW urutan ke-:position wajib berupa angka lebih dari 0.',
            '*.sdm_alamat_kelurahan.*' => 'Kelurahan urutan ke-:position maksimal 40 karakter.',
            '*.sdm_alamat_kecamatan.*' => 'Kecamatan urutan ke-:position maksimal 40 karakter dan wajib terdaftar Pengaturan Umum.',
            '*.sdm_alamat_kota.*' => 'Kota urutan ke-:position maksimal 40 karakter.',
            '*.sdm_alamat_provinsi.*' => 'Provinsi urutan ke-:position maksimal 40 karakter.',
            '*.sdm_alamat_kodepos.*' => 'Kode Pos urutan ke-:position maksimal 10 karakter.',
            '*.sdm_agama.*' => 'Agama urutan ke-:position maksimal 20 karakter dan wajib terdaftar Pengaturan Umum.',
            '*.sdm_no_kk.*' => 'Nomor KK urutan ke-:position maksimal 20 karakter dan wajib terdaftar Pengaturan Umum.',
            '*.sdm_status_kawin.*' => 'Status Menikah urutan ke-:position maksimal 10 karakter dan wajib terdaftar Pengaturan Umum.',
            '*.sdm_jml_anak.*' => 'Jumlah Anak urutan ke-:position wajib berupa angka lebih dari 0.',
            '*.sdm_pendidikan.*' => 'Pendidikan urutan ke-:position maksimal 10 karakter dan wajib terdaftar Pengaturan Umum.',
            '*.sdm_jurusan.*' => 'Jurusan urutan ke-:position maksimal 60 karakter.',
            '*.sdm_telepon.*' => 'Telepon urutan ke-:position maksimal 40 karakter.',
            '*.email.*' => 'Email urutan ke-:position wajib berupa email.',
            '*.sdm_disabilitas.*' => 'Disabilitas urutan ke-:position maksimal 30 karakter dan wajib terdaftar Pengaturan Umum.',
            '*.sdm_no_bpjs.*' => 'No BPJS urutan ke-:position maksimal 30 karakter.',
            '*.sdm_no_jamsostek.*' => 'No Jamsostek urutan ke-:position maksimal 30 karakter.',
            '*.sdm_no_npwp.*' => 'NPWP urutan ke-:position maksimal 30 karakter.',
            '*.sdm_nama_bank.*' => 'Nama Bank urutan ke-:position maksimal 20 karakter dan wajib terdaftar Pengaturan Umum.',
            '*.sdm_cabang_bank.*' => 'Cabang Bank urutan ke-:position maksimal 50 karakter.',
            '*.sdm_rek_bank.*' => 'Nomor Rekening Bank urutan ke-:position maksimal 40 karakter.',
            '*.sdm_an_rek.*' => 'A.n Rekening urutan ke-:position maksimal 40 karakter dan wajib terdaftar Pengaturan Umum.',
            '*.sdm_nama_dok.*' => 'Judul Dokumen Titipan urutan ke-:position maksimal 50 karakter.',
            '*.sdm_nomor_dok.*' => 'Nomor Dokumen Titipan urutan ke-:position maksimal 40 karakter.',
            '*.sdm_penerbit_dok.*' => 'Penembit Dokumen Titipan urutan ke-:position maksimal 60 karakter.',
            '*.sdm_an_dok.*' => 'A.n Dokumen Titipan urutan ke-:position maksimal 80 karakter.',
            '*.sdm_kadaluarsa_dok.*' => 'Kadaluarsa Dokumen Titipan urutan ke-:position wajib berupa tanggal.',
            '*.sdm_uk_seragam.*' => 'Ukuran Seragam urutan ke-:position maksimal 10 karakter dan wajib terdaftar Pengaturan Umum.',
            '*.sdm_uk_sepatu.*' => 'Ukuran Sepatu urutan ke-:position wajib berupa angka lebih dari 0.',
            '*.sdm_ket_kary.*' => 'Keterangan SDM urutan ke-:position wajib berupa karakter.',
            '*.password.*' => 'Kata Sandi tidak sesuai format.',
            '*.sdm_tgl_berhenti.*' => 'ID Pengunggah urutan ke-:position wajib berupa tanggal jika Jenis Berhenti terisi.',
            '*.sdm_jenis_berhenti.*' => 'ID Pengunggah urutan ke-:position wajib berupa tanggal jika Jenis Berhenti terisi.',
            '*.sdm_ket_berhenti.*' => 'Keterangan Berhenti urutan ke-:position wajib berupa karakter.',
            '*.sdm_id_pembuat.*' => 'ID Pembuat urutan ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
            '*.sdm_id_pengunggah.*' => 'ID Pengunggah urutan ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
            '*.sdm_id_pengubah.*' => 'ID Pengubah urutan ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
            '*.sdm_diunggah.*' => 'Waktu Unggah urutan ke-:position wajib berupa tanggal.',
            '*.sdm_hak_akses.*' => 'Hak Akses wajib berupa karakter',
            '*.sdm_ijin_akses.*' => 'Ijin Akses wajib berupa karakter',
        ];
    }

    public static function dasarValidasiPencarianSDM()
    {
        return [
            '*.kata_kunci' => ['sometimes', 'nullable', 'string'],
            '*.bph' => ['sometimes', 'nullable', 'numeric', Rule::in([25, 50, 75, 100])],
            '*.urut.*' => ['sometimes', 'nullable', 'string'],
            '*.unduh' => ['sometimes', 'nullable', 'string', Rule::in(['excel'])],
        ];
    }

    public static function pesanKesalahanValidasiPencarianSDM()
    {
        return [
            '*.kata_kunci.*' => 'Kata Kunci Pencarian wajib berupa karakter.',
            '*.bph.*' => 'Baris Per halaman wajib sesuai daftar.',
            '*.urut.*' => 'Butir Pengaturan urutan ke-:position wajib berupa karakter.',
            '*.unduh.*' => 'Parameter Pencarian Unduhan urutan ke-:position tidak sesuai daftar.'
        ];
    }

    public static function dasarValidasiPosisiSDM()
    {
        return [
            '*.posisi_atasan' => ['sometimes', 'nullable', 'string', 'max:40', 'different:posisi_nama'],
            '*.posisi_wlkp' => ['sometimes', 'nullable', 'string', 'max:40'],
            '*.posisi_keterangan' => ['sometimes', 'nullable', 'string', 'max:40'],
            '*.posisi_status' => ['required', 'string', Rule::in(['AKTIF', 'NON-AKTIF'])],
        ];
    }

    public static function pesanKesalahanValidasiPosisiSDM()
    {
        return [
            '*.posisi_status.*' => 'Status Jabatan urutan ke-:position wajib sesuai daftar.',
            '*.posisi_nama.*' => 'Nama Jabatan urutan ke-:position kosong atau sudah terpakai sebelumnya.',
            '*.posisi_atasan.*' => 'Jabatan Atasan urutan ke-:position wajib berupa karakter dan berbeda dengan kolom Nama Jabatan.',
            '*.posisi_id_pembuat.*' => 'ID Pembuat urutan ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
            '*.posisi_id_pengubah.*' => 'ID Pengubah urutan ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
            '*.posisi_id_pengunggah.*' => 'ID Pengunggah urutan ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
            '*.posisi_wlkp.*' => 'Kode WLKP Jabatan urutan ke-:position wajib berupa karakter panjang maksimal 40 karakter.',
            '*.posisi_keterangan.*' => 'Keterangan Jabatan urutan ke-:position wajib berupa karakter panjang maksimal 40 karakter.',
            '*.posisi_status.*' => 'Status Jabatan urutan ke-:position wajib berupa karakter terdaftar.',
            '*.posisi_diunggah.*' => 'Waktu Unggah baris ke-:position wajib berupa tanggal.'
        ];
    }

    public static function validasiPencarianPosisiSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                ...static::dasarValidasiPencarianSDM(),
                '*.posisi_status.*' => ['sometimes', 'nullable', 'string', Rule::in(['AKTIF', 'NON-AKTIF'])],
                '*.penempatan_lokasi.*' => ['sometimes', 'nullable', 'string'],
                '*.penempatan_kontrak.*' => ['sometimes', 'nullable', 'string']
            ],
            [
                ...static::pesanKesalahanValidasiPencarianSDM(),
                ...static::pesanKesalahanValidasiPosisiSDM(),
                '*.penempatan_lokasi.*' => 'Butir Pengaturan urutan ke-:position wajib berupa karakter.',
                '*.penempatan_kontrak.*' => 'Butir Pengaturan urutan ke-:position wajib berupa karakter.',
            ]
        );
    }

    public static function validasiTambahDataPosisiSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                '*.posisi_nama' => ['required', 'string', 'unique:posisis,posisi_nama'],
                '*.posisi_id_pembuat' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                ...static::dasarValidasiPosisiSDM()
            ],
            static::pesanKesalahanValidasiPosisiSDM()
        );
    }

    public static function validasiUbahDataPosisiSDM($permintaan, $uuid)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                '*.posisi_nama' => ['required', 'string', 'max:40', Rule::unique('posisis')->where(fn ($query) => $query->whereNot('posisi_uuid', $uuid))],
                '*.posisi_id_pengubah' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                ...static::dasarValidasiPosisiSDM()
            ],
            static::pesanKesalahanValidasiPosisiSDM()
        );
    }

    public static function validasiBerkasImporDataPosisiSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                'posisi_unggah' => ['required', 'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            ],
            [
                'posisi_unggah.*' => 'Berkas yang diunggah wajib berupa file excel (.xlsx).'
            ]
        );
    }

    public static function validasiBerkasImporDataSanksiSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                'unggah_sanksi_sdm' => ['required', 'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            ],
            [
                'unggah_sanksi_sdm.*' => 'Berkas yang diunggah wajib berupa file excel (.xlsx).'
            ]
        );
    }

    public static function validasiBerkasImporDataNilaiSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                'unggah_nilai_sdm' => ['required', 'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            ],
            [
                'unggah_nilai_sdm.*' => 'Berkas yang diunggah wajib berupa file excel (.xlsx).'
            ]
        );
    }

    public static function validasiImporDataPosisiSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                '*.posisi_nama' => ['required', 'string', 'max:40'],
                '*.posisi_id_pengunggah' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                '*.posisi_diunggah' => ['sometimes', 'nullable', 'date'],
                ...static::dasarValidasiPosisiSDM()
            ],
            static::pesanKesalahanValidasiPosisiSDM()
        );
    }

    public static function dasarValidasiPermintaanTambahSDM()
    {
        return [
            '*.tambahsdm_penempatan' => ['required', 'string', 'max:20'],
            '*.tambahsdm_posisi' => ['required', 'string', 'max:40'],
            '*.tambahsdm_jumlah' => ['required', 'numeric', 'min:1'],
            '*.tambahsdm_tgl_diusulkan' => ['required', 'date'],
            '*.tambahsdm_tgl_dibutuhkan' => ['required', 'date', 'after:tambahsdm_tgl_diusulkan'],
            '*.tambahsdm_sdm_id' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
            '*.tambahsdm_alasan' => ['required', 'string'],
            '*.tambahsdm_keterangan' => ['sometimes', 'nullable', 'string'],
            '*.tambahsdm_status' => ['sometimes', 'nullable', 'string', Rule::in(['DIUSULKAN', 'DISETUJUI', 'DITOLAK', 'DITUNDA', 'DIBATALKAN'])],
            '*.tambahsdm_berkas' => ['sometimes', 'file', 'mimetypes:application/pdf'],
        ];
    }

    public static function pesanKesalahanValidasiPermintaanTambahSDM()
    {
        return [
            '*.tambahsdm_no.*' => 'Nomor Permintaan urutan ke-:position sudah terpakai.',
            '*.tgl_diusulkan_mulai.*' => 'Tanggal Mulai Diusulkan wajib berupa tanggal valid.',
            '*.tgl_diusulkan_sampai.*' => 'Tanggal Akhir Diusulkan wajib berupa tanggal valid dan lebih lama dari Tanggal Mulai.',
            '*.tambahsdm_status.*' => 'Status Permohonan urutan ke-:position wajib berupa karakter dan terdaftar.',
            '*.tambahsdm_penempatan.*' => 'Lokasi urutan ke-:position wajib berupa karakter panjang maksimal 20 karakter.',
            '*.tambahsdm_posisi.*' => 'Posisi/Jabatan urutan ke-:position wajib berupa karakter panjang maksimal 40 karakter.',
            '*.tambahsdm_jumlah.*' => 'Jumlah Kebutuhan urutan ke-:position wajib berupa angka lebih dari nol.',
            '*.tambahsdm_tgl_diusulkan.*' => 'Tanggal Diusulkan urutan ke-:position wajib berupa tanggal.',
            '*.tambahsdm_tgl_dibutuhkan.*' => 'Tanggal Dibutuhkan urutan ke-:position wajib berupa tanggal dan lebih lama dari Tanggal Diusulkan.',
            '*.tambahsdm_alasan.*' => 'Alasan Penambahan urutan ke-:position wajib berupa karakter.',
            '*.tambahsdm_keterangan.*' => 'Keterangan Penambahan urutan ke-:position wajib berupa karakter.',
            '*.tambahsdm_sdm_id.*' => 'ID Pemohon urutan ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
            '*.tambahsdm_id_pembuat.*' => 'ID Pembuat urutan ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
            '*.tambahsdm_id_pengubah.*' => 'ID Pengubah urutan ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
            '*.tambahsdm_id_pengunggah.*' => 'ID Pengunggah urutan ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
            '*.tambahsdm_berkas.*' => 'Berkas Permintaan wajib berupa berkas format PDF.',
        ];
    }

    public static function validasiPencarianPermintaanTambahSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                ...static::dasarValidasiPencarianSDM(),
                '*.tgl_diusulkan_mulai' => ['sometimes', 'nullable', 'date'],
                '*.tgl_diusulkan_sampai' => ['sometimes', 'nullable', 'required_with:tgl_diusulkan_mulai', 'date', 'after:tgl_diusulkan_mulai'],
                '*.tambahsdm_status.*' => ['sometimes', 'nullable', 'string', Rule::in(['DIUSULKAN', 'DISETUJUI', 'DITOLAK', 'DITUNDA', 'DIBATALKAN'])],
                '*.tambahsdm_laju' => ['sometimes', 'nullable', 'string', Rule::in(['BELUM TERPENUHI', 'SUDAH TERPENUHI', 'KELEBIHAN'])],
                '*.tambahsdm_penempatan.*' => ['sometimes', 'nullable', 'string'],
                '*.posisi.*' => ['sometimes', 'nullable', 'string'],
            ],
            [
                ...static::pesanKesalahanValidasiPencarianSDM(),
                ...static::pesanKesalahanValidasiPermintaanTambahSDM(),
                '*.tambahsdm_laju.*' => 'Status Terpenuhi tidak sesuai daftar.',
                '*.posisi.*' => 'Jabatan harus berupa karakter.',

            ]
        );
    }

    public static function validasiTambahDataPermintaanTambahSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                '*.tambahsdm_no' => ['required', 'string', 'max:20', 'unique:tambahsdms,tambahsdm_no'],
                '*.tambahsdm_id_pembuat' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                ...static::dasarValidasiPermintaanTambahSDM()
            ],
            static::pesanKesalahanValidasiPermintaanTambahSDM()
        );
    }

    public static function validasiUbahDataPermintaanTambahSDM($permintaan, $aturan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            $aturan,
            static::pesanKesalahanValidasiPermintaanTambahSDM()
        );
    }

    public static function dasarValidasiLapPelanggaranSDM()
    {
        return [
            '*.langgar_no_absen' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
            '*.langgar_pelapor' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
            '*.langgar_tanggal' => ['required', 'date'],
            '*.langgar_status' => ['required', 'in:DIPROSES,DIBATALKAN'],
            '*.langgar_isi' => ['required', 'string'],
            '*.langgar_keterangan' => ['sometimes', 'nullable', 'string'],
        ];
    }

    public static function pesanKesalahanValidasiLapPelanggaranSDM()
    {
        return [
            '*.tgl_langgar_mulai.*' => 'Tanggal Mulai Laporan urutan ke-:position wajib berupa tanggal valid.',
            '*.tgl_langgar_sampai.*' => 'Tanggal Akhir Laporan urutan ke-:position wajib berupa tanggal valid dan lebih lama dari Tanggal Mulai Laporan.',
            '*.langgar_status.*' => 'Status Laporan urutan ke-:position tidak sesuai daftar.',
            '*.langgar_penempatan.*' => 'Lokasi urutan ke-:position wajib berupa karakter.',
            '*.status_sdm.*' => 'Status urutan ke-:position wajib berupa karakter.',
            '*.langgar_proses.*' => 'Proses Laporan urutan ke-:position tidak sesuai daftar.',
            '*.langgar_no_absen.*' => 'No Absen Terlapor urutan ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
            '*.langgar_lap_no.*' => 'No Laporan urutan ke-:position maksimal 20 karakter atau sudah pernah dipakai sebelumnya.',
            '*.langgar_pelapor.*' => 'No Absen Pelapor maksimal 10 karakter dan terdaftar di data SDM.',
            '*.langgar_tanggal.*' => 'Tanggal Laporan tidak valid.',
            '*.langgar_isi.*' => 'Isi Laporan wajib berupa karakter.',
            '*.langgar_keterangan.*' => 'Keterangan wajib berupa karakter.',
            '*.langgar_id_pembuat.*' => 'ID Pembuat maksimal 10 karakter dan terdaftar di data SDM.',
            '*.langgar_id_pengubah.*' => 'ID Pengubah maksimal 10 karakter dan terdaftar di data SDM.',
        ];
    }

    public static function validasiPencarianLapPelanggaranSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                ...static::dasarValidasiPencarianSDM(),
                '*.tgl_langgar_mulai' => ['sometimes', 'nullable', 'date'],
                '*.tgl_langgar_sampai' => ['sometimes', 'nullable', 'required_with:tgl_langgar_mulai', 'date', 'after:tgl_langgar_mulai'],
                '*.langgar_status.*' => ['sometimes', 'nullable', 'string', Rule::in(['DIPROSES', 'DIBATALKAN'])],
                '*.langgar_penempatan.*' => ['sometimes', 'nullable', 'string'],
                '*.status_sdm.*' => ['sometimes', 'nullable', 'string'],
                '*.langgar_proses' => ['sometimes', 'nullable', 'string', Rule::in(['SELESAI', 'BELUM SELESAI'])],
            ],
            [
                ...static::pesanKesalahanValidasiPencarianSDM(),
                ...static::pesanKesalahanValidasiLapPelanggaranSDM()
            ]
        );
    }

    public static function validasiTambahDataLapPelanggaranSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                ...static::dasarValidasiLapPelanggaranSDM(),
                '*.langgar_lap_no' => ['required', 'string', 'max:20', 'unique:pelanggaransdms,langgar_lap_no'],
                '*.langgar_id_pembuat' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
            ],
            static::pesanKesalahanValidasiLapPelanggaranSDM()
        );
    }

    public static function validasiBerkasLapPelanggaranSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                'berkas_laporan' => ['sometimes', 'file', 'mimetypes:application/pdf'],
            ],
            [
                'berkas_laporan' => 'Berkas laporan yang diunggah wajib berupa file PDF.',
            ]
        );
    }

    public static function validasiUbahDataLapPelanggaranSDM($permintaan, $aturan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            $aturan,
            static::pesanKesalahanValidasiLapPelanggaranSDM()
        );
    }

    public static function dasarValidasiSanksiSDM()
    {
        return [
            '*.sanksi_jenis' => ['required', 'string', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                return $query->where('atur_jenis', 'SANKSI SDM');
            })],
            '*.sanksi_mulai' => ['required', 'date'],
            '*.sanksi_selesai' => ['required', 'date', 'after:sanksi_mulai'],
            '*.sanksi_tambahan' => ['sometimes', 'nullable', 'string'],
            '*.sanksi_keterangan' => ['sometimes', 'nullable', 'string'],
            '*.sanksi_berkas' => ['sometimes', 'file', 'mimetypes:application/pdf'],
        ];
    }

    public static function pesanKesalahanValidasiSanksiSDM()
    {
        return [
            '*.sanksi_jenis.*' => 'Jenis Sanksi urutan ke-:position tidak terdaftar.',
            '*.sanksi_mulai.*' => 'Tanggal Mulai Sanksi urutan ke-:position wajib berupa tanggal.',
            '*.sanksi_selesai.*' => 'Tanggal Selesai Sanksi urutan ke-:position wajib berupa tanggal setelah Tanggal Mulai Sanksi.',
            '*.sanksi_tambahan.*' => 'Sanksi Tambahan urutan ke-:position wajib berupa karakter.',
            '*.sanksi_keterangan.*' => 'Keterangan Sanksi urutan ke-:position wajib berupa karakter.',
            '*.sanksi_berkas.*' => 'Berkas Sanksi urutan ke-:position wajib berupa berkas format PDF.',
            '*.sanksi_lap_no.*' => 'Nomor Laporan Pelanggaran urutan ke-:position sudah dikenai sanksi.',
            '*.sanksi_id_pengubah.*' => 'ID Pembuat urutan ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
            '*.sanksi_id_pembuat.*' => 'ID Pengubah urutan ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
            '*.sanksi_no_absen.*' => 'No Absen penerima sanksi urutan ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
            '*.tgl_sanksi_mulai.*' => 'Tanggal Mulai Sanksi wajib berupa tanggal.',
            '*.tgl_sanksi_sampai.*' => 'Tanggal Akhir Sanksi wajib berupa tanggal dan lebih lama dari Tanggal Mulai Sanksi.',
            '*.sanksi_penempatan.*' => 'Lokasi Penempatan wajib berupa karakter.',
            '*.status_sdm.*' => 'Status Penempatan wajib berupa karakter.',
            '*.status_sanksi.*' => 'Status Sanksi tidak sesuai daftar.',
            '*.sanksi_id_pengunggah.*' => 'ID Pengunggah urutan ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
            '*.sanksi_diunggah.*' => 'Tanggal Unggah Sanksi urutan ke-:position wajib berupa tanggal.',
        ];
    }

    public static function validasiUbahDataSanksiSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                '*.sanksi_id_pengubah' => ['sometimes', 'nullable', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                '*.sanksi_lap_no' => ['sometimes', 'string', 'max:20', 'unique:sanksisdms,sanksi_lap_no'],
                ...static::dasarValidasiSanksiSDM()
            ],
            static::pesanKesalahanValidasiSanksiSDM()
        );
    }

    public static function validasiTambahDataSanksiSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                '*.sanksi_lap_no' => ['required', 'string', 'max:20', 'unique:sanksisdms,sanksi_lap_no'],
                '*.sanksi_id_pembuat' => ['sometimes', 'nullable', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                '*.sanksi_no_absen' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                ...static::dasarValidasiSanksiSDM()
            ],
            static::pesanKesalahanValidasiSanksiSDM()
        );
    }

    public static function validasiPencarianSanksiSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                ...static::dasarValidasiPencarianSDM(),
                '*.tgl_sanksi_mulai' => ['sometimes', 'nullable', 'date'],
                '*.tgl_sanksi_sampai' => ['sometimes', 'nullable', 'required_with:tgl_sanksi_mulai', 'date', 'after:tgl_sanksi_mulai'],
                '*.sanksi_jenis.*' => ['required', 'string', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                    return $query->where('atur_jenis', 'SANKSI SDM');
                })],
                '*.sanksi_penempatan.*' => ['sometimes', 'nullable', 'string'],
                '*.status_sdm.*' => ['sometimes', 'nullable', 'string'],
                '*.status_sanksi' => ['sometimes', 'nullable', 'string', Rule::in(['AKTIF', 'BERAKHIR'])],
            ],
            [
                ...static::pesanKesalahanValidasiPencarianSDM(),
                ...static::pesanKesalahanValidasiSanksiSDM()
            ]
        );
    }

    public static function validasiImporDataSanksiSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                '*.sanksi_no_absen' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                '*.sanksi_id_pengunggah' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                '*.sanksi_diunggah' => ['sometimes', 'nullable', 'date'],
                ...Arr::except(static::dasarValidasiSanksiSDM(), ['*.sanksi_berkas'])
            ],
            static::pesanKesalahanValidasiSanksiSDM()
        );
    }

    public static function dasarValidasiNilaiSDM()
    {
        return [
            '*.nilaisdm_tahun' => ['required', 'date_format:Y'],
            '*.nilaisdm_periode' => ['required', 'string'],
            '*.nilaisdm_bobot_hadir' => ['sometimes', 'nullable', 'numeric'],
            '*.nilaisdm_bobot_sikap' => ['sometimes', 'nullable', 'numeric'],
            '*.nilaisdm_bobot_target' => ['sometimes', 'nullable', 'numeric'],
            '*.nilaisdm_tindak_lanjut' => ['sometimes', 'nullable', 'string'],
            '*.nilaisdm_keterangan' => ['sometimes', 'nullable', 'string'],
            '*.nilai_berkas' => ['sometimes', 'file', 'mimetypes:application/pdf'],
        ];
    }

    public static function pesanKesalahanValidasiNilaiSDM()
    {
        return [
            '*.nilaisdm_no_absen.*' => 'Nomor Absen urutan ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
            '*.nilaisdm_tahun.*' => 'Tahun Penilaian SDM urutan ke-:position wajib berupa Tahun.',
            '*.nilaisdm_periode.*' => 'Periode Penilaian Berkala SDM urutan ke-:position wajib berupa karakter.',
            '*.nilaisdm_bobot_hadir.*' => 'Bobot Nilai Kehadiran urutan ke-:position wajib berupa angka.',
            '*.nilaisdm_bobot_sikap.*' => 'Bobot Nilai Sikap Kerja urutan ke-:position wajib berupa angka.',
            '*.nilaisdm_bobot_target.*' => 'Bobot Nilai Target Kerja urutan ke-:position wajib berupa angka.',
            '*.nilaisdm_tindak_lanjut.*' => 'Tindak Lanjut Penilaian SDM urutan ke-:position wajib berupa karakter.',
            '*.nilaisdm_keterangan.*' => 'Keterangan Penilaian SDM Penilaian SDM urutan ke-:position wajib berupa karakter.',
            '*.nilai_berkas.*' => 'Berkas Penilaian urutan ke-:position wajib berupa berkas format PDF.',
            '*.nilaisdm_id_pengubah.*' => 'ID Pengubah urutan ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
            '*.nilaisdm_id_pembuat.*' => 'ID Pembuat urutan ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
            '*.nilaisdm_id_pengunggah.*' => 'ID Pengunggah urutan ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
            '*.nilaisdm_kontrak.*' => 'Jenis Kontrak urutan ke-:position wajib berupa karakter.',
            '*.nilaisdm_penempatan.*' => 'Lokasi urutan ke-:position wajib berupa karakter.',
            '*.nilaisdm_diunggah.*' => 'Tanggal Unggah Sanksi urutan ke-:position wajib berupa tanggal.'
        ];
    }

    public static function validasiUbahDataNilaiSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                '*.nilaisdm_id_pengubah' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                ...static::dasarValidasiNilaiSDM()
            ],
            static::pesanKesalahanValidasiNilaiSDM()
        );
    }

    public static function validasiTambahDataNilaiSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                '*.nilaisdm_id_pembuat' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                '*.nilaisdm_no_absen' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                ...static::dasarValidasiNilaiSDM()
            ],
            static::pesanKesalahanValidasiNilaiSDM()
        );
    }

    public static function validasiPencarianNilaiSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                ...static::dasarValidasiPencarianSDM(),
                '*.nilaisdm_tahun.*' => ['sometimes', 'nullable', 'date_format:Y'],
                '*.nilaisdm_periode.*' => ['sometimes', 'nullable', 'string'],
                '*.nilaisdm_kontrak.*' =>  ['sometimes', 'nullable', 'string'],
                '*.nilaisdm_penempatan.*' => ['sometimes', 'nullable', 'string'],
            ],
            [
                ...static::pesanKesalahanValidasiPencarianSDM(),
                ...static::pesanKesalahanValidasiNilaiSDM()
            ]
        );
    }

    public static function validasiImporDataNilaiSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                '*.nilaisdm_no_absen' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                '*.nilaisdm_id_pengunggah' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                '*.nilaisdm_diunggah' => ['required', 'nullable', 'date'],
                ...static::dasarValidasiNilaiSDM()
            ],
            static::pesanKesalahanValidasiNilaiSDM()
        );
    }

    public static function dasarValidasiPencarianPenempatanSDM()
    {
        return [
            ...Arr::except(static::dasarValidasiPencarianSDM(), ['*.bph']),
            '*.lokasi.*' => ['sometimes', 'nullable', 'string', 'max:40'],
            '*.kontrak.*' => ['sometimes', 'nullable', 'string', 'max:40'],
            '*.kategori.*' => ['sometimes', 'nullable', 'string', 'max:40'],
            '*.pangkat.*' => ['sometimes', 'nullable', 'string', 'max:40'],
            '*.kelamin.*' => ['sometimes', 'nullable', 'string', 'max:40'],
            '*.agama.*' => ['sometimes', 'nullable', 'string', 'max:40'],
            '*.kawin.*' => ['sometimes', 'nullable', 'string', 'max:40'],
            '*.pendidikan.*' => ['sometimes', 'nullable', 'string', 'max:40'],
            '*.warganegara.*' => ['sometimes', 'nullable', 'string', 'max:40'],
            '*.disabilitas.*' => ['sometimes', 'nullable', 'string', 'max:40'],
            '*.posisi.*' => ['sometimes', 'nullable', 'string'],
            '*.bph' => ['sometimes', 'nullable', 'numeric', Rule::in([100, 250, 500, 1000])],
        ];
    }

    public static function dasarValidasiPenempatanSDM($permintaan)
    {
        return [
            '*.penempatan_no_absen' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
            '*.penempatan_selesai' => ['sometimes', 'nullable', Rule::requiredIf(in_array(Arr::pluck($permintaan, '*.penempatan_kontrak'), ['PKWT', 'PERCOBAAN'])), 'date', 'after:penempatan_mulai'],
            '*.penempatan_ke' => ['sometimes', 'nullable', Rule::requiredIf(in_array(Arr::pluck($permintaan, '*.penempatan_kontrak'), ['PKWT', 'PERCOBAAN'])), 'numeric', 'min:0'],
            '*.penempatan_lokasi' => ['required', 'string', 'max:40', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                return $query->where('atur_jenis', 'PENEMPATAN');
            })],
            '*.penempatan_posisi' => ['required', 'string', 'max:40', 'exists:posisis,posisi_nama'],
            '*.penempatan_kategori' => ['required', 'string', 'max:40', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                return $query->where('atur_jenis', 'KATEGORI');
            })],
            '*.penempatan_kontrak' => ['required', 'string', 'max:40', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                return $query->where('atur_jenis', 'STATUS KONTRAK');
            })],
            '*.penempatan_pangkat' => ['required', 'string', 'max:40', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                return $query->where('atur_jenis', 'PANGKAT');
            })],
            '*.penempatan_golongan' => ['required', 'string', 'max:40', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                return $query->where('atur_jenis', 'GOLONGAN');
            })],
            '*.penempatan_grup' => ['sometimes', 'nullable', 'string', 'max:40'],
            '*.penempatan_keterangan' => ['sometimes', 'nullable', 'string'],
            '*.penempatan_berkas' => ['sometimes', 'file', 'mimetypes:application/pdf'],
        ];
    }

    public static function pesanKesalahanPencarianPenempatanSDM()
    {
        return [
            '*.lokasi.*' => 'Lokasi Penempatan wajib berupa karakter.',
            '*.kontrak.*' => 'Kontrak SDM wajib berupa karakter.',
            '*.kategori.*' => 'Kategori Penempatan wajib berupa karakter.',
            '*.pangkat.*' => 'Pangkar SDM wajib berupa karakter.',
            '*.kelamin.*' => 'Jenis Kelamin SDM wajib berupa karakter.',
            '*.agama.*' => 'Agama SDM wajib berupa karakter.',
            '*.kawin.*' => 'Status Kawin SDM wajib berupa karakter.',
            '*.pendidikan.*' => 'Pendidikan SDM wajib berupa karakter.',
            '*.warganegara.*' => 'Warga Negara SDM wajib berupa karakter.',
            '*.disabilitas.*' => 'Disabilitas SDM wajib berupa karakter.',
            '*.posisi.*' => 'Jabatan SDM wajib berupa karakter.',
            '*.penempatan_mulai.*' => 'Tanggal Mulai Penempatan urutan ke-:position wajib berupa tanggal.',
            '*.penempatan_id_pembuat.*' => 'ID Pembuat urutan ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
            '*.penempatan_no_absen.*' => 'Nomor Absen urutan ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
            '*.penempatan_selesai.*' => 'Tanggal Selesai Penempatan urutan ke-:position wajib berupa tanggal dan lebih lama dari Tanggal Mulai Penempatan jika Jenis Kontrak berupa PKWT atau PERCOBAAN.',
            '*.penempatan_ke.*' => 'Nomor Urut Penempatan urutan ke-:position wajib berupa angka jika Jenis Kontrak berupa PKWT atau PERCOBAAN.',
            '*.penempatan_lokasi.*' => 'Lokasi Penempatan urutan ke-:position maksimal 40 karakter dan terdaftar.',
            '*.penempatan_posisi.*' => 'Jabatan Penempatan urutan ke-:position maksimal 40 karakter dan terdaftar.',
            '*.penempatan_kategori.*' => 'Kategori Penempatan urutan ke-:position maksimal 40 karakter dan terdaftar.',
            '*.penempatan_kontrak.*' => 'Jenis Kontrak urutan ke-:position maksimal 40 karakter dan terdaftar.',
            '*.penempatan_pangkat.*' => 'Pangkat Penempatan urutan ke-:position maksimal 40 karakter dan terdaftar.',
            '*.penempatan_golongan.*' => 'Golongan Penempatan urutan ke-:position maksimal 40 karakter dan terdaftar.',
            '*.penempatan_grup.*' => 'Grup Penempatan urutan ke-:position wajib berupa karakter.',
            '*.penempatan_keterangan.*' => 'Keterangan Penempatan urutan ke-:position wajib berupa karakter.',
            '*.penempatan_berkas.*' => 'Berkas Penempatan urutan ke-:position wajib berupa berkas format PDF.',
            ...static::pesanKesalahanValidasiPencarianSDM()
        ];
    }

    public static function validasiPencarianPenempatanSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            static::dasarValidasiPencarianPenempatanSDM(),
            static::pesanKesalahanPencarianPenempatanSDM()
        );
    }

    public static function validasiTambahPenempatanSDM($permintaan)
    {
        return Validasi::validasiUmum(
            $permintaan,
            [
                '*.penempatan_mulai' => ['required', 'date', Rule::unique('penempatans')->where(function ($query) use ($permintaan) {
                    $query->where('penempatan_no_absen', $permintaan[0]['penempatan_no_absen']);
                })],
                '*.penempatan_id_pembuat' => ['required', 'string', 'exists:sdms,sdm_no_absen'],
                ...static::dasarValidasiPenempatanSDM($permintaan)
            ],
            static::pesanKesalahanPencarianPenempatanSDM()
        );
    }
}
