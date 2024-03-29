@extends('rangka')

@section('isi')
    <div id="sdm_nilai_tambahUbah">
        <form class="form-xhr kartu" id="form_sdm_nilai_tambahUbah" method="POST" action="{{ $app->url->current() }}">
            <input name="_token" type="hidden" value="{{ $app->request->session()->token() }}">

            <div class="judul-form gspan-4">
                <h4 class="form">{{ $app->request->routeIs('sdm.penilaian.tambah') ? 'Tambah' : 'Ubah' }} Penilaian SDM</h4>

                <a class="tutup-i">
                    <svg viewbox="0 0 24 24">
                        <use href="#ikontutup"></use>
                    </svg>
                </a>
            </div>

            @if ($app->request->routeIs('sdm.penilaian.tambah'))
                <div class="isian panjang">
                    <label for="nilaisdm_no_absen">Identitas SDM</label>

                    <select class="pil-cari" id="nilaisdm_no_absen" name="nilaisdm_no_absen" required>
                        <option selected disabled></option>

                        @foreach ($sdms as $sdm)
                            <option value={{ $sdm->sdm_no_absen }} @selected($sdm->sdm_no_absen == $app->request->old('nilaisdm_no_absen', $nilai->nilaisdm_no_absen ?? null))>
                                {{ $sdm->sdm_no_absen . ' - ' . $sdm->sdm_nama . ' - ' . $sdm->penempatan_lokasi . ' - ' . $sdm->penempatan_kontrak . ' - ' . $sdm->penempatan_posisi }}
                            </option>
                        @endforeach
                    </select>

                    <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
                </div>
            @endif

            <div class="isian normal">
                <label for="nilaisdm_tahun">Tahun Penilaian</label>

                <select class="pil-cari" id="nilaisdm_tahun" name="nilaisdm_tahun" required>
                    <option selected disabled></option>

                    @foreach (range(2020, date('Y')) as $tahun)
                        <option @selected($tahun == $app->request->old('nilaisdm_tahun', $nilai->nilaisdm_tahun ?? null))>{{ $tahun }}</option>
                    @endforeach
                </select>

                <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
            </div>

            <div class="isian normal">
                <label for="nilaisdm_periode">Periode Penilaian</label>

                <select class="pil-cari" id="nilaisdm_periode" name="nilaisdm_periode" required>
                    <option selected disabled></option>

                    <option @selected($app->request->old('nilaisdm_periode', $nilai->nilaisdm_periode ?? null) == 'SEMESTER-I')>SEMESTER-I</option>
                    <option @selected($app->request->old('nilaisdm_periode', $nilai->nilaisdm_periode ?? null) == 'SEMESTER-II')>SEMESTER-II</option>
                </select>

                <span class="t-bantu">Isi tanggal</span>
            </div>

            <div class="isian pendek">
                <label for="nilaisdm_bobot_hadir">Nilai Bobot Kehadiran</label>

                <input id="nilaisdm_bobot_hadir" name="nilaisdm_bobot_hadir" type="number"
                    value="{{ $app->request->old('nilaisdm_bobot_hadir', $nilai->nilaisdm_bobot_hadir ?? null) }}" step="0.0001" max="30">

                <span class="t-bantu">Nilai Maksimal = 30</span>
            </div>

            <div class="isian pendek">
                <label for="nilaisdm_bobot_sikap">Nilai Bobot Sikap Kerja</label>

                <input id="nilaisdm_bobot_sikap" name="nilaisdm_bobot_sikap" type="number"
                    value="{{ $app->request->old('nilaisdm_bobot_sikap', $nilai->nilaisdm_bobot_sikap ?? null) }}" step="0.0001" max="30">

                <span class="t-bantu">Nilai Maksimal = 30</span>
            </div>

            <div class="isian pendek">
                <label for="nilaisdm_bobot_target">Nilai Bobot Target Kerja</label>

                <input id="nilaisdm_bobot_target" name="nilaisdm_bobot_target" type="number"
                    value="{{ $app->request->old('nilaisdm_bobot_target', $nilai->nilaisdm_bobot_target ?? null) }}" step="0.0001" max="40">

                <span class="t-bantu">Nilai Maksimal = 40</span>
            </div>

            <div class="isian gspan-4">
                <label for="nilaisdm_tindak_lanjut">Tindak Lanjut Penilaian</label>

                <textarea id="nilaisdm_tindak_lanjut" name="nilaisdm_tindak_lanjut" rows="3">
                    {{ $app->request->old('nilaisdm_tindak_lanjut', $nilai->nilaisdm_tindak_lanjut ?? null) }}
                </textarea>

                <span class="t-bantu">
                    Dapat berupa promosi/rotasi/mutasi/demosi, sanksi, pelatihan, pemutusan kontrak, dsj
                </span>
            </div>

            <div class="isian gspan-4">
                <label for="nilaisdm_keterangan">Keterangan Penilaian</label>

                <textarea id="nilaisdm_keterangan" name="nilaisdm_keterangan" rows="3">{{ $app->request->old('nilaisdm_keterangan', $nilai->nilaisdm_keterangan ?? null) }}</textarea>

                <span class="t-bantu">Keterangan lain terkait informasi penilaian</span>
            </div>

            <div class="isian normal">
                <label for="nilai_berkas">Unggah Dokumen Penilaian</label>

                <input id="nilai_berkas" name="nilai_berkas" type="file" accept=".pdf,application/pdf">

                <span class="t-bantu">Scan PDF formulir penilaian
                    {{ $app->filesystem->exists(
                        'sdm/penilaian/berkas/' .
                            $app->request->old('nilaisdm_no_absen', $nilai->nilaisdm_no_absen ?? null) .
                            ' - ' .
                            $app->request->old('nilaisdm_tahun', $nilai->nilaisdm_tahun ?? null) .
                            ' - ' .
                            $app->request->old('nilaisdm_periode', $nilai->nilaisdm_periode ?? null) .
                            '.pdf',
                    )
                        ? '(berkas yang diunggah akan menindih berkas unggahan lama).'
                        : '' }}</span>
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

                pilCari('#form_sdm_nilai_tambahUbah .pil-cari');
                formatIsian('#form_sdm_nilai_tambahUbah .isian :is(textarea,input[type=text],input[type=search])');
            })();
        </script>

        @include('pemberitahuan')
        @include('komponen')
    </div>
@endsection
