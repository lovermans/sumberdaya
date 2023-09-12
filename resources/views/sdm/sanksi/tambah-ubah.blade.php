@extends('rangka')

@section('isi')
    <div id="sdm_sanksi_tambahUbah">
        <form class="form-xhr kartu" id="form_sdm_sanksi_tambahUbah" method="POST" action="{{ $app->url->current() }}">
            <input name="_token" type="hidden" value="{{ $app->request->session()->token() }}">

            <div class="judul-form gspan-4">
                <h4 class="form">{{ $app->request->routeIs('sdm.sanksi.tambah') ? 'Tambah' : 'Ubah' }} Sanksi SDM</h4>

                <a class="tutup-i">
                    <svg viewbox="0 0 24 24">
                        <use href="#ikontutup"></use>
                    </svg>
                </a>
            </div>

            @if ($app->request->routeIs('sdm.sanksi.ubah'))
                @if (count($lapPelanggaran))
                    <div class="isian gspan-4">
                        <label for="sanksi_lap_no">Nomor Laporan</label>

                        <select class="pil-cari" id="sanksi_lap_no" name="sanksi_lap_no" required>
                            @foreach ($lapPelanggaran as $lapPel)
                                <option value="{{ $lapPel->langgar_lap_no }}" @selected($lapPel->langgar_lap_no == $app->request->old('sanksi_lap_no', $sanksiLama->sanksi_lap_no ?? null))>
                                    {{ $lapPel->langgar_lap_no }} - {{ $lapPel->langgar_tanggal }} - {{ $lapPel->langgar_tsdm_nama }}
                                    {{ $lapPel->langgar_isi }} oleh {{ $lapPel->langgar_psdm_nama }}
                                </option>
                            @endforeach
                        </select>

                        <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
                    </div>
                @endif
            @endif

            <div class="isian normal">
                <label for="sanksi_jenis">Jenis Sanksi</label>

                <select class="pil-cari" id="sanksi_jenis" name="sanksi_jenis" required>
                    @if (
                        !in_array($app->request->old('sanksi_jenis', $sanksiLama->sanksi_jenis ?? null), (array) $sanksis->pluck('atur_butir')->toArray()) &&
                            !is_null($app->request->old('sanksi_jenis', $sanksiLama->sanksi_jenis ?? null)))
                        <option class="merah" value="{{ $app->request->old('sanksi_jenis', $sanksiLama->sanksi_jenis ?? null) }}" selected>
                            {{ $app->request->old('sanksi_jenis', $sanksiLama->sanksi_jenis ?? null) }}
                        </option>
                    @endif

                    @foreach ($sanksis as $sanksi)
                        <option @selected($sanksi->atur_butir == $app->request->old('sanksi_jenis', $sanksiLama->sanksi_jenis ?? null)) @class(['merah' => $sanksi->atur_status == 'NON-AKTIF'])>
                            {{ $sanksi->atur_butir }}
                        </option>
                    @endforeach
                </select>

                <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
            </div>

            <div class="isian pendek">
                <label for="sanksi_mulai">Sanksi Mulai</label>

                <input id="sanksi_mulai" name="sanksi_mulai" type="date"
                    value="{{ $app->request->old('sanksi_mulai', $sanksiLama->sanksi_mulai ?? $app->date->today()->toDateString()) }}" required>

                <span class="t-bantu">Isi tanggal</span>
            </div>

            <div class="isian pendek">
                <label for="sanksi_selesai">Sanksi Selesai</label>

                <input id="sanksi_selesai" name="sanksi_selesai" type="date"
                    value="{{ $app->request->old('sanksi_selesai',$sanksiLama->sanksi_selesai ??$app->date->today()->addMonths(6)->toDateString()) }}" required>
                <span class="t-bantu">Isi tanggal</span>
            </div>

            <div class="isian gspan-4">
                <label for="sanksi_tambahan">Sanksi Tambahan</label>

                <textarea id="sanksi_tambahan" name="sanksi_tambahan" rows="3">{{ $app->request->old('sanksi_tambahan', $sanksiLama->sanksi_tambahan ?? null) }}</textarea>

                <span class="t-bantu">Sanksi tambahan dapat berupa denda, demosi/rotasi/mutasi</span>
            </div>

            <div class="isian gspan-4">
                <label for="sanksi_keterangan">Keterangan Sanksi</label>

                <textarea id="sanksi_keterangan" name="sanksi_keterangan" rows="3">{{ $app->request->old('sanksi_keterangan', $sanksiLama->sanksi_keterangan ?? null) }}</textarea>

                <span class="t-bantu">Keterangan lain terkait informasi sanksi</span>
            </div>

            <div class="isian normal">
                <label for="sanksi_berkas">Unggah Dokumen Sanksi</label>

                <input id="sanksi_berkas" name="sanksi_berkas" type="file" accept=".pdf,application/pdf">

                <span class="t-bantu">
                    Scan PDF laporan pelanggaran, sanksi, pernyataan & lampiran lainnya
                    {{ $app->filesystem->exists(
                        'sdm/sanksi/berkas/' .
                            $app->request->old('sanksi_no_absen', $sanksiLama->sanksi_no_absen ?? null) .
                            ' - ' .
                            $app->request->old('sanksi_jenis', $sanksiLama->sanksi_jenis ?? null) .
                            ' - ' .
                            $app->request->old('sanksi_mulai', $sanksiLama->sanksi_mulai ?? null) .
                            '.pdf',
                    )
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

                pilCari('#form_sdm_sanksi_tambahUbah .pil-cari');
                formatIsian('#form_sdm_sanksi_tambahUbah .isian :is(textarea,input[type=text],input[type=search])');
            })();
        </script>

        @include('pemberitahuan')
        @include('komponen')
    </div>
@endsection
