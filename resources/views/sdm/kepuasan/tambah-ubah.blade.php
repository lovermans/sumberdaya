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

            <fieldset class="gspan-4">
                <legend>
                    <i><u>Survey 5</u></i> : Saya bekerja dengan peralatan/perlengkapan/atribut kerja yang aman dan memadai.
                </legend>

                <div class="isian normal">
                    <label for="surveysdm_5">Jawaban</label>

                    <select class="pil-saja" id="surveysdm_5" name="surveysdm_5" required>
                        <option selected disabled></option>

                        @foreach ($jawabans as $jawaban)
                            <option value="{{ $jawaban['value'] }}" @selected($jawaban['value'] == $app->request->old('surveysdm_5', $kepuasan->surveysdm_5 ?? null))>{{ $jawaban['text'] }}</option>
                        @endforeach
                    </select>

                    <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
                </div>
            </fieldset>

            <fieldset class="gspan-4">
                <legend>
                    <i><u>Survey 6</u></i> : Perlindungan untuk saya bekerja memadai (BPJS Kesehatan & Ketenagakerjaan).
                </legend>

                <div class="isian normal">
                    <label for="surveysdm_6">Jawaban</label>

                    <select class="pil-saja" id="surveysdm_6" name="surveysdm_6" required>
                        <option selected disabled></option>

                        @foreach ($jawabans as $jawaban)
                            <option value="{{ $jawaban['value'] }}" @selected($jawaban['value'] == $app->request->old('surveysdm_6', $kepuasan->surveysdm_6 ?? null))>{{ $jawaban['text'] }}</option>
                        @endforeach
                    </select>

                    <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
                </div>
            </fieldset>

            <fieldset class="gspan-4">
                <legend>
                    <i><u>Survey 7</u></i> : Lingkungan kerja saya bebas dari intimidasi dan diskriminasi.
                </legend>

                <div class="isian normal">
                    <label for="surveysdm_7">Jawaban</label>

                    <select class="pil-saja" id="surveysdm_7" name="surveysdm_7" required>
                        <option selected disabled></option>

                        @foreach ($jawabans as $jawaban)
                            <option value="{{ $jawaban['value'] }}" @selected($jawaban['value'] == $app->request->old('surveysdm_7', $kepuasan->surveysdm_7 ?? null))>{{ $jawaban['text'] }}</option>
                        @endforeach
                    </select>

                    <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
                </div>
            </fieldset>

            <fieldset class="gspan-4">
                <legend>
                    <i><u>Survey 8</u></i> : Lingkungan kerja saya aman dan sehat (semua resiko bahaya telah diidentifikasi dan dikendalikan).
                </legend>

                <div class="isian normal">
                    <label for="surveysdm_8">Jawaban</label>

                    <select class="pil-saja" id="surveysdm_8" name="surveysdm_8" required>
                        <option selected disabled></option>

                        @foreach ($jawabans as $jawaban)
                            <option value="{{ $jawaban['value'] }}" @selected($jawaban['value'] == $app->request->old('surveysdm_8', $kepuasan->surveysdm_8 ?? null))>{{ $jawaban['text'] }}</option>
                        @endforeach
                    </select>

                    <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
                </div>
            </fieldset>

            <fieldset class="gspan-4">
                <legend>
                    <i><u>Survey 9</u></i> : Fasilitas umum di tempat kerja memadai dan terawat dengan baik.
                </legend>

                <div class="isian normal">
                    <label for="surveysdm_9">Jawaban</label>

                    <select class="pil-saja" id="surveysdm_9" name="surveysdm_9" required>
                        <option selected disabled></option>

                        @foreach ($jawabans as $jawaban)
                            <option value="{{ $jawaban['value'] }}" @selected($jawaban['value'] == $app->request->old('surveysdm_9', $kepuasan->surveysdm_9 ?? null))>{{ $jawaban['text'] }}</option>
                        @endforeach
                    </select>

                    <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
                </div>
            </fieldset>

            <fieldset class="gspan-4">
                <legend>
                    <i><u>Survey 10</u></i> : Saya puas dengan kontribusi saya terhadap kinerja Perusahaan.
                </legend>

                <div class="isian normal">
                    <label for="surveysdm_10">Jawaban</label>

                    <select class="pil-saja" id="surveysdm_10" name="surveysdm_10" required>
                        <option selected disabled></option>

                        @foreach ($jawabans as $jawaban)
                            <option value="{{ $jawaban['value'] }}" @selected($jawaban['value'] == $app->request->old('surveysdm_10', $kepuasan->surveysdm_10 ?? null))>{{ $jawaban['text'] }}</option>
                        @endforeach
                    </select>

                    <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
                </div>
            </fieldset>

            <div class="isian gspan-4">
                <label for="surveysdm_saran">Saran</label>

                <textarea id="surveysdm_saran" name="surveysdm_saran" rows="3">{{ $app->request->old('surveysdm_saran', $kepuasan->surveysdm_saran ?? null) }}</textarea>

                <span class="t-bantu">Isikan saran/kritik untuk peningkatan kinerja Perusahaan</span>
            </div>

            <div class="isian gspan-4">
                <label for="surveysdm_keterangan">Keterangan</label>

                <textarea id="surveysdm_keterangan" name="surveysdm_keterangan" rows="3">{{ $app->request->old('surveysdm_keterangan', $kepuasan->surveysdm_keterangan ?? null) }}</textarea>

                <span class="t-bantu">Keterangan lain terkait informasi survey</span>
            </div>

            <div class="isian normal">
                <label for="surveysdm_berkas">Unggah Dokumen Survey</label>

                <input id="surveysdm_berkas" name="surveysdm_berkas" type="file" accept=".pdf,application/pdf">

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
