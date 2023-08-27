@extends('rangka')

@section('isi')
<div id="sanksi_sdm_lihat">
    <div class="kartu form">
        @isset($sanksi)
        <div class="judul-form gspan-4">
            <h4 class="form">Data Sanksi SDM</h4>

            <a class="tutup-i">
                <svg viewbox="0 0 24 24">
                    <use href="#ikontutup"></use>
                </svg>
            </a>
        </div>

        @if ($sanksi->sanksi_lap_no)
        <div class="isian">
            <h3>Nomor Laporan</h3>
            <p><u><a class="isi-xhr"
                        href="{{ $app->url->route('sdm.pelanggaran.data', ['kata_kunci' => $sanksi->sanksi_lap_no]) }}"
                        aria-label="Lap Pelanggaran SDM No {{ $sanksi->sanksi_lap_no }}">{{
                        $sanksi->sanksi_lap_no }}</a></u>
            </p>
        </div>

        <div class="isian">
            <h3>Tanggal Laporan</h3>
            <p>{{ strtoupper($app->date->make($sanksi->langgar_tanggal)?->translatedFormat('d F Y')) }}</p>
        </div>

        <div class="isian">
            <h3>Status Laporan</h3>
            <p>{{ $sanksi->langgar_status }}</p>
        </div>

        <div class="isian">
            <h3>Identitas Pelapor</h3>
            <p>{{ $sanksi->langgar_pelapor }} - {{ $sanksi->langgar_psdm_nama }} - {{ $sanksi->langgar_plokasi }} -
                {{ $sanksi->langgar_pkontrak }} - {{ $sanksi->langgar_pposisi }}</p>
        </div>

        <div class="isian">
            <h3>Identitas Terlapor</h3>
            <p>{{ $sanksi->sanksi_no_absen }} - {{ $sanksi->langgar_tsdm_nama }} - {{ $sanksi->langgar_tlokasi }} -
                {{ $sanksi->langgar_tkontrak }} - {{ $sanksi->langgar_tposisi }}</p>
        </div>

        <div class="isian gspan-4">
            <h3>Dugaan Pelanggaran</h3>
            <p>{!! nl2br($sanksi->langgar_isi) !!}</p>
        </div>

        <div class="isian gspan-4">
            <h3>Keterangan Laporan</h3>
            <p>{!! nl2br($sanksi->langgar_keterangan) !!}</p>
        </div>

        <div class="isian gspan-4">
            <h3>Bukti Pendukung Laporan</h3>

            @if ($app->filesystem->exists($berkasLap_Pelanggaran = 'sdm/pelanggaran/berkas/' . $sanksi->sanksi_lap_no .
            '.pdf'))
            <iframe class="berkas tcetak"
                src="{{ $app->url->route('sdm.berkas', ['berkas' => $berkasLap_Pelanggaran . '?' . filemtime(storage_path('app/' . $berkasLap_Pelanggaran))]) }}"
                title="Bukti Pendukung Laporan Pelanggaran SDM" loading="lazy"
                onload="if (this.contentDocument.body.id == 'badan-dokumen') this.remove()"></iframe>

            <a class="sekunder tcetak" target="_blank" title="Unduh Berkas Terunggah"
                href="{{ $app->url->route('sdm.berkas', ['berkas' => $berkasLap_Pelanggaran . '?' . filemtime(storage_path('app/' . $berkasLap_Pelanggaran))]) }}">
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
                Sanksi : {{ $sanksi->sanksi_jenis }} <br>
                Mulai : {{ strtoupper($app->date->make($sanksi->sanksi_mulai)?->translatedFormat('d F Y'))
                }}<br>
                Selesai : {{ strtoupper($app->date->make($sanksi->sanksi_selesai)?->translatedFormat('d F Y'))
                }}<br>
                Tambahan : {!! nl2br($sanksi->sanksi_tambahan) !!}<br>
                Keterangan : {!!nl2br($sanksi->sanksi_keterangan) !!}
            </p>
        </div>


        <div class="isian gspan-4">
            <h3>Berkas Pemberian Sanksi</h3>

            @if ($app->filesystem->exists($berkasSanksi = 'sdm/sanksi/berkas/' . $sanksi->sanksi_no_absen . ' - ' .
            $sanksi->sanksi_jenis . ' - ' . $sanksi->sanksi_mulai . '.pdf'))
            <iframe class="berkas tcetak"
                src="{{ $app->url->route('sdm.berkas', ['berkas' => $berkasSanksi . '?' . filemtime(storage_path('app/' . $berkasSanksi))]) }}"
                title="Dokumen Sanksi SDM" loading="lazy"
                onload="if (this.contentDocument.body.id == 'badan-dokumen') this.remove()"></iframe>

            <a class="sekunder tcetak" target="_blank" title="Unduh Berkas Terunggah"
                href="{{ $app->url->route('sdm.berkas', ['berkas' => $berkasSanksi . '?' . filemtime(storage_path('app/' . $berkasSanksi))]) }}">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonunduh"></use>
                </svg>
                BERKAS
            </a>

            @else
            <p class="merah">Tidak ada bukti pendukung terunggah.</p>

            @endif
        </div>

        <a class="utama isi-xhr" data-rekam="false" data-tujuan="#sanksi_sdm_lihat_sematan"
            href="{{ $app->url->route('sdm.sanksi.ubah', ['uuid' => $sanksi->sanksi_uuid]) }}">UBAH
            SANKSI</a>

        <a class="sekunder isi-xhr" data-rekam="false" data-tujuan="#sanksi_sdm_lihat_sematan"
            href="{{ $app->url->route('sdm.sanksi.hapus', ['uuid' => $sanksi->sanksi_uuid]) }}">HAPUS</a>

        @endisset
    </div>

    <div id="sanksi_sdm_lihat_sematan" class="scroll-margin"></div>

    @include('pemberitahuan')
    @include('komponen')
</div>
@endsection