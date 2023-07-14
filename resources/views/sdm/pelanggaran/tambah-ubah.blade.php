@extends('rangka')

@section('isi')
<div id="sdm_pelanggaran_tambahUbah">
    <form id="form_sdm_pelanggaran_tambahUbah" class="form-xhr kartu" method="POST"
        action="{{ $urlRangka->current() }}">
        <input type="hidden" name="_token" value="{{ $rekRangka->session()->token() }}">

        <div class="gspan-4">
            <a class="tutup-i">
                <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#tutup' }}"
                        xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg>
            </a>

            <h4 class="form">{{$rekRangka->routeIs('sdm.pelanggaran.tambah') ? 'Tambah' : 'Ubah'}} Data Pelanggaran SDM
            </h4>
        </div>

        <div class="isian panjang">
            <label for="sdm_pelanggaran_tambahUbahPelapor">Pelapor</label>
            <select id="sdm_pelanggaran_tambahUbahPelapor" name="langgar_pelapor" class="pil-cari" required>
                <option selected></option>
                @if (!in_array($rekRangka->old('langgar_pelapor', $langgar->langgar_pelapor ?? null),
                $sdms->pluck('sdm_no_absen')->toArray()) && !is_null($rekRangka->old('langgar_pelapor',
                $langgar->langgar_pelapor ?? null)))
                <option value="{{ $rekRangka->old('langgar_pelapor', $langgar->langgar_pelapor ?? null) }}"
                    class="merah" selected>{{ $rekRangka->old('langgar_pelapor', $langgar->langgar_pelapor ?? null) }}
                </option>
                @endif
                @foreach ($sdms as $sdm)
                <option @selected($sdm->sdm_no_absen == $rekRangka->old('langgar_pelapor', $langgar->langgar_pelapor ??
                    null)) value="{{ $sdm->sdm_no_absen }}">{{ $sdm->sdm_no_absen }} - {{ $sdm->sdm_nama }} - {{
                    $sdm->penempatan_posisi }}</option>
                @endforeach
            </select>
            <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
        </div>

        <div class="isian panjang">
            <label for="sdm_pelanggaran_tambahUbahTerlapor">Terlapor</label>
            <select id="sdm_pelanggaran_tambahUbahTerlapor" {{ $rekRangka->routeIs('sdm.pelanggaran.ubah') ?
                'name=langgar_no_absen' : 'name=langgar_no_absen[] multiple'}} class="pil-cari" required>
                @if ($rekRangka->routeIs('sdm.pelanggaran.ubah')) <option selected></option> @endif
                @if (!in_array($rekRangka->old('langgar_no_absen', $langgar->langgar_no_absen ?? null),
                $sdms->pluck('sdm_no_absen')->toArray()) && !is_null($rekRangka->old('langgar_no_absen',
                $langgar->langgar_no_absen ?? null)))
                <option value="{{ $rekRangka->old('langgar_no_absen', $langgar->langgar_no_absen ?? null) }}"
                    class="merah" selected>{{ $rekRangka->old('langgar_no_absen', $langgar->langgar_no_absen ?? null) }}
                    - NON-AKTIF</option>
                @endif
                @foreach ($sdms as $sdm)
                <option @selected($sdm->sdm_no_absen == $rekRangka->old('langgar_no_absen', $langgar->langgar_no_absen
                    ?? null)) value="{{ $sdm->sdm_no_absen }}">{{ $sdm->sdm_no_absen }} - {{ $sdm->sdm_nama }} - {{
                    $sdm->penempatan_posisi }}</option>
                @endforeach
            </select>
            <span class="t-bantu">Pilih satu atau lebih, disarankan tidak memilih pilihan berwarna merah</span>
        </div>

        <div class="isian pendek">
            <label for="sdm_pelanggaran_tambahUbahTglLapor">Tanggal Laporan</label>
            <input id="sdm_pelanggaran_tambahUbahTglLapor" type="date" name="langgar_tanggal"
                value="{{ $rekRangka->old('langgar_tanggal', $langgar->langgar_tanggal ?? $dateRangka->today()->toDateString()) }}"
                required>
            <span class="t-bantu">Pilih atau isi tanggal</span>
        </div>

        @if ($rekRangka->routeIs('sdm.pelanggaran.ubah'))
        <div class="isian pendek">
            <label for="sdm_langgar_tambahUbahStatus">Status</label>
            <select id="sdm_langgar_tambahUbahStatus" name="langgar_status" class="pil-saja" required>
                <option @selected($rekRangka->old('langgar_status', $langgar->langgar_status ?? null) ==
                    'DIPROSES')>DIPROSES</option>
                <option @selected($rekRangka->old('langgar_status', $langgar->langgar_status ?? null) ==
                    'DIBATALKAN')>DIBATALKAN</option>
            </select>
            <span class="t-bantu">Pilih satu</span>
        </div>
        @endif

        <div class="isian gspan-4">
            <label for="sdm_langgar_tambahUbahIsiLaporan">Dugaan Pelanggaran</label>
            <textarea id="sdm_langgar_tambahUbahIsiLaporan" name="langgar_isi" rows="3"
                required>{{ $rekRangka->old('langgar_isi', $langgar->langgar_isi ?? null) }}</textarea>
            <span class="t-bantu">Isi aduan pelanggaran</span>
        </div>

        <div class="isian gspan-4">
            <label for="sdm_langgar_tambahUbahKeterangan">Keterangan</label>
            <textarea id="sdm_langgar_tambahUbahKeterangan" name="langgar_keterangan"
                rows="3">{{ $rekRangka->old('langgar_keterangan', $langgar->langgar_keterangan ?? null) }}</textarea>
            <span class="t-bantu">Isi keterangan lain yang diperlukan</span>
        </div>

        <div class="isian gspan-2">
            <label for="sdm_langgar_tambahUbahUnggahBerkas">Unggah/Perbarui Bukti Pelanggaran</label>
            <input id="sdm_langgar_tambahUbahUnggahBerkas" type="file" name="berkas_laporan"
                accept=".pdf,application/pdf">
            <span class="t-bantu">Scan PDF bukti pendukung pelanggaran {{
                $storageRangka->exists('sdm/pelanggaran/berkas/'. $rekRangka->old('sdm_no_absen',
                $langgar->langgar_lap_no ??
                null) .'.pdf') ? '(berkas yang diunggah akan menindih berkas unggahan lama).' : '' }}</span>
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

            pilSaja('#form_sdm_pelanggaran_tambahUbah .pil-saja');
            pilCari('#form_sdm_pelanggaran_tambahUbah .pil-cari');
            formatIsian('#form_sdm_pelanggaran_tambahUbah .isian :is(textarea,input[type=text],input[type=search])');
        })();
    </script>

    @include('pemberitahuan')
    @include('komponen')
</div>
@endsection