@extends('rangka')

@section('isi')
    <div id="kepuasan_sdm_lihat">
        <div class="kartu form">
            @isset($kepuasan)
                <div class="judul-form gspan-4">
                    <h4 class="form">Data Kepuasan SDM</h4>

                    <a class="tutup-i">
                        <svg viewbox="0 0 24 24">
                            <use href="#ikontutup"></use>
                        </svg>
                    </a>
                </div>

                <div class="isian">
                    <h3>Identitas</h3>
                    <p>
                        {{ $kepuasan->surveysdm_no_absen }} - {{ $kepuasan->sdm_nama }} - {{ $kepuasan->penempatan_lokasi }} - {{ $kepuasan->penempatan_kontrak }} -
                        {{ $kepuasan->penempatan_posisi }}
                    </p>
                </div>

                <div class="isian">
                    <h3>Tahun Penilaian</h3>
                    <p>{{ $kepuasan->surveysdm_tahun }}</p>
                </div>

                <div class="gspan-4">
                    <p class="biru">
                        <small>
                            Keterangan skor : 1 = Tidak Puas, 2 = Kurang Puas, 3 = Ragu, 4 = Puas, 5 = Sangat Puas.
                        </small>
                    </p>
                    <ol>
                        <li>
                            Prosedur Kerja : {{ $kepuasan->surveysdm_1 }}.
                        </li>
                        <li>
                            Instruksi Kerja : {{ $kepuasan->surveysdm_2 }}.
                        </li>
                        <li>
                            <i>Jobdesc</i> & Beban Kerja : {{ $kepuasan->surveysdm_3 }}.
                        </li>
                        <li>
                            Arahan Pimpinan : {{ $kepuasan->surveysdm_4 }}.
                        </li>
                        <li>
                            Peralatan/Perlengkapan/Atribut Kerja : {{ $kepuasan->surveysdm_5 }}.
                        </li>
                        <li>
                            Perlindungan Asuransi : {{ $kepuasan->surveysdm_6 }}.
                        </li>
                        <li>
                            Keamanan Bekerja : {{ $kepuasan->surveysdm_7 }}.
                        </li>
                        <li>
                            Keselamatan Bekerja : {{ $kepuasan->surveysdm_8 }}.
                        </li>
                        <li>
                            Fasilitas Umum : {{ $kepuasan->surveysdm_9 }}.
                        </li>
                        <li>
                            Kontribusi Kinerja : {{ $kepuasan->surveysdm_10 }}.
                        </li>
                    </ol>
                </div>

                <div class="isian">
                    <h3>Total Skor</h3>
                    <p>Tingkat Kepuasan = {{ $kepuasan->surveysdm_skor }} ({{ $kepuasan->surveysdm_klasifikasi }})</p>
                </div>

                <div class="isian gspan-4">
                    <h3>Saran</h3>
                    <p>{!! nl2br($kepuasan->surveysdm_saran) !!}</p>
                </div>

                <div class="isian gspan-4">
                    <h3>Keterangan</h3>
                    <p>{!! nl2br($kepuasan->surveysdm_keterangan) !!}</p>
                </div>

                <div class="isian gspan-4">
                    <h3>Berkas Survey</h3>

                    @if ($app->filesystem->exists($berkasKepuasan = 'sdm/kepuasan/berkas/' . $kepuasan->surveysdm_no_absen . ' - ' . $kepuasan->surveysdm_tahun . '.pdf'))
                        <iframe class="berkas tcetak"
                            src="{{ $app->url->route('sdm.berkas', ['berkas' => $berkasKepuasan . '?id=' . filemtime(storage_path('app/' . $berkasKepuasan))]) }}"
                            title="Bukti Pendukung Kepuasan SDM" loading="lazy" onload="if (this.contentDocument.body.id == 'badan-dokumen') this.remove()"></iframe>

                        <a class="sekunder tcetak"
                            href="{{ $app->url->route('sdm.berkas', ['berkas' => $berkasKepuasan . '?id=' . filemtime(storage_path('app/' . $berkasKepuasan))]) }}"
                            title="Unduh Berkas Terunggah" target="_blank">
                            <svg viewBox="0 0 24 24">
                                <use href="#ikonunduh"></use>
                            </svg>
                            BERKAS
                        </a>
                    @else
                        <p class="merah">Tidak ada bukti pendukung terunggah.</p>
                    @endif
                </div>

                <a class="utama isi-xhr" data-rekam="false" data-tujuan="#kepuasan_sdm_lihat_sematan"
                    href="{{ $app->url->route('sdm.kepuasan.ubah', ['uuid' => $kepuasan->surveysdm_uuid]) }}">
                    UBAH SURVEY
                </a>
            @else
                <div class="isian gspan-4">
                    <p>Periksa kembali data yang diminta.</p>
                </div>
            @endisset
        </div>

        <div class="scroll-margin" id="kepuasan_sdm_lihat_sematan"></div>

        @include('pemberitahuan')
        @include('komponen')
    </div>
@endsection
