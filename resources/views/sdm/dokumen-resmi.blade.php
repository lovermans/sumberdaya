@extends('rangka')

@section('isi')
<div id="sdm_dokumen_resmi">
    <p class="tcetak kartu">
        <svg fill="var(--taut-umum)" viewbox="0 0 24 24">
            <use href="#ikoninformasi"></use>
        </svg>
        Semua tautan dokumen di halaman ini hanya berlaku selama 5 (lima) menit. Muat ulang halaman untuk memperbarui
        masa berlaku tautan dokumen di halaman ini.
    </p>

    @isset($dokumenPengurusCabang)
    @forelse ($dokumenPengurusCabang as $jalur)
    @if (blank($app->request->user()->sdm_ijin_akses) || $app->request->user()->sdm_ijin_akses == substr($jalur,28))
    <details class="kartu">
        <summary>Panduan Pengurus Cabang {{ substr($jalur,28) }}</summary>

        <ol>
            @forelse ($app->filesystem->files($jalur) as $berkas)
            <li>
                <a href="{{ $app->filesystem->disk('local')->temporaryUrl($berkas, $app->date->now()->addMinutes(5)) }}"
                    target="_blank">{{ $app->files->name($berkas).'.'.$app->files->extension($berkas) }}</a>
            </li>
            @empty Panduan Belum Tersedia.
            @endforelse
        </ol>
    </details>
    @endif

    @empty
    <p class="kartu">Panduan Pengurus Cabang belum tersedia.</p>

    @endforelse
    @endisset

    @isset($dokumenPengurus)
    @forelse ($dokumenPengurus as $jalur)
    <details class="kartu">
        <summary>{{ substr($jalur,21) }}</summary>

        <ol>
            @forelse ($app->filesystem->files($jalur) as $berkas)
            <li>
                <a href="{{ $app->filesystem->disk('local')->temporaryUrl($berkas, $app->date->now()->addMinutes(5)) }}"
                    target="_blank">{{ $app->files->name($berkas).'.'.$app->files->extension($berkas) }}</a>
            </li>
            @empty Panduan Belum Tersedia.
            @endforelse
        </ol>
    </details>

    @empty
    <p class="kartu">Panduan Pengurus belum tersedia.</p>

    @endforelse
    @endisset

    @isset($dokumenUmum)
    @forelse ($dokumenUmum as $jalur)
    <details class="kartu" {{ substr($jalur,17)=='Pengumuman' ? 'open' : '' }}>
        <summary>{{ substr($jalur,17) }}</summary>

        <ol>
            @forelse ($app->filesystem->files($jalur) as $berkas)
            <li>
                <a href="{{ $app->filesystem->disk('local')->temporaryUrl($berkas, $app->date->now()->addMinutes(5)) }}"
                    target="_blank">{{ $app->files->name($berkas).'.'.$app->files->extension($berkas) }}</a>
            </li>
            @empty Panduan Belum Tersedia.
            @endforelse
        </ol>
    </details>

    @empty
    <p class="kartu">Panduan SDM belum tersedia.</p>

    @endforelse
    @endisset

    <div class="pintasan tcetak">
        <a href="#" onclick="event.preventDefault();window.scrollTo(0,0)" title="Kembali Ke Atas">
            <svg viewBox="0 0 24 24">
                <use href="#ikonpanahatas"></use>
            </svg>
        </a>
    </div>

    @include('pemberitahuan')
    @include('komponen')
</div>
@endsection