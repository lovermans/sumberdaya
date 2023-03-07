<?php

namespace App\Http\Controllers\Auth;

use App\Tambahan\FungsiStatis;
use Illuminate\Validation\Rule;

class RegisteredUserController
{
    /**
    * Display the registration view.
    *
    * @return \Illuminate\View\View
    */
    public function create(FungsiStatis $fungsiStatis)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();

        abort_unless($pengguna && str($pengguna?->sdm_hak_akses)->contains('SDM-PENGURUS'), 403);

        $aturs = $fungsiStatis->ambilCacheAtur();
        $permintaanSdms = $fungsiStatis->ambilCachePermintaanTambahSDM();
        $atasan = $fungsiStatis->ambilCacheSDM();
        
        $data = [
            'permintaanSdms' => $permintaanSdms,
            'atasans' => $atasan,
            'negaras' => $aturs->where('atur_jenis', 'NEGARA')->sortBy(['atur_butir', 'asc']),
            'kelamins' => $aturs->where('atur_jenis', 'KELAMIN')->sortBy(['atur_butir', 'asc']),
            'gdarahs' => $aturs->where('atur_jenis', 'GOLONGAN DARAH')->sortBy(['atur_butir', 'asc']),
            'agamas' => $aturs->where('atur_jenis', 'AGAMA')->sortBy(['atur_butir', 'asc']),
            'kawins' => $aturs->where('atur_jenis', 'STATUS MENIKAH')->sortBy(['atur_butir', 'asc']),
            'pendidikans' => $aturs->where('atur_jenis', 'PENDIDIKAN')->sortBy(['atur_butir', 'asc']),
            'disabilitas' => $aturs->where('atur_jenis', 'DISABILITAS')->sortBy(['atur_butir', 'asc']),
            'banks' => $aturs->where('atur_jenis', 'BANK')->sortBy(['atur_butir', 'asc']),
            'seragams' => $aturs->where('atur_jenis', 'UKURAN SERAGAM')->sortBy(['atur_butir', 'asc']),
            'phks' => $aturs->where('atur_jenis', 'JENIS BERHENTI')->sortBy(['atur_butir', 'asc']),
            'perans' => $aturs->where('atur_jenis', 'PERAN')->sortBy(['atur_butir', 'asc']),
            'penempatans' => $aturs->where('atur_jenis', 'PENEMPATAN')->sortBy(['atur_butir', 'asc']),
        ];
        
        $HtmlPenuh = $app->view->make('tambah-ubah-akun', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }
    
    /**
    * Handle an incoming registration request.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\RedirectResponse
    *
    * @throws \Illuminate\Validation\ValidationException
    */
    public function store(FungsiStatis $fungsiStatis, Rule $rule)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();

        abort_unless($pengguna && str($pengguna?->sdm_hak_akses)->contains('SDM-PENGURUS'), 403);
        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi');

        $hash = $app->hash;
        $database = $app->db;
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        $halaman = $app->view;
        
        $reqs->merge(['sdm_id_pembuat' => $pengguna->sdm_no_absen, 'password' => $hash->make($reqs->sdm_no_ktp) ?? null]);
        
        $validasi = $app->validator->make(
            $reqs->all(),
            [
                'foto_profil' => ['sometimes', 'image'],
                'sdm_berkas' => ['sometimes', 'file', 'mimetypes:application/pdf'],
                'sdm_no_permintaan' => ['nullable', 'string', 'max:20', 'exists:tambahsdms,tambahsdm_no'],
                'sdm_no_absen' => ['required', 'string', 'max:10', 'unique:sdms,sdm_no_absen'],
                'sdm_id_atasan' => ['nullable', 'string', 'max:10', 'different:sdm_no_absen', 'exists:sdms,sdm_no_absen'],
                'sdm_tgl_gabung' => ['required', 'date'],
                'sdm_warganegara' => ['required', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                    return $query->where('atur_jenis', 'NEGARA');
                })],
                'sdm_no_ktp' => ['required', 'string', 'max:20'],
                'sdm_nama' => ['required', 'string', 'max:80'],
                'sdm_tempat_lahir' => ['required', 'string', 'max:40'],
                'sdm_tgl_lahir' => ['required', 'date'],
                'sdm_kelamin' => ['required', 'string', 'max:2', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                    return $query->where('atur_jenis', 'KELAMIN');
                })],
                'sdm_gol_darah' => ['nullable', 'string', 'max:2', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                    return $query->where('atur_jenis', 'GOLONGAN DARAH');
                })],
                'sdm_alamat' => ['required', 'string', 'max:120'],
                'sdm_alamat_rt' => ['nullable', 'numeric', 'min:0'],
                'sdm_alamat_rw' => ['nullable', 'numeric', 'min:0'],
                'sdm_alamat_kelurahan' => ['required', 'string', 'max:40'],
                'sdm_alamat_kecamatan' => ['required', 'string', 'max:40'],
                'sdm_alamat_kota' => ['required', 'string', 'max:40'],
                'sdm_alamat_provinsi' => ['required', 'string', 'max:40'],
                'sdm_alamat_kodepos' => ['nullable', 'string', 'max:10'],
                'sdm_agama' => ['required', 'string', 'max:20', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                    return $query->where('atur_jenis', 'AGAMA');
                })],
                'sdm_no_kk' => ['nullable', 'string', 'max:20'],
                'sdm_status_kawin' => ['required', 'string', 'max:10', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                    return $query->where('atur_jenis', 'STATUS MENIKAH');
                })],
                'sdm_jml_anak' => ['nullable', 'numeric', 'min:0'],
                'sdm_pendidikan' => ['required', 'string', 'max:10', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                    return $query->where('atur_jenis', 'PENDIDIKAN');
                })],
                'sdm_jurusan' => ['nullable', 'string', 'max:60'],
                'sdm_telepon' => ['required', 'string', 'max:40'],
                'email' => ['required', 'email'],
                'sdm_disabilitas' => ['required', 'string', 'max:30', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                    return $query->where('atur_jenis', 'DISABILITAS');
                })],
                'sdm_no_bpjs' => ['nullable', 'string', 'max:30'],
                'sdm_no_jamsostek' => ['nullable', 'string', 'max:30'],
                'sdm_no_npwp' => ['nullable', 'string', 'max:30'],
                'sdm_nama_bank' => ['nullable', 'string', 'max:20', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                    return $query->where('atur_jenis', 'BANK');
                })],
                'sdm_cabang_bank' => ['nullable', 'string', 'max:50'],
                'sdm_rek_bank' => ['nullable', 'string', 'max:40'],
                'sdm_nama_dok' => ['nullable', 'string', 'max:50'],
                'sdm_nomor_dok' => ['nullable', 'string', 'max:40'],
                'sdm_penerbit_dok' => ['nullable', 'string', 'max:60'],
                'sdm_an_dok' => ['nullable', 'string', 'max:80'],
                'sdm_kadaluarsa_dok' => ['nullable', 'date'],
                'sdm_uk_seragam' => ['nullable', 'string', 'max:10', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                    return $query->where('atur_jenis', 'UKURAN SERAGAM');
                })],
                'sdm_uk_sepatu' => ['nullable', 'numeric', 'min:0'],
                'sdm_ket_kary' => ['nullable', 'string'],
                'password' => ['nullable', 'string'],
                'sdm_id_pembuat' => ['nullable', 'string', 'max:10'],
            ],
            [],
            [
                'foto_profil' => 'Foto Profil',
                'sdm_berkas' => 'Berkas Yang Diunggah',
                'sdm_no_permintaan' => 'Nomor Permintaan Tambah SDM',
                'sdm_no_absen' => 'Nomor Absen SDM',
                'sdm_id_atasan' => 'Nomor Absen Atasan',
                'sdm_tgl_gabung' => 'Tanggal Bergabung SDM',
                'sdm_warganegara' => 'Warganegara',
                'sdm_no_ktp' => 'Nomor E-KTP/Passport',
                'sdm_nama' => 'Nama SDM',
                'sdm_tempat_lahir' => 'Tempat Lahir',
                'sdm_tgl_lahir' => 'Tanggal Lahir',
                'sdm_kelamin' => 'Kelamin',
                'sdm_gol_darah' => 'Golongan Darah',
                'sdm_alamat' => 'Alamat',
                'sdm_alamat_rt' => 'Alamat RT',
                'sdm_alamat_rw' => 'Alamat RW',
                'sdm_alamat_kelurahan' => 'Alamat Kelurahan',
                'sdm_alamat_kecamatan' => 'Alamat Kecamatan',
                'sdm_alamat_kota' => 'Alamat Kota/Kabupaten',
                'sdm_alamat_provinsi' => 'Alamat Provinsi',
                'sdm_alamat_kodepos' => 'Alamat Kode Pos',
                'sdm_agama' => 'Agama',
                'sdm_no_kk' => 'Nomor KK',
                'sdm_status_kawin' => 'Status Menikah',
                'sdm_jml_anak' => 'Jumlah Anak',
                'sdm_pendidikan' => 'Pendidikan',
                'sdm_jurusan' => 'Jurusan',
                'sdm_telepon' => 'Telepon',
                'email' => 'Email',
                'sdm_disabilitas' => 'Disabilitas',
                'sdm_no_bpjs' => 'Nomor BPJS',
                'sdm_no_jamsostek' => 'Nomor Jamsostek',
                'sdm_no_npwp' => 'NPWM',
                'sdm_nama_bank' => 'Nama Bank',
                'sdm_cabang_bank' => 'Cabang Bank',
                'sdm_rek_bank' => 'Nomor Rekening Bank',
                'sdm_nama_dok' => 'Nama/Judul Dokumen Titipan',
                'sdm_nomor_dok' => 'Nomor Dokumen Titipan',
                'sdm_penerbit_dok' => 'Penerbit Dokumen Titipan',
                'sdm_an_dok' => 'A.n Dokumen Titipan',
                'sdm_kadaluarsa_dok' => 'Tanggal Kadaluarsa Dokumen Titipan',
                'sdm_uk_seragam' => 'Ukuran Seragam',
                'sdm_uk_sepatu' => 'Ukuran Sepatu',
                'sdm_ket_kary' => 'Keterangan Karyawan',
                'password' => 'Kata Sandi',
                'sdm_id_pembuat' => 'No Absen Pengurus',
            ]
        );

        $validasi->validate();
        
        $valid = $validasi->safe();
        
        $data = $valid->except(['foto_profil', 'sdm_berkas']);
        
        $database->table('sdms')->insert($data);
        
        $foto = $valid->only('foto_profil')['foto_profil'] ?? false;
        $berkas = $valid->only('sdm_berkas')['sdm_berkas'] ?? false;
        $no_absen = $valid->only('sdm_no_absen')['sdm_no_absen'];
        
        if ($foto) {
            $foto->storeAs('sdm/foto-profil', $no_absen . '.webp');
        }
        
        if ($berkas) {
            $berkas->storeAs('sdm/berkas', $no_absen . '.pdf');
        }
        
        $fungsiStatis->hapusCacheSDMUmum();
        
        $pesan = $fungsiStatis->statusBerhasil();
        
        return $app->redirect->route('sdm.mulai')->with('pesan', $pesan);
    }
}
