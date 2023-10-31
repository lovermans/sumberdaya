@extends('rangka')

@section('isi')
    <div id="sdm_kepuasan_tambahUbah">
        <form class="form-xhr kartu" id="form_sdm_kepuasan_tambahUbah" method="POST" action="{{ $app->url->current() }}">
            <input name="_token" type="hidden" value="{{ $app->request->session()->token() }}">

            <div class="judul-form gspan-4">
                <h4 class="form">{{ $app->request->routeIs('sdm.kepuasan.tambah') ? 'Tambah' : 'Ubah' }} Data Kepuasan SDM</h4>

                <a class="tutup-i">
                    <svg viewbox="0 0 24 24">
                        <use href="#ikontutup"></use>
                    </svg>
                </a>
            </div>

            @if ($app->request->routeIs('sdm.kepuasan.tambah'))
                <div class="isian panjang">
                    <label for="surveysdm_no_absen">Identitas SDM</label>

                    <select class="pil-cari" id="surveysdm_no_absen" name="surveysdm_no_absen" required>
                        <option selected disabled></option>

                        @foreach ($sdms as $sdm)
                            <option value={{ $sdm->sdm_no_absen }} @selected($sdm->sdm_no_absen == $app->request->old('surveysdm_no_absen', $kepuasan->surveysdm_no_absen ?? null))>
                                {{ $sdm->sdm_no_absen . ' - ' . $sdm->sdm_nama . ' - ' . $sdm->penempatan_lokasi . ' - ' . $sdm->penempatan_kontrak . ' - ' . $sdm->penempatan_posisi }}
                            </option>
                        @endforeach
                    </select>

                    <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
                </div>
            @endif

            <div class="isian normal">
                <label for="surveysdm_tahun">Tahun Survey</label>

                <select class="pil-cari" id="surveysdm_tahun" name="surveysdm_tahun" required>
                    <option selected disabled></option>

                    @foreach (range(2020, date('Y')) as $tahun)
                        <option @selected($tahun == $app->request->old('surveysdm_tahun', $kepuasan->surveysdm_tahun ?? null))>{{ $tahun }}</option>
                    @endforeach
                </select>

                <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
            </div>

            <fieldset class="gspan-4">
                <legend>
                    <i><u>Survey 1</u></i> : Prosedur yang mengatur prose-proses kerja antar Bagian/Departemen telah tersedia cukup jelas dan memadai.
                </legend>

                <div class="isian normal">
                    <label for="surveysdm_1">Jawaban</label>

                    <select class="pil-saja" id="surveysdm_1" name="surveysdm_1" required>
                        <option selected disabled></option>

                        @foreach ($jawabans as $jawaban)
                            <option value="{{ $jawaban['value'] }}" @selected($jawaban['value'] == $app->request->old('surveysdm_1', $kepuasan->surveysdm_1 ?? null))>{{ $jawaban['text'] }}</option>
                        @endforeach
                    </select>

                    <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
                </div>
            </fieldset>

            <fieldset class="gspan-4">
                <legend>
                    <i><u>Survey 2</u></i> : Instruksi kerja yang berhubungan dengan posisi/jabatan pekerjaan saya cukup jelas dan memadai.
                </legend>

                <div class="isian normal">
                    <label for="surveysdm_2">Jawaban</label>

                    <select class="pil-saja" id="surveysdm_2" name="surveysdm_2" required>
                        <option selected disabled></option>

                        @foreach ($jawabans as $jawaban)
                            <option value="{{ $jawaban['value'] }}" @selected($jawaban['value'] == $app->request->old('surveysdm_2', $kepuasan->surveysdm_2 ?? null))>{{ $jawaban['text'] }}</option>
                        @endforeach
                    </select>

                    <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
                </div>
            </fieldset>

            <fieldset class="gspan-4">
                <legend>
                    <i><u>Survey 3</u></i> : Jobdesc/beban kerja + jadwal kerja saya cukup jelas dan telah sesuai dengan posisi/jabatan saya.
                </legend>

                <div class="isian normal">
                    <label for="surveysdm_3">Jawaban</label>

                    <select class="pil-saja" id="surveysdm_3" name="surveysdm_3" required>
                        <option selected disabled></option>

                        @foreach ($jawabans as $jawaban)
                            <option value="{{ $jawaban['value'] }}" @selected($jawaban['value'] == $app->request->old('surveysdm_3', $kepuasan->surveysdm_3 ?? null))>{{ $jawaban['text'] }}</option>
                        @endforeach
                    </select>

                    <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
                </div>
            </fieldset>

            <fieldset class="gspan-4">
                <legend>
                    <i><u>Survey 4</u></i> : Pelatihan, bimbingan, petunjuk dan instruksi dari atasan cukup jelas dan dapat diikuti.
                </legend>

                <div class="isian normal">
                    <label for="surveysdm_4">Jawaban</label>

                    <select class="pil-saja" id="surveysdm_4" name="surveysdm_4" required>
                        <option selected disabled></option>

                        @foreach ($jawabans as $jawaban)
                            <option value="{{ $jawaban['value'] }}" @selected($jawaban['value'] == $app->request->old('surveysdm_4', $kepuasan->surveysdm_4 ?? null))>{{ $jawaban['text'] }}</option>
                        @endforeach
                    </select>

                    <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
                </div>
            </fieldset>

            <div class="isian gspan-4">
                <label for="surveysdm_keterangan">Keterangan</label>

                <textarea id="surveysdm_keterangan" name="surveysdm_keterangan" rows="3">{{ $app->request->old('surveysdm_keterangan', $kepuasan->surveysdm_keterangan ?? null) }}</textarea>

                <span class="t-bantu">Keterangan lain terkait informasi survey</span>
            </div>

            <div class="isian normal">
                <label for="kepuasan_berkas">Unggah Dokumen Survey</label>

                <input id="kepuasan_berkas" name="kepuasan_berkas" type="file" accept=".pdf,application/pdf">

                <span class="t-bantu">Scan PDF formulir survey kepuasan SDM
                    {{ $app->filesystem->exists(
                        'sdm/kepuasan/berkas/' .
                            $app->request->old('surveysdm_no_absen', $kepuasan->surveysdm_no_absen ?? null) .
                            ' - ' .
                            $app->request->old('surveysdm_tahun', $kepuasan->surveysdm_tahun ?? null) .
                            ' - ',
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

                pilCari('#form_sdm_kepuasan_tambahUbah .pil-cari');
                pilSaja('#form_sdm_kepuasan_tambahUbah .pil-saja');
                formatIsian('#form_sdm_kepuasan_tambahUbah .isian :is(textarea,input[type=text],input[type=search])');
            })();
        </script>

        @include('pemberitahuan')
        @include('komponen')
    </div>
@endsection
