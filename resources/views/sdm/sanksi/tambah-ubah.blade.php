@extends('rangka')

@section('isi')
<div id="sdm_sanksi_tambahUbah">
    <form id="form_sdm_sanksi_tambahUbah" class="form-xhr kartu" method="POST" action="{{ $app->url->current() }}">
        <input type="hidden" name="_token" value="{{ $app->request->session()->token() }}">

        <div class="judul-form gspan-4">
            <h4 class="form">{{$app->request->routeIs('sdm.sanksi.tambah') ? 'Tambah' : 'Ubah'}} Sanksi SDM</h4>

            <a class="tutup-i">
                <svg viewbox="0 0 24 24">
                    <use href="#ikontutup"></use>
                </svg>
            </a>
        </div>

        @if (count($lapPelanggaran))
        <div class="isian gspan-4">
            <label for="sanksi_lap_no">Nomor Laporan</label>

            <select id="sanksi_lap_no" name="sanksi_lap_no" class="pil-cari" required>
                @foreach ($lapPelanggaran as $lapPel)
                <option @selected($lapPel->langgar_lap_no == $app->request->old('sanksi_lap_no',
                    $sanksiLama->sanksi_lap_no ?? null)) value="{{ $lapPel->langgar_lap_no }}">
                    {{ $lapPel->langgar_lap_no }} - {{ $lapPel->langgar_tanggal }} - {{ $lapPel->langgar_tsdm_nama }}
                    {{$lapPel->langgar_isi }} oleh {{ $lapPel->langgar_psdm_nama }}
                </option>
                @endforeach
            </select>

            <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
        </div>
        @endif

        <div class="isian normal">
            <label for="sanksi_jenis">Jenis Sanksi</label>

            <select id="sanksi_jenis" name="sanksi_jenis" class="pil-cari" required>
                @if (!in_array($app->request->old('sanksi_jenis', $sanksiLama->sanksi_jenis ?? null), (array)
                $sanksis->pluck('atur_butir')->toArray()) && !is_null($app->request->old('sanksi_jenis',
                $sanksiLama->sanksi_jenis ?? null)))
                <option value="{{ $app->request->old('sanksi_jenis', $sanksiLama->sanksi_jenis ?? null) }}"
                    class="merah" selected>{{ $app->request->old('sanksi_jenis', $sanksiLama->sanksi_jenis ?? null)
                    }}</option>
                @endif

                @foreach ($sanksis as $sanksi)
                <option @selected($sanksi->atur_butir == $app->request->old('sanksi_jenis',
                    $sanksiLama->sanksi_jenis ?? null)) @class(['merah' => $sanksi->atur_status == 'NON-AKTIF'])>{{
                    $sanksi->atur_butir }}</option>
                @endforeach
            </select>

            <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
        </div>

        <div class="isian pendek">
            <label for="sanksi_mulai">Sanksi Mulai</label>

            <input id="sanksi_mulai" type="date" name="sanksi_mulai"
                value="{{ $app->request->old('sanksi_mulai', $sanksiLama->sanksi_mulai ?? $app->date->today()->toDateString()) }}"
                required>

            <span class="t-bantu">Isi tanggal</span>
        </div>

        <div class="isian pendek">
            <label for="sanksi_selesai">Sanksi Selesai</label>

            <input id="sanksi_selesai" type="date" name="sanksi_selesai"
                value="{{ $app->request->old('sanksi_selesai', $sanksiLama->sanksi_selesai ?? $app->date->today()->addMonths(6)->toDateString()) }}"
                required>
            <span class="t-bantu">Isi tanggal</span>
        </div>

        <div class="isian gspan-4">
            <label for="sanksi_tambahan">Sanksi Tambahan</label>

            <textarea id="sanksi_tambahan" name="sanksi_tambahan"
                rows="3">{{ $app->request->old('sanksi_tambahan', $sanksiLama->sanksi_tambahan ?? null) }}</textarea>

            <span class="t-bantu">Sanksi tambahan dapat berupa denda, demosi/rotasi/mutasi</span>
        </div>

        <div class="isian gspan-4">
            <label for="sanksi_keterangan">Keterangan Sanksi</label>

            <textarea id="sanksi_keterangan" name="sanksi_keterangan"
                rows="3">{{ $app->request->old('sanksi_keterangan', $sanksiLama->sanksi_keterangan ?? null) }}</textarea>

            <span class="t-bantu">Keterangan lain terkait informasi sanksi</span>
        </div>

        <div class="isian normal">
            <label for="sanksi_berkas">Unggah Dokumen Sanksi</label>

            <input id="sanksi_berkas" type="file" name="sanksi_berkas" accept=".pdf,application/pdf">

            <span class="t-bantu">Scan PDF laporan pelanggaran, sanksi, pernyataan & lampiran lainnya {{
                $app->filesystem->exists('sdm/sanksi/berkas/'. $app->request->old('sanksi_no_absen',
                $sanksiLama->sanksi_no_absen ??
                null) . ' - ' . $app->request->old('sanksi_jenis', $sanksiLama->sanksi_jenis ?? null) . ' - ' .
                $app->request->old('sanksi_mulai', $sanksiLama->sanksi_mulai ?? null) . '.pdf') ? '(berkas yang diunggah
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

            pilCari('#form_sdm_sanksi_tambahUbah .pil-cari');
            formatIsian('#form_sdm_sanksi_tambahUbah .isian :is(textarea,input[type=text],input[type=search])');
        })();
    </script>

    @include('pemberitahuan')
    @include('komponen')
</div>
@endsection