<details class="kartu">
    <summary>
        Tahun {{ $app->date->today()->subYear()->format('Y') }} =
        {{ $pesertaTahunLalu > 0 ? number_format(($puasTahunLalu / $pesertaTahunLalu) * 100, 2, ',', '.') : 0 }}%
        responden merasa puas | Tahun {{ $app->date->today()->format('Y') }} =
        {{ $pesertaTahunIni > 0 ? number_format(($puasTahunIni / $pesertaTahunIni) * 100, 2, ',', '.') : 0 }}%
        responden merasa puas.
        {{-- Rata-rata Penilaian Berkala SDM : Tahun {{ $app->date->today()->subYear()->format('Y') }} =
        {{ number_format($rataTahunLalu, 2, ',', '.') }} | Tahun {{ $app->date->today()->format('Y') }} =
        {{ number_format($rataTahunIni, 2, ',', '.') }} --}}
    </summary>

    <div class="kartu">
        <i>Keterangan skor : 1 = Tidak Puas, 2 = Kurang Puas, 3 = Ragu, 4 = Puas, 5 = Sangat Puas.</i>
        <div class="data">
            <table class="tabel">
                <thead>
                    <tr>
                        <th>Rata-rata {{ $app->date->today()->subYear()->format('Y') }}</th>
                        <th>Rata-rata {{ $app->date->today()->format('Y') }}</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>
                            <ol>
                                <li>
                                    Prosedur Kerja : {{ $dataTahunLalu->avg('surveysdm_1') ?? 0 }}
                                </li>
                                <li>
                                    Instruksi Kerja : {{ $dataTahunLalu->avg('surveysdm_2') ?? 0 }}
                                </li>
                                <li>
                                    <i>Jobdesc</i> & Beban Kerja : {{ $dataTahunLalu->avg('surveysdm_3') ?? 0 }}
                                </li>
                                <li>
                                    Arahan Pimpinan : {{ $dataTahunLalu->avg('surveysdm_4') ?? 0 }}
                                </li>
                                <li>
                                    Peralatan/Perlengkapan/Atribut Kerja : {{ $dataTahunLalu->avg('surveysdm_5') ?? 0 }}
                                </li>
                                <li>
                                    Perlindungan Asuransi : {{ $dataTahunLalu->avg('surveysdm_6') ?? 0 }}
                                </li>
                                <li>
                                    Keamanan Bekerja : {{ $dataTahunLalu->avg('surveysdm_7') ?? 0 }}
                                </li>
                                <li>
                                    Keselamatan Bekerja : {{ $dataTahunLalu->avg('surveysdm_8') ?? 0 }}
                                </li>
                                <li>
                                    Fasilitas Umum : {{ $dataTahunLalu->avg('surveysdm_9') ?? 0 }}
                                </li>
                                <li>
                                    Kontribusi Kinerja : {{ $dataTahunLalu->avg('surveysdm_10') ?? 0 }}
                                </li>
                            </ol>

                            <b><u><i>Skor rata-rata total : {{ $dataTahunLalu->avg('surveysdm_skor') ?? 0 }}</i></u></b><br>
                            Jumlah responden : {{ number_format($pesertaTahunLalu, 0, ',', '.') }} SDM.
                        </td>
                        <td>
                            <ol>
                                <li>
                                    Prosedur Kerja : {{ $dataTahunIni->avg('surveysdm_1') ?? 0 }}
                                </li>
                                <li>
                                    Instruksi Kerja : {{ $dataTahunIni->avg('surveysdm_2') ?? 0 }}
                                </li>
                                <li>
                                    <i>Jobdesc</i> & Beban Kerja : {{ $dataTahunIni->avg('surveysdm_3') ?? 0 }}
                                </li>
                                <li>
                                    Arahan Pimpinan : {{ $dataTahunIni->avg('surveysdm_4') ?? 0 }}
                                </li>
                                <li>
                                    Peralatan/Perlengkapan/Atribut Kerja : {{ $dataTahunIni->avg('surveysdm_5') ?? 0 }}
                                </li>
                                <li>
                                    Perlindungan Asuransi : {{ $dataTahunIni->avg('surveysdm_6') ?? 0 }}
                                </li>
                                <li>
                                    Keamanan Bekerja : {{ $dataTahunIni->avg('surveysdm_7') ?? 0 }}
                                </li>
                                <li>
                                    Keselamatan Bekerja : {{ $dataTahunIni->avg('surveysdm_8') ?? 0 }}
                                </li>
                                <li>
                                    Fasilitas Umum : {{ $dataTahunIni->avg('surveysdm_9') ?? 0 }}
                                </li>
                                <li>
                                    Kontribusi Kinerja : {{ $dataTahunIni->avg('surveysdm_10') ?? 0 }}
                                </li>
                            </ol>

                            <b><u><i>Skor rata-rata total : {{ $dataTahunIni->avg('surveysdm_skor') ?? 0 }}</i></u></b><br>
                            Jumlah responden : {{ number_format($pesertaTahunIni, 0, ',', '.') }} SDM.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <p>
        <a class="isi-xhr utama" href="{{ $app->url->route('sdm.kepuasan.data') }}">SELENGKAPNYA</a>
    </p>
</details>
