@extends('rangka')

@section('isi')
<div id="tambah_ubah_akun">
    <form id="form_tambahUbahAkun" class="form-xhr kartu tcetak" method="POST" action="{{ $app->url->current() }}">
        <input type="hidden" name="_token" value="{{ $app->request->session()->token() }}">

        <div class="isian pendek">
            <img id="foto" @class(['svg'=> !$app->filesystem->exists('sdm/foto-profil/' .
            $app->request->old('sdm_no_absen',
            $sdm->sdm_no_absen ?? null) . '.webp')])
            src="{{
            $app->filesystem->exists('sdm/foto-profil/' . $app->request->old('sdm_no_absen', $sdm->sdm_no_absen ??
            null) . '.webp')
            ? $app->url->route('sdm.tautan-foto-profil', ['berkas_foto_profil' => $app->request->old('sdm_no_absen',
            $sdm->sdm_no_absen ?? null) . '.webp' . '?' . filemtime($app->storagePath('app/sdm/foto-profil/' .
            $app->request->old('sdm_no_absen', $sdm->sdm_no_absen ?? null) . '.webp'))])
            : $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/blank.webp'))
            }}"
            alt="{{ $app->request->old('sdm_no_absen', $sdm->sdm_nama ?? 'foto akun') }}"
            title="{{ $app->request->old('sdm_no_absen',$sdm->sdm_nama ?? 'foto akun') }}"
            loading="lazy">
        </div>

        <div class="isian normal">
            <label for="foto_profil">Foto Profil</label>

            <input id="foto_profil" type="file" name="foto_profil" accept="image/*" capture
                onchange="siapkanFoto(this)">

            <span class="t-bantu">
                Pilih gambar atau ambil dari kamera
                {{ $app->filesystem->exists('sdm/foto-profil/'.$app->request->old('sdm_no_absen', $sdm->sdm_no_absen ??
                null).'.webp') ? '(berkas yang diunggah akan menindih berkas unggahan lama).' : '' }}
            </span>
        </div>

        @if(str()->contains($app->request->user()->sdm_hak_akses, 'SDM-PENGURUS'))
        <div class="isian panjang">
            <label for="permintaan_sdm_no">No Permintaan</label>

            <select id="permintaan_sdm_no" name="sdm_no_permintaan" class="pil-cari">
                <option selected></option>

                @if (!in_array($app->request->old('sdm_no_permintaan', $sdm->sdm_no_permintaan ?? null), (array)
                $permintaanSdms->pluck('tambahsdm_no')->toArray()) && $app->request->old('sdm_no_permintaan',
                $sdm->sdm_no_permintaan ?? null))
                <option value="{{ $app->request->old('sdm_no_permintaan', $sdm->sdm_no_permintaan ?? null) }}"
                    class="merah" selected>{{ $app->request->old('sdm_no_permintaan', $sdm->sdm_no_permintaan ?? null)
                    }}
                </option>
                @endif

                @foreach ($permintaanSdms as $permintaanSdm)
                <option value="{{ $permintaanSdm->tambahsdm_no }}" @selected($app->request->old('sdm_no_permintaan',
                    $sdm->sdm_no_permintaan ?? null) == $permintaanSdm->tambahsdm_no) @class(['merah' =>
                    $permintaanSdm->tambahsdm_status !== 'DISETUJUI' || $permintaanSdm->tambahsdm_jumlah <
                        $permintaanSdm->tambahsdm_terpenuhi])>
                        {{ $permintaanSdm->tambahsdm_no }} : {{ $permintaanSdm->tambahsdm_posisi }} {{
                        $permintaanSdm->tambahsdm_penempatan }}
                        {{ ($permintaanSdm->tambahsdm_jumlah > $permintaanSdm->tambahsdm_terpenuhi) ? 'KURANG ' .
                        ($permintaanSdm->tambahsdm_jumlah - $permintaanSdm->tambahsdm_terpenuhi) :
                        (($permintaanSdm->tambahsdm_jumlah < $permintaanSdm->tambahsdm_terpenuhi) ? 'KELEBIHAN ' .
                            ($permintaanSdm->tambahsdm_terpenuhi - $permintaanSdm->tambahsdm_jumlah) : 'TELAH
                            TERPENUHI')}}
                </option>
                @endforeach
            </select>

            <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
        </div>
        @endif

        <div class="isian normal">
            <label for="sdm_no_absen">Nomor Absen</label>

            <input id="sdm_no_absen" type="text" name="sdm_no_absen"
                value="{{ $app->request->old('sdm_no_absen', $sdm->sdm_no_absen ?? null) }}" pattern="^[0-9]{8}$"
                inputmode="numeric" {{str()->contains($app->request->user()->sdm_hak_akses, 'SDM-PENGURUS') ?
            'required' :
            'readonly'}}>

            <span class="t-bantu">8 digit nomor absen</span>
        </div>

        @if(str()->contains($app->request->user()->sdm_hak_akses, 'SDM-PENGURUS'))
        <div class="isian panjang">
            <label for="sdm_no_absen_atasan">Nomor Absen Atasan</label>

            <select id="sdm_no_absen_atasan" name="sdm_id_atasan" class="pil-cari">
                <option selected></option>

                @if (!in_array($app->request->old('sdm_id_atasan', $sdm->sdm_id_atasan ?? null), (array)
                $atasans->pluck('sdm_no_absen')->toArray()) && $app->request->old('sdm_id_atasan', $sdm->sdm_id_atasan
                ??
                null))
                <option value="{{ $app->request->old('sdm_id_atasan', $sdm->sdm_id_atasan ?? null) }}" class="merah"
                    selected>{{ $app->request->old('sdm_id_atasan', $sdm->sdm_id_atasan ?? null) }}
                </option>
                @endif

                @foreach ($atasans as $atasan)
                <option value="{{ $atasan->sdm_no_absen }}" @selected($app->request->old('sdm_id_atasan',
                    $sdm->sdm_id_atasan ?? null) == $atasan->sdm_no_absen) @class(['merah' =>
                    $atasan->sdm_tgl_berhenti])>
                    {{ $atasan->sdm_no_absen }} - {{ $atasan->sdm_nama }} - {{ $atasan->penempatan_posisi }}
                </option>
                @endforeach
            </select>

            <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
        </div>
        @endif

        <div class="isian pendek">
            <label for="sdm_tgl_gabung">Tanggal Bergabung</label>

            <input id="sdm_tgl_gabung" type="date" name="sdm_tgl_gabung"
                value="{{ $app->request->old('sdm_tgl_gabung', $sdm->sdm_tgl_gabung ?? $app->date->today()->toDateString()) }}"
                {{str()->contains($app->request->user()->sdm_hak_akses, 'SDM-PENGURUS') ? 'required' :
            'readonly'}}>

            <span class="t-bantu">Pilih atau isi tanggal</span>
        </div>

        <div class="isian normal">
            <label for="sdm_warganegara">Warganegara</label>

            <select id="sdm_warganegara" name="sdm_warganegara" class="pil-cari"
                {{str()->contains($app->request->user()->sdm_hak_akses, 'SDM-PENGURUS') ? 'required' :
                'readonly'}}>
                @if (!in_array($app->request->old('sdm_warganegara', $sdm->sdm_warganegara ?? null),
                $negaras->pluck('atur_butir')->toArray()) && $app->request->old('sdm_warganegara', $sdm->sdm_warganegara
                ??
                null))
                <option value="{{ $app->request->old('sdm_warganegara', $sdm->sdm_warganegara ?? null) }}" class="merah"
                    selected>
                    {{ $app->request->old('sdm_warganegara', $sdm->sdm_warganegara ?? null) }}
                </option>
                @endif

                @foreach ($negaras as $negara)
                <option {{ ($app->request->old('sdm_warganegara', $sdm->sdm_warganegara ?? null) == $negara->atur_butir)
                    ?
                    'selected' : (!$app->request->old('sdm_warganegara', $sdm->sdm_warganegara ?? null) &&
                    $negara->atur_butir == 'INDONESIA' ? 'selected' : '') }} @class(['merah' => $negara->atur_status ==
                    'NON-AKTIF'])>
                    {{ $negara->atur_butir }}
                </option>
                @endforeach
            </select>

            <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
        </div>

        <div class="isian normal">
            <label for="sdm_no_ktp">Nomor E-KTP/Passport</label>

            <input id="sdm_no_ktp" type="text" name="sdm_no_ktp"
                value="{{ $app->request->old('sdm_no_ktp', $sdm->sdm_no_ktp ?? null) }}" required>

            <span class="t-bantu">E-KTP/Passport valid</span>
        </div>

        <div class="isian panjang">
            <label for="sdm_nama">Nama</label>

            <input id="sdm_nama" type="text" name="sdm_nama"
                value="{{ $app->request->old('sdm_nama', $sdm->sdm_nama ?? null) }}" required maxlength="80">

            <span class="t-bantu">Nama Lengkap</span>
        </div>

        <div class="isian normal">
            <label for="sdm_tempat_lahir">Tempat Lahir</label>

            <input id="sdm_tempat_lahir" type="text" name="sdm_tempat_lahir"
                value="{{ $app->request->old('sdm_tempat_lahir', $sdm->sdm_tempat_lahir ?? null) }}" required
                maxlength="40">

            <span class="t-bantu">Nama Kota</span>
        </div>

        <div class="isian pendek">
            <label for="sdm_tgl_lahir">Tanggal Lahir</label>

            <input id="sdm_tgl_lahir" type="date" name="sdm_tgl_lahir"
                value="{{ $app->request->old('sdm_tgl_lahir', $sdm->sdm_tgl_lahir ?? $app->date->today()->subYears(18)->toDateString()) }}"
                required>

            <span class="t-bantu">Pilih atau isi tanggal</span>
        </div>

        <div class="isian pendek">
            <label for="sdm_kelamin">Kelamin</label>

            <select id="sdm_kelamin" name="sdm_kelamin" class="pil-saja" required>
                @if (!in_array($app->request->old('sdm_kelamin', $sdm->sdm_kelamin ?? null),
                $kelamins->pluck('atur_butir')->toArray()) && $app->request->old('sdm_kelamin', $sdm->sdm_kelamin ??
                null))
                <option value="{{ $app->request->old('sdm_kelamin', $sdm->sdm_kelamin ?? null) }}" class="merah"
                    selected>
                    {{ $app->request->old('sdm_kelamin', $sdm->sdm_kelamin ?? null) }}
                </option>
                @endif

                @foreach ($kelamins as $kelamin)
                <option {{ ($app->request->old('sdm_kelamin', $sdm->sdm_kelamin ?? null) == $kelamin->atur_butir) ?
                    'selected' : (!$app->request->old('sdm_kelamin', $sdm->sdm_kelamin ?? null) &&$kelamin->atur_butir
                    ==
                    'L' ? 'selected' : '') }} @class(['merah' => $kelamin->atur_status == 'NON-AKTIF'])>
                    {{ $kelamin->atur_butir }}
                </option>
                @endforeach
            </select>

            <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
        </div>

        <div class="isian pendek">
            <label for="sdm_gol_darah">Gol Darah</label>

            <select id="sdm_gol_darah" name="sdm_gol_darah" class="pil-saja">
                <option selected></option>

                @if (!in_array($app->request->old('sdm_gol_darah', $sdm->sdm_gol_darah ?? null),
                $gdarahs->pluck('atur_butir')->toArray()) && $app->request->old('sdm_gol_darah', $sdm->sdm_gol_darah ??
                null))
                <option value="{{ $app->request->old('sdm_gol_darah', $sdm->sdm_gol_darah ?? null) }}" class="merah"
                    selected>
                    {{ $app->request->old('sdm_gol_darah', $sdm->sdm_gol_darah ?? null) }}
                </option>
                @endif

                @foreach ($gdarahs as $gdarah)
                <option @selected($app->request->old('sdm_gol_darah', $sdm->sdm_gol_darah ?? null) ==
                    $gdarah->atur_butir)
                    @class(['merah' => $gdarah->atur_status == 'NON-AKTIF'])>
                    {{ $gdarah->atur_butir }}
                </option>
                @endforeach
            </select>

            <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
        </div>

        <div class="isian panjang">
            <label for="sdm_alamat">Alamat</label>

            <input id="sdm_alamat" type="text" name="sdm_alamat"
                value="{{ $app->request->old('sdm_alamat', $sdm->sdm_alamat ?? null) }}" required maxlength="120">

            <span class="t-bantu">Alamat KTP/Passport</span>
        </div>

        <div class="isian kecil">
            <label for="sdm_alamat_rt">RT</label>

            <input id="sdm_alamat_rt" type="number" name="sdm_alamat_rt" min="0"
                value="{{ $app->request->old('sdm_alamat_rt', $sdm->sdm_alamat_rt ?? null) }}">

            <span class="t-bantu">Angka</span>
        </div>

        <div class="isian kecil">
            <label for="sdm_alamat_rw">RW</label>

            <input id="sdm_alamat_rw" type="number" name="sdm_alamat_rw" min="0"
                value="{{ $app->request->old('sdm_alamat_rw', $sdm->sdm_alamat_rw ?? null) }}">

            <span class="t-bantu">Angka</span>
        </div>

        <div class="isian normal">
            <label for="sdm_alamat_kelurahan">Kelurahan</label>

            <input id="sdm_alamat_kelurahan" type="text" name="sdm_alamat_kelurahan"
                value="{{ $app->request->old('sdm_alamat_kelurahan', $sdm->sdm_alamat_kelurahan ?? null) }}" required
                maxlength="40">

            <span class="t-bantu">Kelurahan</span>
        </div>

        <div class="isian normal">
            <label for="sdm_alamat_kecamatan">Kecamatan</label>

            <input id="sdm_alamat_kecamatan" type="text" name="sdm_alamat_kecamatan"
                value="{{ $app->request->old('sdm_alamat_kecamatan', $sdm->sdm_alamat_kecamatan ?? null) }}" required
                maxlength="40">

            <span class="t-bantu">Kecamatan</span>
        </div>

        <div class="isian normal">
            <label for="sdm_alamat_kota">Kota/Kabupaten</label>

            <input id="sdm_alamat_kota" type="text" name="sdm_alamat_kota"
                value="{{ $app->request->old('sdm_alamat_kota', $sdm->sdm_alamat_kota ?? null) }}" required
                maxlength="40">

            <span class="t-bantu">Kota/Kabupaten</span>
        </div>

        <div class="isian normal">
            <label for="sdm_alamat_provinsi">Provinsi</label>

            <input id="sdm_alamat_provinsi" type="text" name="sdm_alamat_provinsi"
                value="{{ $app->request->old('sdm_alamat_provinsi', $sdm->sdm_alamat_provinsi ?? null) }}" required
                maxlength="40">

            <span class="t-bantu">Provinsi</span>
        </div>

        <div class="isian pendek">
            <label for="sdm_alamat_kodepos">Kode Pos</label>

            <input id="sdm_alamat_kodepos" type="text" name="sdm_alamat_kodepos"
                value="{{ $app->request->old('sdm_alamat_kodepos', $sdm->sdm_alamat_kodepos ?? null) }}" maxlength="10">

            <span class="t-bantu">Kode Pos</span>
        </div>

        <div class="isian normal">
            <label for="sdm_agama">Agama</label>

            <select id="sdm_agama" name="sdm_agama" class="pil-cari" required>
                @if (!in_array($app->request->old('sdm_agama', $sdm->sdm_agama ?? null),
                $agamas->pluck('atur_butir')->toArray()) && $app->request->old('sdm_agama', $sdm->sdm_agama ?? null))
                <option value="{{ $app->request->old('sdm_agama', $sdm->sdm_agama ?? null) }}" class="merah" selected>
                    {{ $app->request->old('sdm_agama', $sdm->sdm_agama ?? null) }}
                </option>
                @endif

                @foreach ($agamas as $agama)
                <option {{ ($app->request->old('sdm_agama', $sdm->sdm_agama ?? null) == $agama->atur_butir) ? 'selected'
                    :
                    (!$app->request->old('sdm_agama', $sdm->sdm_agama ?? null) && $agama->atur_butir == 'ISLAM' ?
                    'selected' : '') }} @class(['merah' => $agama->atur_status == 'NON-AKTIF'])>
                    {{ $agama->atur_butir }}
                </option>
                @endforeach
            </select>

            <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
        </div>

        <div class="isian normal">
            <label for="sdm_no_kk">Nomor KK</label>

            <input id="sdm_no_kk" type="text" name="sdm_no_kk"
                value="{{ $app->request->old('sdm_no_kk', $sdm->sdm_no_kk ?? null) }}">

            <span class="t-bantu">16 digit nomor KK</span>
        </div>

        <div class="isian normal">
            <label for="sdm_status_kawin">Status Kawin</label>

            <select id="sdm_status_kawin" name="sdm_status_kawin" class="pil-cari" required>
                @if (!in_array($app->request->old('sdm_status_kawin', $sdm->sdm_status_kawin ?? null),
                $kawins->pluck('atur_butir')->toArray()) && $app->request->old('sdm_status_kawin',
                $sdm->sdm_status_kawin
                ?? null))
                <option value="{{ $app->request->old('sdm_status_kawin', $sdm->sdm_status_kawin ?? null) }}"
                    class="merah" selected>
                    {{ $app->request->old('sdm_status_kawin', $sdm->sdm_status_kawin ?? null) }}
                </option>
                @endif
                @foreach ($kawins as $kawin)
                <option {{ ($app->request->old('sdm_status_kawin', $sdm->sdm_status_kawin ?? null) ==
                    $kawin->atur_butir)
                    ?
                    'selected' : (!$app->request->old('sdm_status_kawin', $sdm->sdm_status_kawin ?? null) &&
                    $kawin->atur_butir == 'LAJANG' ? 'selected' : '') }} @class(['merah' => $kawin->atur_status ==
                    'NON-AKTIF'])>
                    {{ $kawin->atur_butir }}
                </option>
                @endforeach
            </select>

            <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
        </div>

        <div class="isian pendek">
            <label for="sdm_jml_anak">Anak</label>

            <input id="sdm_jml_anak" type="number" name="sdm_jml_anak" min="0"
                value="{{ $app->request->old('sdm_jml_anak', $sdm->sdm_jml_anak ?? null) }}">

            <span class="t-bantu">Angka</span>
        </div>

        <div class="isian normal">
            <label for="sdm_pendidikan">Pendidikan</label>

            <select id="sdm_pendidikan" name="sdm_pendidikan" class="pil-cari" required>
                <option selected></option>

                @if (!in_array($app->request->old('sdm_pendidikan', $sdm->sdm_pendidikan ?? null),
                $pendidikans->pluck('atur_butir')->toArray()) && $app->request->old('sdm_pendidikan',
                $sdm->sdm_pendidikan
                ?? null))
                <option value="{{ $app->request->old('sdm_pendidikan', $sdm->sdm_pendidikan ?? null) }}" class="merah"
                    selected>
                    {{ $app->request->old('sdm_pendidikan', $sdm->sdm_pendidikan ?? null) }}
                </option>
                @endif

                @foreach ($pendidikans as $pendidikan)
                <option @selected($app->request->old('sdm_pendidikan', $sdm->sdm_pendidikan ?? null) ==
                    $pendidikan->atur_butir) @class(['merah' => $pendidikan->atur_status == 'NON-AKTIF'])>
                    {{ $pendidikan->atur_butir }}
                </option>
                @endforeach
            </select>

            <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
        </div>

        <div class="isian normal">
            <label for="sdm_jurusan">Jurusan</label>

            <input id="sdm_jurusan" type="text" name="sdm_jurusan"
                value="{{ $app->request->old('sdm_jurusan', $sdm->sdm_jurusan ?? null) }}" maxlength="60">

            <span class="t-bantu">Jurusan Pendidikan</span>
        </div>

        <div class="isian normal">
            <label for="sdm_telepon">Telepon</label>

            <input id="sdm_telepon" type="tel" name="sdm_telepon"
                value="{{ $app->request->old('sdm_telepon', $sdm->sdm_telepon ?? null) }}" maxlength="40"
                inputmode="tel" required>

            <span class="t-bantu">Nomor HP/WA atau</span>
        </div>

        <div class="isian normal">
            <label for="email">Email</label>

            <input id="email" type="email" name="email" value="{{ $app->request->old('email', $sdm->email ?? null) }}"
                required inputmode="email">

            <span class="t-bantu">Alamat email</span>
        </div>

        <div class="isian normal">
            <label for="sdm_disabilitas">Disabilitas</label>

            <select id="sdm_disabilitas" name="sdm_disabilitas" class="pil-cari"
                {{str()->contains($app->request->user()->sdm_hak_akses, 'SDM-PENGURUS') ? 'required' :
                'readonly'}}>
                @if (!in_array($app->request->old('sdm_disabilitas', $sdm->sdm_disabilitas ?? null),
                $disabilitas->pluck('atur_butir')->toArray()) && $app->request->old('sdm_disabilitas',
                $sdm->sdm_disabilitas ?? null))
                <option value="{{ $app->request->old('sdm_disabilitas', $sdm->sdm_disabilitas ?? null) }}" class="merah"
                    selected>
                    {{ $app->request->old('sdm_disabilitas', $sdm->sdm_disabilitas ?? null) }}
                </option>
                @endif

                @foreach ($disabilitas as $difabel)
                <option {{ ($app->request->old('sdm_disabilitas', $sdm->sdm_disabilitas ?? null) ==
                    $difabel->atur_butir)
                    ?
                    'selected' : (!$app->request->old('sdm_disabilitas', $sdm->sdm_disabilitas ?? null) &&
                    $difabel->atur_butir == 'NORMAL' ? 'selected' : '') }} @class(['merah' => $difabel->atur_status ==
                    'NON-AKTIF'])>
                    {{ $difabel->atur_butir }}
                </option>
                @endforeach
            </select>

            <span class="t-bantu">{{str()->contains($app->request->user()->sdm_hak_akses, 'SDM-PENGURUS') ?
                'Disarankan tidak
                memilih pilihan berwarna merah' : 'Perubahan tidak akan tersimpan'}}</span>
        </div>

        <div class="isian normal">
            <label for="sdm_no_bpjs">BPJS Kesehatan</label>

            <input id="sdm_no_bpjs" type="text" name="sdm_no_bpjs"
                value="{{ $app->request->old('sdm_no_bpjs', $sdm->sdm_no_bpjs ?? null) }}" maxlength="30">

            <span class="t-bantu">Nomor atau keterangan</span>
        </div>

        <div class="isian normal">
            <label for="sdm_no_jamsostek">BPJS Ketenagakerjaan</label>

            <input id="sdm_no_jamsostek" type="text" name="sdm_no_jamsostek"
                value="{{ $app->request->old('sdm_no_jamsostek', $sdm->sdm_no_jamsostek ?? null) }}" maxlength="30">

            <span class="t-bantu">Nomor atau keterangan</span>
        </div>

        <div class="isian normal">
            <label for="sdm_no_npwp">NPWP</label>

            <input id="sdm_no_npwp" type="text" name="sdm_no_npwp"
                value="{{ $app->request->old('sdm_no_npwp', $sdm->sdm_no_npwp ?? null) }}" maxlength="30">

            <span class="t-bantu">Nomor atau keterangan</span>
        </div>

        @if(str()->contains($app->request->user()->sdm_hak_akses, 'SDM-PENGURUS'))
        <div class="isian pendek">
            <label for="sdm_nama_bank">Nama Bank</label>

            <select id="sdm_nama_bank" name="sdm_nama_bank" class="pil-cari">
                <option selected></option>

                @if (!in_array($app->request->old('sdm_nama_bank', $sdm->sdm_nama_bank ?? null),
                $banks->pluck('atur_butir')->toArray()) && $app->request->old('sdm_nama_bank', $sdm->sdm_nama_bank ??
                null))
                <option value="{{ $app->request->old('sdm_nama_bank', $sdm->sdm_nama_bank ?? null) }}" class="merah"
                    selected>
                    {{ $app->request->old('sdm_nama_bank', $sdm->sdm_nama_bank ?? null) }}
                </option>
                @endif

                @foreach ($banks as $bank)
                <option @selected($app->request->old('sdm_nama_bank', $sdm->sdm_nama_bank ?? null) == $bank->atur_butir)
                    @class(['merah' => $bank->atur_status == 'NON-AKTIF'])>
                    {{ $bank->atur_butir }}
                </option>
                @endforeach
            </select>

            <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
        </div>

        <div class="isian normal">
            <label for="sdm_cabang_bank">Cabang Bank</label>

            <input id="sdm_cabang_bank" type="text" name="sdm_cabang_bank"
                value="{{ $app->request->old('sdm_cabang_bank', $sdm->sdm_cabang_bank ?? null) }}" maxlength="50">

            <span class="t-bantu">Kantor Cabang Rekening</span>
        </div>

        <div class="isian normal">
            <label for="sdm_rek_bank">Rekening Bank</label>

            <input id="sdm_rek_bank" type="text" name="sdm_rek_bank"
                value="{{ $app->request->old('sdm_rek_bank', $sdm->sdm_rek_bank ?? null) }}" maxlength="40">

            <span class="t-bantu">Nomor rekening</span>
        </div>

        <div class="isian panjang">
            <label for="sdm_an_rek">A.n Rekening</label>

            <input id="sdm_an_rek" type="text" name="sdm_an_rek"
                value="{{ $app->request->old('sdm_an_rek', $sdm->sdm_an_rek ?? null) }}" maxlength="80">

            <span class="t-bantu">Nama pemilik rekening</span>
        </div>

        <div class="isian panjang">
            <label for="sdm_nama_dok">Judul Dokumen</label>

            <input id="sdm_nama_dok" type="text" name="sdm_nama_dok"
                value="{{ $app->request->old('sdm_nama_dok', $sdm->sdm_nama_dok ?? null) }}" maxlength="50">

            <span class="t-bantu">Nama dokumen titipan</span>
        </div>

        <div class="isian normal">
            <label for="sdm_nomor_dok">Nomor Dokumen</label>

            <input id="sdm_nomor_dok" type="text" name="sdm_nomor_dok"
                value="{{ $app->request->old('sdm_nomor_dok', $sdm->sdm_nomor_dok ?? null) }}" maxlength="40">

            <span class="t-bantu">Nomor dokumen titipan</span>
        </div>

        <div class="isian panjang">
            <label for="sdm_penerbit_dok">Penerbit Dokumen</label>

            <input id="sdm_penerbit_dok" type="text" name="sdm_penerbit_dok"
                value="{{ $app->request->old('sdm_penerbit_dok', $sdm->sdm_penerbit_dok ?? null) }}" maxlength="60">

            <span class="t-bantu">Penerbit dokumen titipan</span>
        </div>

        <div class="isian panjang">
            <label for="sdm_an_dok">A.n Dokumen</label>

            <input id="sdm_an_dok" type="text" name="sdm_an_dok"
                value="{{ $app->request->old('sdm_an_dok', $sdm->sdm_an_dok ?? null) }}" maxlength="80">

            <span class="t-bantu">Nama pemilik dokumen titipan</span>
        </div>

        <div class="isian pendek">
            <label for="sdm_kadaluarsa_dok">Kadaluarsa Dokumen</label>

            <input id="sdm_kadaluarsa_dok" type="date" name="sdm_kadaluarsa_dok"
                value="{{ $app->request->old('sdm_kadaluarsa_dok', $sdm->sdm_kadaluarsa_dok ?? null) }}">

            <span class="t-bantu">Tanggal kadaluarsa dokumen titipan</span>
        </div>
        @endif

        <div class="isian pendek">
            <label for="sdm_uk_seragam">Ukuran Seragam</label>

            <select id="sdm_uk_seragam" name="sdm_uk_seragam" class="pil-cari">
                <option selected></option>

                @if (!in_array($app->request->old('sdm_uk_seragam', $sdm->sdm_uk_seragam ?? null),
                $seragams->pluck('atur_butir')->toArray()) && $app->request->old('sdm_uk_seragam', $sdm->sdm_uk_seragam
                ??
                null))
                <option value="{{ $app->request->old('sdm_uk_seragam', $sdm->sdm_uk_seragam ?? null) }}" class="merah"
                    selected>
                    {{ $app->request->old('sdm_uk_seragam', $sdm->sdm_uk_seragam ?? null) }}
                </option>
                @endif

                @foreach ($seragams as $seragam)
                <option @selected($app->request->old('sdm_uk_seragam', $sdm->sdm_uk_seragam ?? null) ==
                    $seragam->atur_butir) @class(['merah' => $seragam->atur_status == 'NON-AKTIF'])>
                    {{ $seragam->atur_butir }}
                </option>
                @endforeach
            </select>

            <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
        </div>

        <div class="isian pendek">
            <label for="sdm_uk_sepatu">No Sepatu</label>

            <input id="sdm_uk_sepatu" type="number" name="sdm_uk_sepatu" min="0"
                value="{{ $app->request->old('sdm_uk_sepatu', $sdm->sdm_uk_sepatu ?? null) }}">

            <span class="t-bantu">Angka</span>
        </div>

        @if(str()->contains($app->request->user()->sdm_hak_akses, 'SDM-PENGURUS'))
        <div class="isian gspan-4">
            <label for="sdm_ket_kary">Keterangan</label>

            <textarea id="sdm_ket_kary" name="sdm_ket_kary" rows="3">
                {{ $app->request->old('sdm_ket_kary', $sdm->sdm_ket_kary ?? null) }}
            </textarea>

            <span class="t-bantu">Keterangan lain</span>
        </div>
        @endif

        @if ($app->request->routeIs('sdm.ubah-akun'))
        @if (!$app->request->user()?->sdm_ijin_akses && str()->contains($app->request->user()->sdm_hak_akses,
        'SDM-PENGURUS'))
        <div class="isian normal">
            <label for="sdm_hak_akses">Peran</label>

            <select id="sdm_hak_akses" name="sdm_hak_akses[]" multiple class="pil-cari">
                @foreach (array_unique(array_merge($perans->pluck('atur_butir')->toArray(), explode(',',
                $app->request->old('sdm_hak_akses', $sdm->sdm_hak_akses ?? null)))) as $peran)
                <option @selected(in_array($peran, explode(',', $app->request->old('sdm_hak_akses', $sdm->sdm_hak_akses
                    ??
                    null)))) @class(['merah' => in_array($peran, $perans->where('atur_status',
                    'NON-AKTIF')->pluck('atur_butir')->toArray())])>
                    {{ $peran }}
                </option>
                @endforeach
            </select>
            <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
        </div>

        <div class="isian normal">
            <label for="sdm_ijin_akses">Akses</label>

            <select id="sdm_ijin_akses" name="sdm_ijin_akses[]" multiple class="pil-cari">
                @foreach (array_filter(array_unique(array_merge($penempatans->pluck('atur_butir')->toArray(),
                explode(',', $app->request->old('sdm_ijin_akses', $sdm->sdm_ijin_akses ?? null))))) as $penempatan)
                <option @selected(in_array($penempatan, explode(',', $app->request->old('sdm_ijin_akses',
                    $sdm->sdm_ijin_akses ?? null)))) @class(['merah' => in_array($penempatan,
                    $penempatans->where('atur_status', 'NON-AKTIF')->pluck('atur_butir')->toArray())])>
                    {{ $penempatan }}
                </option>
                @endforeach
            </select>

            <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
        </div>
        @endif

        @if(str()->contains($app->request->user()->sdm_hak_akses, 'SDM-PENGURUS'))
        <div class="isian pendek">
            <label for="sdm_tgl_berhenti">Tanggal Berhenti</label>

            <input id="sdm_tgl_berhenti" type="date" name="sdm_tgl_berhenti"
                value="{{ $app->request->old('sdm_tgl_berhenti', $sdm->sdm_tgl_berhenti ?? null) }}">

            <span class="t-bantu">Tanggal SDM non-aktif</span>
        </div>

        <div class="isian normal">
            <label for="sdm_jenis_berhenti">Jenis Berhenti</label>

            <select id="sdm_jenis_berhenti" name="sdm_jenis_berhenti" class="pil-cari">
                <option selected></option>

                @if (!in_array($app->request->old('sdm_jenis_berhenti', $sdm->sdm_jenis_berhenti ?? null),
                $phks->pluck('atur_butir')->toArray()) && $app->request->old('sdm_jenis_berhenti',
                $sdm->sdm_jenis_berhenti
                ?? null))
                <option value="{{ $app->request->old('sdm_jenis_berhenti', $sdm->sdm_jenis_berhenti ?? null) }}"
                    class="merah" selected>
                    {{ $app->request->old('sdm_jenis_berhenti', $sdm->sdm_jenis_berhenti ?? null) }}
                </option>
                @endif

                @foreach ($phks as $phk)
                <option @selected($app->request->old('sdm_jenis_berhenti', $sdm->sdm_jenis_berhenti ?? null) ==
                    $phk->atur_butir) @class(['merah' => $phk->atur_status == 'NON-AKTIF'])>
                    {{ $phk->atur_butir }}
                </option>
                @endforeach
            </select>

            <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
        </div>

        <div class="isian gspan-4">
            <label for="sdm_ket_berhenti">Keterangan Berhenti</label>

            <textarea id="sdm_ket_berhenti" name="sdm_ket_berhenti" rows="3">
                {{ $app->request->old('sdm_ket_berhenti', $sdm->sdm_ket_berhenti ?? null) }}
            </textarea>

            <span class="t-bantu">Keterangan lain pelepasan</span>
        </div>

        <div class="isian gspan-4">
            <label>Berkas SDM</label>

            @if ($app->filesystem->exists($berkasSDM = 'sdm/berkas/'. $sdm->sdm_no_absen.'.pdf'))
            <iframe class="berkas tcetak"
                src="{{ $app->url->route('sdm.berkas', ['berkas' =>  $berkasSDM . '?' . filemtime($app->storagePath('app/' . $berkasSDM))]) }}"
                title="Berkas SDM" loading="lazy"
                onload="if (this.contentDocument.body.id == 'badan-dokumen') this.remove()">
            </iframe>

            <a class="sekunder tcetak" target="_blank" title="Unduh Berkas Terunggah"
                href="{{ $app->url->route('sdm.berkas', ['berkas' =>  $berkasSDM . '?' . filemtime($app->storagePath('app/' . $berkasSDM))]) }}">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonunduh"></use>
                </svg>
                BERKAS
            </a>

            @else
            <p class="merah">Tidak ada berkas terunggah.</p>

            @endif
        </div>
        @endif
        @endif

        @if(str()->contains($app->request->user()->sdm_hak_akses, 'SDM-PENGURUS'))
        <div class="isian normal">
            <label for="sdm_berkas">Unggah Berkas</label>

            <input id="sdm_berkas" type="file" name="sdm_berkas" accept=".pdf,application/pdf">

            <span class="t-bantu">
                Scan PDF data isian pelamar, lamaran, riwayat hidup, rangkuman tes, persetujuan gaji,
                tanda terima dokumen titipan, serah terima, pengunduran diri dan pelepasan
                {{$app->filesystem->exists('sdm/berkas/'.$app->request->old('sdm_no_absen', $sdm->sdm_no_absen ??
                null).'.pdf') ? '(berkas yang diunggah akan menindih berkas unggahan lama).' : '' }}
            </span>
        </div>
        @endif

        <div class="gspan-4"></div>

        <button class="utama pelengkap" type="submit">SIMPAN</button>

        @if ($sdm->sdm_uuid ?? null)
        <a class="sekunder isi-xhr" href="{{ $app->url->route('sdm.akun', ['uuid' => $sdm->sdm_uuid]) }}">TUTUP</a>
        @else
        <a class="sekunder isi-xhr"
            href="{{ $app->url->to($app->request->session()->get('tautan_perujuk') ?? '/') }}">TUTUP</a>
        @endif

    </form>

    <script>
        (async() => {
            while(!window.aplikasiSiap) {
                await new Promise((resolve,reject) =>
                setTimeout(resolve, 1000));
            }
            
            pilSaja('#form_tambahUbahAkun .pil-saja');
            pilCari('#form_tambahUbahAkun .pil-cari');
            pilDasar('#form_tambahUbahAkun .pil-dasar');
            formatIsian('#form_tambahUbahAkun .isian :is(textarea,input[type=text],input[type=search])');
        })();
        function siapkanFoto(berkas) {
            if (!window.SiapkanFoto) {
                import("{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/siapkan-foto-es.js')) }}").then(({default : SF}) => {
                    window.SiapkanFoto = SF; 
                    new SiapkanFoto(berkas);
                });
            } else {
                window.SiapkanFoto 
                ? new SiapkanFoto(berkas) 
                : (function () {
                    document.getElementById('foto_profil').value = '';
                    alert('Terjadi kesalahan dalam memroses foto profil. Modul pemrosesan foto tidak ditemukan. Harap hubungi Personalia Pusat.');
                })();
            };
        }
    </script>

    @include('pemberitahuan')
    @include('komponen')
</div>
@endsection