<details class="kartu">
    <summary>
        Tahun {{ $app->date->today()->subYear()->format('Y') }} =
        {{ $pesertaTahunLalu > 0 ? number_format(($puasTahunLalu / $pesertaTahunLalu) * 100, 2, ',', '.') : 0 }}% dari total
        {{ number_format($pesertaTahunLalu, 0, ',', '.') }} responden merasa puas | Tahun {{ $app->date->today()->format('Y') }} =
        {{ $pesertaTahunIni > 0 ? number_format(($puasTahunIni / $pesertaTahunIni) * 100, 2, ',', '.') : 0 }}% dari total
        {{ number_format($pesertaTahunIni, 0, ',', '.') }}
        responden merasa puas.
        {{-- Rata-rata Penilaian Berkala SDM : Tahun {{ $app->date->today()->subYear()->format('Y') }} =
        {{ number_format($rataTahunLalu, 2, ',', '.') }} | Tahun {{ $app->date->today()->format('Y') }} =
        {{ number_format($rataTahunIni, 2, ',', '.') }} --}}
    </summary>

    <p>
        <a class="isi-xhr utama" href="{{ $app->url->route('sdm.kepuasan.data') }}">SELENGKAPNYA</a>
    </p>
</details>
