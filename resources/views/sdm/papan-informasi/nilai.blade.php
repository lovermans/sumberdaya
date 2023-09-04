<details class="kartu">
    <summary>
        Rata-rata Penilaian Berkala SDM : Tahun {{ $app->date->today()->subYear()->format('Y') }} =
        {{ number_format($rataTahunLalu, 2, ',', '.') }} | Tahun {{ $app->date->today()->format('Y') }} =
        {{ number_format($rataTahunIni, 2, ',', '.') }}
    </summary>

    <p>
        <a class="isi-xhr utama" href="{{ $app->url->route('sdm.penilaian.data') }}">SELENGKAPNYA</a>
    </p>
</details>
