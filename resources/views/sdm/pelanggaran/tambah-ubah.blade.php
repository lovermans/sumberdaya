@extends('rangka')

@section('isi')
    <div id="sdm_pelanggaran_tambahUbah">
        <form class="form-xhr kartu" id="form_sdm_pelanggaran_tambahUbah" method="POST" action="{{ $app->url->current() }}">
            <input name="_token" type="hidden" value="{{ $app->request->session()->token() }}">

            <div class="judul-form gspan-4">
                <h4 class="form">
                    {{ $app->request->routeIs('sdm.pelanggaran.tambah') ? 'Tambah' : 'Ubah' }} Data Pelanggaran SDM
                </h4>

                <a class="tutup-i">
                    <svg viewbox="0 0 24 24">
                        <use href="#ikontutup"></use>
                    </svg>
                </a>
            </div>

            <div class="isian panjang">
                <label for="sdm_pelanggaran_tambahUbahPelapor">Pelapor</label>
                <select class="pil-cari" id="sdm_pelanggaran_tambahUbahPelapor" name="langgar_pelapor" required>
                    <option selected></option>

                    @if (
                        !in_array($app->request->old('langgar_pelapor', $langgar->langgar_pelapor ?? null), $sdms->pluck('sdm_no_absen')->toArray()) &&
                            !is_null($app->request->old('langgar_pelapor', $langgar->langgar_pelapor ?? null)))
                        <option class="merah" value="{{ $app->request->old('langgar_pelapor', $langgar->langgar_pelapor ?? null) }}" selected>
                            {{ $app->request->old('langgar_pelapor', $langgar->langgar_pelapor ?? null) }}
                        </option>
                    @endif

                    @foreach ($sdms as $sdm)
                        <option value="{{ $sdm->sdm_no_absen }}" @selected($sdm->sdm_no_absen == $app->request->old('langgar_pelapor', $langgar->langgar_pelapor ?? null))>
                            {{ $sdm->sdm_no_absen }} - {{ $sdm->sdm_nama }} - {{ $sdm->penempatan_posisi }}
                        </option>
                    @endforeach
                </select>
                <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
            </div>

            <div class="isian panjang">
                <label for="sdm_pelanggaran_tambahUbahTerlapor">Terlapor</label>
                <select class="pil-cari" id="sdm_pelanggaran_tambahUbahTerlapor"
                    {{ $app->request->routeIs('sdm.pelanggaran.ubah') ? 'name=langgar_no_absen' : 'name=langgar_no_absen[] multiple' }} required>
                    @if ($app->request->routeIs('sdm.pelanggaran.ubah'))
                        <option selected></option>
                    @endif

                    @if (
                        !in_array($app->request->old('langgar_no_absen', $langgar->langgar_no_absen ?? null), $sdms->pluck('sdm_no_absen')->toArray()) &&
                            !is_null($app->request->old('langgar_no_absen', $langgar->langgar_no_absen ?? null)))
                        <option class="merah" value="{{ $app->request->old('langgar_no_absen', $langgar->langgar_no_absen ?? null) }}" selected>
                            {{ $app->request->old('langgar_no_absen', $langgar->langgar_no_absen ?? null) }} - NON-AKTIF
                        </option>
                    @endif

                    @foreach ($sdms as $sdm)
                        <option value="{{ $sdm->sdm_no_absen }}" @selected($sdm->sdm_no_absen == $app->request->old('langgar_no_absen', $langgar->langgar_no_absen ?? null))>
                            {{ $sdm->sdm_no_absen }} - {{ $sdm->sdm_nama }} - {{ $sdm->penempatan_posisi }}
                        </option>
                    @endforeach
                </select>

                <span class="t-bantu">Pilih satu atau lebih, disarankan tidak memilih pilihan berwarna merah</span>
            </div>

            <div class="isian pendek">
                <label for="sdm_pelanggaran_tambahUbahTglLapor">Tanggal Laporan</label>
                <input id="sdm_pelanggaran_tambahUbahTglLapor" name="langgar_tanggal" type="date"
                    value="{{ $app->request->old('langgar_tanggal', $langgar->langgar_tanggal ?? $app->date->today()->toDateString()) }}" required>
                <span class="t-bantu">Pilih atau isi tanggal</span>
            </div>

            @if ($app->request->routeIs('sdm.pelanggaran.ubah'))
                <div class="isian pendek">
                    <label for="sdm_langgar_tambahUbahStatus">Status</label>
                    <select class="pil-saja" id="sdm_langgar_tambahUbahStatus" name="langgar_status" required>
                        <option @selected($app->request->old('langgar_status', $langgar->langgar_status ?? null) == 'DIPROSES')>DIPROSES</option>
                        <option @selected($app->request->old('langgar_status', $langgar->langgar_status ?? null) == 'DIBATALKAN')>DIBATALKAN</option>
                    </select>
                    <span class="t-bantu">Pilih satu</span>
                </div>
            @endif

            <div class="isian gspan-4">
                <label for="sdm_langgar_tambahUbahIsiLaporan">Dugaan Pelanggaran</label>
                <textarea id="sdm_langgar_tambahUbahIsiLaporan" name="langgar_isi" rows="3" required>
                    {{ $app->request->old('langgar_isi', $langgar->langgar_isi ?? null) }}
                </textarea>
                <span class="t-bantu">Isi aduan pelanggaran</span>
            </div>

            <div class="isian gspan-4">
                <label for="sdm_langgar_tambahUbahKeterangan">Keterangan</label>
                <textarea id="sdm_langgar_tambahUbahKeterangan" name="langgar_keterangan" rows="3">
                    {{ $app->request->old('langgar_keterangan', $langgar->langgar_keterangan ?? null) }}
                </textarea>
                <span class="t-bantu">Isi keterangan lain yang diperlukan</span>
            </div>

            <div class="isian gspan-2">
                <label for="sdm_langgar_tambahUbahUnggahBerkas">Unggah/Perbarui Bukti Pelanggaran</label>
                <input id="sdm_langgar_tambahUbahUnggahBerkas" name="berkas_laporan" type="file" accept=".pdf,application/pdf">
                <span class="t-bantu">
                    Scan PDF bukti pendukung pelanggaran
                    {{ $app->filesystem->exists('sdm/pelanggaran/berkas/' . $app->request->old('sdm_no_absen', $langgar->langgar_lap_no ?? null) . '.pdf')
                        ? '(berkas yang diunggah akan menindih berkas unggahan lama).'
                        : '' }}
                </span>
            </div>

            <div class="gspan-4"></div>

            <button class="utama pelengkap" type="submit">SIMPAN</button>
        </form>

        <script>
            (async () => {
                while (!window.aplikasiSiap) {
                    await new Promise((resolve, reject) =>
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
