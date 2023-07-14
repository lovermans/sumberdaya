@extends('rangka')

@section('isi')
<div id="sdm_nilai_tambahUbah">
    <form id="form_sdm_nilai_tambahUbah" class="form-xhr kartu" method="POST" action="{{ $urlRangka->current() }}">
        <input type="hidden" name="_token" value="{{ $rekRangka->session()->token() }}">

        <div class="gspan-4">
            <a class="tutup-i">
                <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#tutup' }}"
                        xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg>
            </a>

            <h4 class="form">{{$rekRangka->routeIs('sdm.penilaian.tambah') ? 'Tambah' : 'Ubah'}} Penilaian SDM</h4>
        </div>

        @if ($rekRangka->routeIs('sdm.penilaian.tambah'))
        <div class="isian panjang">
            <label for="nilaisdm_no_absen">Identitas SDM</label>

            <select id="nilaisdm_no_absen" name="nilaisdm_no_absen" class="pil-cari" required>
                <option selected disabled></option>

                @foreach ($sdms as $sdm)
                <option @selected($sdm->sdm_no_absen==$rekRangka->old('nilaisdm_no_absen',
                    $nilai->nilaisdm_no_absen ?? null)) value={{ $sdm->sdm_no_absen }}>{{ $sdm->sdm_no_absen . ' - ' .
                    $sdm->sdm_nama . ' - ' . $sdm->penempatan_lokasi . ' - ' . $sdm->penempatan_kontrak . ' - ' .
                    $sdm->penempatan_posisi }}</option>
                @endforeach
            </select>

            <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
        </div>
        @endif

        <div class="isian normal">
            <label for="nilaisdm_tahun">Tahun Penilaian</label>

            <select id="nilaisdm_tahun" name="nilaisdm_tahun" class="pil-cari" required>
                <option selected disabled></option>

                @foreach (range(2020,date("Y")) as $tahun)
                <option @selected($tahun==$rekRangka->old('nilaisdm_tahun',
                    $nilai->nilaisdm_tahun ?? null))>{{ $tahun }}</option>
                @endforeach
            </select>

            <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
        </div>

        <div class="isian normal">
            <label for="nilaisdm_periode">Periode Penilaian</label>

            <select id="nilaisdm_periode" name="nilaisdm_periode" class="pil-cari" required>
                <option selected disabled></option>

                <option @selected($rekRangka->old('nilaisdm_periode',
                    $nilai->nilaisdm_periode ?? null) == 'SEMESTER-I')>SEMESTER-I</option>
                <option @selected($rekRangka->old('nilaisdm_periode',
                    $nilai->nilaisdm_periode ?? null) == 'SEMESTER-II')>SEMESTER-II</option>
            </select>

            <span class="t-bantu">Isi tanggal</span>
        </div>

        <div class="isian pendek">
            <label for="nilaisdm_bobot_hadir">Nilai Bobot Kehadiran</label>

            <input id="nilaisdm_bobot_hadir" type="number" name="nilaisdm_bobot_hadir"
                value="{{ $rekRangka->old('nilaisdm_bobot_hadir', $nilai->nilaisdm_bobot_hadir ?? null) }}"
                step="0.0001" max="30">

            <span class="t-bantu">Nilai Maksimal = 30</span>
        </div>

        <div class="isian pendek">
            <label for="nilaisdm_bobot_sikap">Nilai Bobot Sikap Kerja</label>

            <input id="nilaisdm_bobot_sikap" type="number" name="nilaisdm_bobot_sikap"
                value="{{ $rekRangka->old('nilaisdm_bobot_sikap', $nilai->nilaisdm_bobot_sikap ?? null) }}"
                step="0.0001" max="30">

            <span class="t-bantu">Nilai Maksimal = 30</span>
        </div>

        <div class="isian pendek">
            <label for="nilaisdm_bobot_target">Nilai Bobot Target Kerja</label>

            <input id="nilaisdm_bobot_target" type="number" name="nilaisdm_bobot_target"
                value="{{ $rekRangka->old('nilaisdm_bobot_target', $nilai->nilaisdm_bobot_target ?? null) }}"
                step="0.0001" max="40">

            <span class="t-bantu">Nilai Maksimal = 40</span>
        </div>

        <div class="isian gspan-4">
            <label for="nilaisdm_tindak_lanjut">Tindak Lanjut Penilaian</label>

            <textarea id="nilaisdm_tindak_lanjut" name="nilaisdm_tindak_lanjut"
                rows="3">{{ $rekRangka->old('nilaisdm_tindak_lanjut', $nilai->nilaisdm_tindak_lanjut ?? null) }}</textarea>

            <span class="t-bantu">Dapat berupa promosi/rotasi/mutasi/demosi, sanksi, pelatihan, pemutusan kontrak,
                dsj</span>
        </div>

        <div class="isian gspan-4">
            <label for="nilaisdm_keterangan">Keterangan Penilaian</label>

            <textarea id="nilaisdm_keterangan" name="nilaisdm_keterangan"
                rows="3">{{ $rekRangka->old('nilaisdm_keterangan', $nilai->nilaisdm_keterangan ?? null) }}</textarea>

            <span class="t-bantu">Keterangan lain terkait informasi penilaian</span>
        </div>

        <div class="isian normal">
            <label for="nilai_berkas">Unggah Dokumen Penilaian</label>

            <input id="nilai_berkas" type="file" name="nilai_berkas" accept=".pdf,application/pdf">

            <span class="t-bantu">Scan PDF formulir penilaian {{
                $storageRangka->exists('sdm/penilaian/berkas/'. $rekRangka->old('nilaisdm_no_absen',
                $nilai->nilaisdm_no_absen ??
                null) . ' - ' . $rekRangka->old('nilaisdm_tahun', $nilai->nilaisdm_tahun ?? null) . ' - ' .
                $rekRangka->old('nilaisdm_periode', $nilai->nilaisdm_periode ?? null) . '.pdf') ? '(berkas yang diunggah
                akan menindih berkas unggahan lama).' : '' }}</span>
        </div>

        <div class="gspan-4"></div>

        <button class="utama pelengkap" type="submit">SIMPAN</button>
    </form>

    <script>
        (async() => {
            while(!window.aplikasiSiap) {
                await new Promise((resolve,reject) =>
                setTimeout(resolve, 1000));
            }

            pilCari('#form_sdm_nilai_tambahUbah .pil-cari');
            formatIsian('#form_sdm_nilai_tambahUbah .isian :is(textarea,input[type=text],input[type=search])');
        })();
    </script>

    @include('pemberitahuan')
    @include('komponen')
</div>
@endsection