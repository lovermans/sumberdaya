@extends('rangka')

@section('isi')
<div id="pelanggaran_sdm_lihat">
    <div class="kartu form">
        @isset($langgar)
        <div class="judul-form gspan-4">
            <h4 class="form">Data Laporan Pelanggaran SDM</h4>

            <a class="tutup-i">
                <svg viewbox="0 0 24 24">
                    <use href="#ikontutup"></use>
                </svg>
            </a>
        </div>

        <div class="isian">
            <h3>Nomor Laporan</h3>
            <p>{{ $langgar->langgar_lap_no }}</p>
        </div>

        <div class="isian">
            <h3>Tanggal Laporan</h3>
            <p>{{ strtoupper($app->date->make($langgar->langgar_tanggal)?->translatedFormat('d F Y')) }}</p>
        </div>

        <div class="isian">
            <h3>Status Laporan</h3>
            <p>{{ $langgar->langgar_status }}</p>
        </div>

        <div class="isian">
            <h3>Identitas Pelapor</h3>
            <p>{{ $langgar->langgar_pelapor }} - {{ $langgar->langgar_psdm_nama }} - {{ $langgar->langgar_plokasi }} -
                {{ $langgar->langgar_pkontrak }} - {{ $langgar->langgar_pposisi }}</p>
        </div>

        <div class="isian">
            <h3>Identitas Terlapor</h3>
            <p>{{ $langgar->langgar_no_absen }} - {{ $langgar->langgar_tsdm_nama }} - {{ $langgar->langgar_tlokasi }} -
                {{ $langgar->langgar_tkontrak }} - {{ $langgar->langgar_tposisi }}</p>
        </div>

        <div class="isian gspan-4">
            <h3>Dugaan Pelanggaran</h3>
            <p>{!! nl2br($langgar->langgar_isi) !!}</p>
        </div>

        <div class="isian gspan-4">
            <h3>Keterangan Laporan</h3>
            <p>{!! nl2br($langgar->langgar_keterangan) !!}</p>
        </div>

        <div class="isian gspan-4">
            <h3>Bukti Pendukung Laporan</h3>

            @if ($app->filesystem->exists('sdm/pelanggaran/berkas/' . $langgar->langgar_lap_no . '.pdf'))
            <iframe class="berkas tcetak"
                src="{{ $app->url->route('sdm.pelanggaran.berkas', ['berkas' => $langgar->langgar_lap_no . '.pdf' . '?' . filemtime(storage_path('app/sdm/pelanggaran/berkas/' . $langgar->langgar_lap_no . '.pdf'))]) }}"
                title="Bukti Pendukung Laporan Pelanggaran SDM" loading="lazy"
                onload="if (this.contentDocument.body.id == 'badan-dokumen') this.remove()"></iframe>

            <a class="sekunder tcetak" target="_blank" title="Unduh Berkas Terunggah"
                href="{{ $app->url->route('sdm.pelanggaran.berkas', ['berkas' => $langgar->langgar_lap_no . '.pdf' . '?' . filemtime(storage_path('app/sdm/pelanggaran/berkas/' . $langgar->langgar_lap_no . '.pdf'))]) }}">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonunduh"></use>
                </svg>
                BERKAS
            </a>

            @else
            <p class="merah">Tidak ada bukti pendukung terunggah.</p>

            @endif
        </div>

        <div class="isian gspan-4"></div>

        @unless ($langgar->final_sanksi_jenis)
        <a class="utama isi-xhr" data-rekam="false" data-tujuan="#pelanggaran_sdm_lihat_sematan"
            href="{{ $app->url->route('sdm.pelanggaran.ubah', ['uuid' => $langgar->langgar_uuid]) }}">UBAH
            LAPORAN PELANGGARAN</a>
        @endunless

        <div class="isian gspan-4">
            <h3>Sanksi Aktif Sebelumnya</h3>
            <p>
                @if ($langgar->lap_no_sebelumnya)
                Lap : {{ $langgar->lap_no_sebelumnya }}<br>
                Sanksi : {{ $langgar->sanksi_aktif_sebelumnya }}<br>
                Berakhir pada : {{
                strtoupper($app->date->make($langgar->sanksi_selesai_sebelumnya)?->translatedFormat('d F Y')) }}

                @else
                Tidak ada

                @endif
            </p>
        </div>

        @if ($langgar->lap_no_sebelumnya)
        <div class="isian gspan-4">
            <h3>Berkas Sanksi Sebelumnya</h3>

            @if ($app->filesystem->exists('sdm/sanksi/berkas/' . $langgar->langgar_no_absen . ' - ' .
            $langgar->sanksi_aktif_sebelumnya . ' - ' . $langgar->sanksi_mulai_sebelumnya . '.pdf'))
            <iframe class="berkas tcetak"
                src="{{ $app->url->route('sdm.sanksi.berkas', ['berkas' => $langgar->langgar_no_absen . ' - ' .
                $langgar->sanksi_aktif_sebelumnya . ' - ' . $langgar->sanksi_mulai_sebelumnya . '.pdf' . '?' . filemtime(storage_path('app/sdm/sanksi/berkas/' . $langgar->langgar_no_absen . ' - ' . $langgar->sanksi_aktif_sebelumnya . ' - ' . $langgar->sanksi_mulai_sebelumnya . '.pdf'))]) }}"
                title="Dokumen Sanksi Sebelumnya" loading="lazy"
                onload="if (this.contentDocument.body.id == 'badan-dokumen') this.remove()"></iframe>

            <a class="sekunder tcetak" target="_blank" title="Unduh Berkas Terunggah"
                href="{{ $app->url->route('sdm.sanksi.berkas', ['berkas' => $langgar->langgar_no_absen . ' - ' . $langgar->sanksi_aktif_sebelumnya . ' - ' . $langgar->sanksi_mulai_sebelumnya . '.pdf' . '?' . filemtime(storage_path('app/sdm/sanksi/berkas/' . $langgar->langgar_no_absen . ' - ' . $langgar->sanksi_aktif_sebelumnya . ' - ' . $langgar->sanksi_mulai_sebelumnya . '.pdf'))]) }}">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonunduh"></use>
                </svg>
                BERKAS
            </a>

            @else
            <p class="merah">Tidak ada bukti pendukung terunggah.</p>

            @endif
        </div>
        @endif

        <div class="isian gspan-4">
            <h3>Sanksi Diberikan</h3>
            <p>
                @if ($langgar->final_sanksi_jenis)
                Sanksi : {{ $langgar->final_sanksi_jenis }} <br>
                Mulai : {{ strtoupper($app->date->make($langgar->final_sanksi_mulai)?->translatedFormat('d F Y'))
                }}<br>
                Selesai : {{ strtoupper($app->date->make($langgar->final_sanksi_selesai)?->translatedFormat('d F Y'))
                }}<br>
                Tambahan : {!! nl2br($langgar->final_sanksi_tambahan) !!}<br>
                Keterangan : {!!nl2br($langgar->final_sanksi_keterangan) !!}

                @else
                Belum ada sanksi

                @endif
            </p>
        </div>

        @if ($langgar->final_sanksi_jenis)
        <div class="isian gspan-4">
            <h3>Berkas Pemberian Sanksi</h3>

            @if ($app->filesystem->exists('sdm/sanksi/berkas/' . $langgar->langgar_no_absen . ' - ' .
            $langgar->final_sanksi_jenis . ' - ' . $langgar->final_sanksi_mulai . '.pdf'))
            <iframe class="berkas tcetak"
                src="{{ $app->url->route('sdm.sanksi.berkas', ['berkas' => $langgar->langgar_no_absen . ' - ' . $langgar->final_sanksi_jenis . ' - ' . $langgar->final_sanksi_mulai . '.pdf' . '?' . filemtime(storage_path('app/sdm/sanksi/berkas/' . $langgar->langgar_no_absen . ' - ' . $langgar->final_sanksi_jenis . ' - ' . $langgar->final_sanksi_mulai . '.pdf'))]) }}"
                title="Dokumen Sanksi SDM" loading="lazy"
                onload="if (this.contentDocument.body.id == 'badan-dokumen') this.remove()"></iframe>

            <a class="sekunder tcetak" target="_blank" title="Unduh Berkas Terunggah"
                href="{{ $app->url->route('sdm.sanksi.berkas', ['berkas' => $langgar->langgar_no_absen . ' - ' . $langgar->final_sanksi_jenis . ' - ' . $langgar->final_sanksi_mulai . '.pdf' . '?' . filemtime(storage_path('app/sdm/sanksi/berkas/' . $langgar->langgar_no_absen . ' - ' . $langgar->final_sanksi_jenis . ' - ' . $langgar->final_sanksi_mulai . '.pdf'))]) }}">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonunduh"></use>
                </svg>
                BERKAS
            </a>

            @else
            <p class="merah">Tidak ada bukti pendukung terunggah.</p>

            @endif
        </div>
        @endif

        @if ($langgar->langgar_status == 'DIPROSES')
        @unless ($langgar->final_sanksi_jenis)
        <a class="utama isi-xhr" data-rekam="false" data-tujuan="#pelanggaran_sdm_lihat_sematan"
            href="{{ $app->url->route('sdm.sanksi.tambah', ['lap_uuid' => $langgar->langgar_uuid]) }}">TAMBAH
            SANKSI</a>

        @else
        <a class="utama isi-xhr" data-rekam="false" data-tujuan="#pelanggaran_sdm_lihat_sematan"
            href="{{ $app->url->route('sdm.sanksi.ubah', ['uuid' => $langgar->final_sanksi_uuid]) }}">UBAH
            SANKSI</a>
        @endunless
        @endif

        @else
        <div class="isian gspan-4">
            <p>Periksa kembali data yang diminta.</p>
        </div>

        @endisset
    </div>

    <div id="pelanggaran_sdm_lihat_sematan" class="scroll-margin"></div>

    @include('pemberitahuan')
    @include('komponen')
</div>
@endsection