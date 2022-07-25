@extends('rangka')

@section('isi')
    <h2>Informasi Dasar</h2>
    <div class="kartu t-data">
        <dl style="grid-row:span 2;text-align:center">
            <dd><img style="width:8em" @class([
                'svg' => !Storage::exists(
                    'sdm/foto-profil/' . auth()->user()->sdm_no_absen . '.webp'
                ),
            ])
                    src="{{ Storage::exists('sdm/foto-profil/' . auth()->user()->sdm_no_absen . '.webp') ? route('sdm.tautan-foto-profil', ['berkas_foto_profil' => auth()->user()->sdm_no_absen . '.webp' . '?' . filemtime(storage_path('app/sdm/foto-profil/' . auth()->user()->sdm_no_absen . '.webp'))]) : asset(mix('/ikon.svg')) . '#akun' }}"
                    alt="{{ auth()->user()->nama ?? 'foto akun' }}" alt="{{ auth()->user()->nama ?? 'foto akun' }}"
                    loading="lazy"></dd>
        </dl>
        <dl>
            <dt>No Absen</dt>
            <dd>{{ auth()->user()->sdm_no_absen }}</dd>
        </dl>
        <dl>
            <dt>Tanggal Bergabung</dt>
            <dd>{{ str()->upper(Date::parse(auth()->user()->sdm_tgl_gabung)->translatedFormat('d F Y')) }}</dd>
        </dl>
        <dl>
            <dt>Warga Negara</dt>
            <dd>{{ auth()->user()->sdm_warganegara }}</dd>
        </dl>
        <dl>
            <dt>No KTP/Passport</dt>
            <dd>{{ auth()->user()->sdm_no_ktp }}</dd>
        </dl>
        <dl class="gspan-2">
            <dt>Nama</dt>
            <dd>{{ auth()->user()->sdm_nama }}</dd>
        </dl>
        <dl>
            <dt>Tempat Lahir</dt>
            <dd>{{ auth()->user()->sdm_tempat_lahir }}</dd>
        </dl>
        <dl>
            <dt>Tanggal Lahir</dt>
            <dd>{{ str()->upper(Date::parse(auth()->user()->sdm_tgl_lahir)->translatedFormat('d F Y')) }}</dd>
        </dl>
        <dl>
            <dt>Kelamin</dt>
            <dd>{{ auth()->user()->sdm_kelamin }}</dd>
            <dt>Gol Darah</dt>
            <dd>{{ auth()->user()->sdm_gol_darah }}</dd>
        </dl>
        <dl class="gspan-2">
            <dt>Alamat</dt>
            <dd>{{ auth()->user()->sdm_alamat }}</dd>
        </dl>
        <dl>
            <dt>RT</dt>
            <dd>{{ auth()->user()->sdm_alamat_rt }}</dd>
            <dt>RW</dt>
            <dd>{{ auth()->user()->sdm_alamat_rw }}</dd>
        </dl>
        <dl>
            <dt>Kelurahan</dt>
            <dd>{{ auth()->user()->sdm_alamat_kelurahan }}</dd>
        </dl>
        <dl>
            <dt>Kecamatan</dt>
            <dd>{{ auth()->user()->sdm_alamat_kecamatan }}</dd>
        </dl>
        <dl>
            <dt>Kota/Kabupaten</dt>
            <dd>{{ auth()->user()->sdm_alamat_kota }}</dd>
        </dl>
        <dl>
            <dt>Provinsi</dt>
            <dd>{{ auth()->user()->sdm_alamat_provinsi }}</dd>
        </dl>
        <dl>
            <dt>Kode Pos</dt>
            <dd>{{ auth()->user()->sdm_alamat_kodepos }}</dd>
        </dl>
        <dl>
            <dt>Agama</dt>
            <dd>{{ auth()->user()->sdm_agama }}</dd>
        </dl>
        <dl>
            <dt>No KK</dt>
            <dd>{{ auth()->user()->sdm_no_kk }}</dd>
        </dl>
        <dl>
            <dt>Status Kawin</dt>
            <dd>{{ auth()->user()->sdm_status_kawin }}</dd>
            <dt>Jumlah Anak</dt>
            <dd>{{ auth()->user()->sdm_jml_anak }}</dd>
        </dl>
        <dl>
            <dt>Pendidikan</dt>
            <dd>{{ auth()->user()->sdm_pendidikan }}</dd>
            <dt>Jurusan</dt>
            <dd>{{ auth()->user()->sdm_jurusan }}</dd>
        </dl>
        <dl>
            <dt>Telepon</dt>
            <dd>{{ auth()->user()->sdm_telepon }}</dd>
            <dt>Email</dt>
            <dd>{{ auth()->user()->email }}</dd>
        </dl>
        <dl>
            <dt>Disabilitas</dt>
            <dd>{{ auth()->user()->sdm_disabilitas }}</dd>
        </dl>
        <dl>
            <dt>No BPJS</dt>
            <dd>{{ auth()->user()->sdm_no_bpjs }}</dd>
            <dt>No Jamsostek</dt>
            <dd>{{ auth()->user()->sdm_no_jamsostek }}</dd>
        </dl>
        <dl>
            <dt>NPWP</dt>
            <dd>{{ auth()->user()->sdm_no_npwp }}</dd>
        </dl>
        <dl>
            <dt>Nama Bank</dt>
            <dd>{{ auth()->user()->sdm_nama_bank }}</dd>
            <dt>Cabang Bank</dt>
            <dd>{{ auth()->user()->sdm_cabang_bank }}</dd>
        </dl>
        <dl>
            <dt>Rekening</dt>
            <dd>{{ auth()->user()->sdm_rek_bank }}</dd>
            <dt>A.n Rekening</dt>
            <dd>{{ auth()->user()->sdm_an_rek }}</dd>
        </dl>
        <dl>
            <dt>Dokumen Jaminan</dt>
            <dd>{{ auth()->user()->sdm_nama_dok }}</dd>
            <dt>Nomor Dokumen</dt>
            <dd>{{ auth()->user()->sdm_nomor_dok }}</dd>
        </dl>
        <dl>
            <dt>Penerbit Dokumen</dt>
            <dd>{{ auth()->user()->sdm_penerbit_dok }}</dd>
            <dt>A.n Dokumen</dt>
            <dd>{{ auth()->user()->sdm_an_dok }}</dd>
        </dl>
        <dl>
            <dt>Kadaluarsa Dokumen</dt>
            <dd>{{ str()->upper(Date::parse(auth()->user()->sdm_kadaluarsa_dok)->translatedFormat('d F Y')) }}</dd>
        </dl>
        <dl>
            <dt>Ukuran Seragam</dt>
            <dd>{{ auth()->user()->sdm_uk_seragam }}</dd>
            <dt>Ukuran Sepatu</dt>
            <dd>{{ auth()->user()->sdm_uk_sepatu }}</dd>
        </dl>
        <dl class="gspan-4">
            <dt>Keterangan</dt>
            <dd>{{ auth()->user()->sdm_ket_kary }}</dd>
        </dl>
    </div>
    @includeWhen(session()->has('spanduk') || session()->has('pesan') || $errors->any(), 'pemberitahuan')
@endsection
