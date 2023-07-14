<details class="kartu">
    <summary>Rata-rata Penilaian Berkala SDM : Tahun {{ $dateRangka->today()->subYear()->format('Y') }} =
        {{number_format($rataTahunLalu, 2, ',','.')}} | Tahun {{ $dateRangka->today()->format('Y') }} =
        {{number_format($rataTahunIni, 2, ',','.')}}</summary>
    <p><a class="isi-xhr utama" href="{{ $urlRangka->route('sdm.penilaian.data' }}">SELENGKAPNYA</a></p>
</details>