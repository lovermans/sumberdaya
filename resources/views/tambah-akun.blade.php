@extends('rangka')

@section('isi')
    <form class="form-xhr kartu tcetak" method="POST" action="{{ route('register') }}">
        @csrf
        <div style="grid-row:span 2;text-align:center">
            <img id="foto" style="width:8em" class="svg" src="{{ asset(mix('/ikon.svg')) . '#akun' }}"
                alt="foto akun" loading="lazy">
        </div>
        <div class="isian gspan-2">
            <label for="foto_profil">Foto Profil</label>
            <input id="foto_profil" type="file" name="foto_profil" accept="image/*" capture
                onchange="window.siapkanFoto(this);">
            <span class="t-bantu">Pilih gambar atau ambil dari kamera</span>
        </div>

        @isset($permintaanSdms)
            <div class="isian gspan-2">
                <label for="permintaan_sdm_no">No Permintaan</label>
                <select name="permintaan_sdm_no" id="permintaan_sdm_no" class="pil-cari" required>
                    <option value=""></option>
                    @foreach ($permintaanSdms as $permintaanSdm)
                        <option value="{{ $permintaanSdm->permintaan_sdm_no }}" @selected(old('permintaan_sdm_no') == $permintaanSdm->permintaan_sdm_no)>
                            {{ $permintaanSdm->permintaan_sdm_no }} (KURANG
                            {{ $permintaanSdm->permintaan_sdm_jml - $permintaanSdm->permintaan_sdm_terpenuhi }}
                            {{ $permintaanSdm->permintaan_sdm_posisi }})</option>
                    @endforeach
                </select>
                <span class="t-bantu">Pilih satu</span>
            </div>
        @else
            <div class="isian">
                <label for="permintaan_sdm_no">Nomor Permintaan SDM</label>
                <input id="permintaan_sdm_no" type="text" name="permintaan_sdm_no" value="{{ old('permintaan_sdm_no') }}"
                    maxlength="20">
                <span class="t-bantu">Nomor Permintaan SDM</span>
            </div>
        @endisset

        <div class="isian">
            <label for="sdm_no_absen">Nomor Absen</label>
            <input id="sdm_no_absen" type="text" name="sdm_no_absen" value="{{ old('sdm_no_absen') }}"
                pattern="^[0-9]{8}$" inputmode="numeric" required>
            <span class="t-bantu">8 digit nomor absen</span>
        </div>
        <div class="isian">
            <label for="sdm_tgl_gabung">Tanggal Bergabung</label>
            <input id="sdm_tgl_gabung" type="date" name="sdm_tgl_gabung" value="{{ old('sdm_tgl_gabung') }}" required>
            <span class="t-bantu">Pilih atau isi tanggal</span>
        </div>
        <div class="isian">
            <label for="sdm_warganegara">Warganegara</label>
            <select name="sdm_warganegara" id="sdm_warganegara" class="pil-tambah" required>
                <option value=""></option>
                <option @selected(old('permintaan_sdm_no') == 'INDONESIA')>INDONESIA</option>
                <option @selected(old('permintaan_sdm_no') == 'CHINA')>CHINA</option>
                <option @selected(old('permintaan_sdm_no') == 'AMERIKA')>AMERIKA</option>
            </select>
            <span class="t-bantu">Pilih satu atau tambah pilihan</span>
        </div>
        <div class="isian">
            <label for="sdm_no_ktp">Nomor E-KTP/Passport</label>
            <input id="sdm_no_ktp" type="text" name="sdm_no_ktp" value="{{ old('sdm_no_ktp') }}" required>
            <span class="t-bantu">E-KTP/Passport valid</span>
        </div>
        <div class="isian gspan-2">
            <label for="sdm_nama">Nama</label>
            <input id="sdm_nama" type="text" name="sdm_nama" value="{{ old('sdm_nama') }}" required maxlength="80">
            <span class="t-bantu">Nama Lengkap</span>
        </div>
        <div class="isian">
            <label for="sdm_tempat_lahir">Tempat Lahir</label>
            <input id="sdm_tempat_lahir" type="text" name="sdm_tempat_lahir" value="{{ old('sdm_tempat_lahir') }}"
                required maxlength="40">
            <span class="t-bantu">Nama Kota</span>
        </div>
        <div class="isian">
            <label for="sdm_tgl_lahir">Tanggal Lahir</label>
            <input id="sdm_tgl_lahir" type="date" name="sdm_tgl_lahir" value="{{ old('sdm_tgl_lahir') }}" required>
            <span class="t-bantu">Pilih atau isi tanggal</span>
        </div>
        <div class="isian">
            <div class="belah">
                <label for="sdm_kelamin">Kelamin</label>
                <select name="sdm_kelamin" id="sdm_kelamin" class="pil-saja" required>
                    <option value=""></option>
                    <option @selected(old('sdm_kelamin') == 'L')>L</option>
                    <option @selected(old('sdm_kelamin') == 'P')>P</option>
                </select>
                <span class="t-bantu">Pilih satu</span>
            </div>
            <div class="belah">
                <label for="sdm_gol_darah">Gol Darah</label>
                <select name="sdm_gol_darah" id="sdm_gol_darah" class="pil-saja">
                    <option value=""></option>
                    <option @selected(old('sdm_gol_darah') == 'A')>A</option>
                    <option @selected(old('sdm_gol_darah') == 'B')>B</option>
                    <option @selected(old('sdm_gol_darah') == 'AB')>AB</option>
                    <option @selected(old('sdm_gol_darah') == 'O')>O</option>
                </select>
                <span class="t-bantu">Pilih satu</span>
            </div>
        </div>
        <div class="isian gspan-2">
            <label for="sdm_alamat">Alamat</label>
            <input id="sdm_alamat" type="text" name="sdm_alamat" value="{{ old('sdm_alamat') }}" required
                maxlength="120">
            <span class="t-bantu">Alamat KTP/Passport</span>
        </div>
        <div class="isian">
            <div class="belah">
                <label for="sdm_alamat_rt">RT</label>
                <input id="sdm_alamat_rt" type="number" name="sdm_alamat_rt" min="0" value="{{ old('sdm_alamat_rt') }}">
                <span class="t-bantu">Angka</span>
            </div>
            <div class="belah">
                <label for="sdm_alamat_rw">RW</label>
                <input id="sdm_alamat_rw" type="number" name="sdm_alamat_rw" min="0" value="{{ old('sdm_alamat_rw') }}">
                <span class="t-bantu">Angka</span>
            </div>
        </div>
        <div class="isian">
            <label for="sdm_alamat_kelurahan">Kelurahan</label>
            <input id="sdm_alamat_kelurahan" type="text" name="sdm_alamat_kelurahan"
                value="{{ old('sdm_alamat_kelurahan') }}" required maxlength="40">
            <span class="t-bantu">Kelurahan</span>
        </div>
        <div class="isian">
            <label for="sdm_alamat_kecamatan">Kecamatan</label>
            <input id="sdm_alamat_kecamatan" type="text" name="sdm_alamat_kecamatan"
                value="{{ old('sdm_alamat_kecamatan') }}" required maxlength="40">
            <span class="t-bantu">Kecamatan</span>
        </div>
        <div class="isian">
            <label for="sdm_alamat_kota">Kota/Kabupaten</label>
            <input id="sdm_alamat_kota" type="text" name="sdm_alamat_kota" value="{{ old('sdm_alamat_kota') }}" required
                maxlength="40">
            <span class="t-bantu">Kota/Kabupaten</span>
        </div>
        <div class="isian">
            <label for="sdm_alamat_provinsi">Provinsi</label>
            <input id="sdm_alamat_provinsi" type="text" name="sdm_alamat_provinsi"
                value="{{ old('sdm_alamat_provinsi') }}" required maxlength="40">
            <span class="t-bantu">Provinsi</span>
        </div>
        <div class="isian">
            <label for="sdm_alamat_kodepos">Kode Pos</label>
            <input id="sdm_alamat_kodepos" type="text" name="sdm_alamat_kodepos" value="{{ old('sdm_alamat_kodepos') }}"
                maxlength="10">
            <span class="t-bantu">Kode Pos</span>
        </div>
        <div class="isian">
            <label for="sdm_agama">Agama</label>
            <select name="sdm_agama" id="sdm_agama" class="pil-cari" required>
                <option value=""></option>
                <option @selected(old('sdm_agama') == 'ISLAM')>ISLAM</option>
                <option @selected(old('sdm_agama') == 'PROTESTAN')>PROTESTAN</option>
                <option @selected(old('sdm_agama') == 'KATOLIK')>KATOLIK</option>
                <option @selected(old('sdm_agama') == 'HINDU')>HINDU</option>
                <option @selected(old('sdm_agama') == 'BUDDHA')>BUDDHA</option>
                <option @selected(old('sdm_agama') == 'KONGHUCU')>KONGHUCU</option>
                <option @selected(old('sdm_agama') == 'KEPERCAYAAN LAIN')>KEPERCAYAAN LAIN</option>
            </select>
            <span class="t-bantu">Pilih satu atau tambah</span>
        </div>
        <div class="isian">
            <label for="sdm_no_kk">Nomor KK</label>
            <input id="sdm_no_kk" type="text" name="sdm_no_kk" value="{{ old('sdm_no_kk') }}">
            <span class="t-bantu">16 digit nomor KK</span>
        </div>
        <div class="isian">
            <label for="sdm_status_kawin">Status Kawin</label>
            <select name="sdm_status_kawin" id="sdm_status_kawin" class="pil-cari" required>
                <option value=""></option>
                <option @selected(old('sdm_status_kawin') == 'LAJANG')>LAJANG</option>
                <option @selected(old('sdm_status_kawin') == 'MENIKAH')>MENIKAH</option>
                <option @selected(old('sdm_status_kawin') == 'DUDA')>DUDA</option>
                <option @selected(old('sdm_status_kawin') == 'JANDA')>JANDA</option>
            </select>
            <span class="t-bantu">Pilih satu</span>
        </div>
        <div class="isian">
            <div class="belah">
                <label for="sdm_jml_anak">Anak</label>
                <input id="sdm_jml_anak" type="number" name="sdm_jml_anak" min="0" value="{{ old('sdm_jml_anak') }}">
                <span class="t-bantu">Angka</span>
            </div>
        </div>
        <div class="isian">
            <label for="sdm_pendidikan">Pendidikan</label>
            <select name="sdm_pendidikan" id="sdm_pendidikan" class="pil-cari" required>
                <option value=""></option>
                <option @selected(old('sdm_pendidikan') == 'SD')>SD</option>
                <option @selected(old('sdm_pendidikan') == 'SMP')>SMP</option>
                <option @selected(old('sdm_pendidikan') == 'SMA')>SMA</option>
                <option @selected(old('sdm_pendidikan') == 'SMK')>SMK</option>
                <option @selected(old('sdm_pendidikan') == 'SMA/K')>SMA/K</option>
                <option @selected(old('sdm_pendidikan') == 'D1')>D1</option>
                <option @selected(old('sdm_pendidikan') == 'D2')>D2</option>
                <option @selected(old('sdm_pendidikan') == 'D3')>D3</option>
                <option @selected(old('sdm_pendidikan') == 'D4')>D4</option>
                <option @selected(old('sdm_pendidikan') == 'S1')>S1</option>
                <option @selected(old('sdm_pendidikan') == 'D4/S1')>D4/S1</option>
                <option @selected(old('sdm_pendidikan') == 'S2')>S2</option>
                <option @selected(old('sdm_pendidikan') == 'S3')>S3</option>
                <option @selected(old('sdm_pendidikan') == 'TIDAK SEKOLAH')>TIDAK SEKOLAH</option>
            </select>
            <span class="t-bantu">Pilih satu</span>
        </div>
        <div class="isian">
            <label for="sdm_jurusan">Jurusan</label>
            <input id="sdm_jurusan" type="text" name="sdm_jurusan" value="{{ old('sdm_jurusan') }}" maxlength="60">
            <span class="t-bantu">Jurusan Pendidikan</span>
        </div>
        <div class="isian">
            <label for="sdm_telepon">Telepon</label>
            <input id="sdm_telepon" type="tel" name="sdm_telepon" value="{{ old('sdm_telepon') }}" maxlength="40"
                inputmode="tel" required>
            <span class="t-bantu">Nomor HP/WA</span>
        </div>
        <div class="isian">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required inputmode="email">
            <span class="t-bantu">Alamat email</span>
        </div>
        <div class="isian">
            <label for="sdm_disabilitas">Disabilitas</label>
            <select name="sdm_disabilitas" id="sdm_disabilitas" class="pil-cari" required>
                <option value=""></option>
                <option @selected(old('sdm_disabilitas') == 'NORMAL')>NORMAL</option>
                <option @selected(old('sdm_disabilitas') == 'DISABILITAS')>DISABILITAS</option>
            </select>
            <span class="t-bantu">Pilih satu</span>
        </div>
        <div class="pintasan"><button type="submit"><span class="judul">SIMPAN</span></button></div>
    </form>
    <script>
        var pilihC = document.querySelectorAll(".pil-cari"),
            pilihT = document.querySelectorAll(".pil-tambah"),
            pilihS = document.querySelectorAll(".pil-saja"),
            pilih = document.querySelectorAll(".pil");
        pilihT.forEach(function(a) {
            new SlimSelect({
                select: a,
                searchPlaceholder: "CARI",
                searchText: "KOSONG",
                searchingText: "MENCARI...",
                // showContent: "down",
                placeholder: "PILIH",
                showSearch: !0,
                searchFocus: !1,
                allowDeselect: !0,
                addToBody: !1,
                hideSelectedOption: !0,
                selectByGroup: !0,
                closeOnSelect: !0,
                addable: function(b) {
                    return b;
                }
            })
        });
        pilihC.forEach(function(a) {
            new SlimSelect({
                select: a,
                searchPlaceholder: "CARI",
                searchText: "KOSONG",
                searchingText: "MENCARI...",
                // showContent: "down",
                placeholder: "PILIH",
                showSearch: !0,
                searchFocus: !1,
                allowDeselect: !0,
                addToBody: !1,
                hideSelectedOption: !0,
                selectByGroup: !0,
                closeOnSelect: !0
            })
        });
        pilih.forEach(function(a) {
            new SlimSelect({
                select: a,
                showSearch: !1,
                searchFocus: !1,
                allowDeselect: !0,
                // showContent: "down",
                addToBody: !1,
                hideSelectedOption: !0,
                selectByGroup: !0,
                closeOnSelect: !0
            })
        });
        pilihS.forEach(function(a) {
            new SlimSelect({
                select: a,
                showSearch: !1,
                searchFocus: !1,
                allowDeselect: !1,
                // showContent: "down",
                addToBody: !1,
                hideSelectedOption: !0,
                selectByGroup: !1,
                closeOnSelect: !0
            })
        });
        var masukanI = document.querySelectorAll(".isian :is(textarea,input[type=text],input[type=search])");
        masukanI.forEach(function(a) {
            a.oninput = function() {
                this.value = this.value.toUpperCase()
            }
        });
        var obj = {{ Js::from($data) }};
    </script>
    @includeWhen(session()->has('spanduk') || session()->has('pesan') || $errors->any(), 'pemberitahuan')
@endsection
