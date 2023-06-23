@extends('rangka')

@section('isi')
<div id="penempatan_sdm_lihat">
    <div class="kartu form">
        @isset($langgar)
        <div class="gspan-4">
            <a class="tutup-i"><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ $mixRangka('/ikon.svg') . '#tutup' }}"
                        xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg></a>

            <h4 class="form">Data Laporan Pelanggaran SDM</h4>
        </div>

        <div class="isian">
            <h3>Nomor Laporan</h3>
            <p>{{ $langgar->langgar_lap_no }}</p>
        </div>

        <div class="isian">
            <h3>Tanggal Laporan</h3>
            <p>{{ strtoupper($dateRangka->make($langgar->langgar_tanggal)?->translatedFormat('d F Y')) }}</p>
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

            @if ($storageRangka->exists('sdm/pelanggaran/berkas/' . $langgar->langgar_lap_no . '.pdf'))
            <iframe class="berkas tcetak"
                src="{{ $urlRangka->route('sdm.pelanggaran.berkas', ['berkas' => $langgar->langgar_lap_no . '.pdf' . '?' . filemtime(storage_path('app/sdm/pelanggaran/berkas/' . $langgar->langgar_lap_no . '.pdf'))], false) }}"
                title="Bukti Pendukung Laporan Pelanggaran SDM" loading="lazy"
                onload="if (this.contentDocument.body.id == 'badan-dokumen') this.remove()"></iframe>

            <a class="sekunder tcetak" target="_blank" title="Unduh Berkas Terunggah"
                href="{{ $urlRangka->route('sdm.pelanggaran.berkas', ['berkas' => $langgar->langgar_lap_no . '.pdf' . '?' . filemtime(storage_path('app/sdm/pelanggaran/berkas/' . $langgar->langgar_lap_no . '.pdf'))], false) }}">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ $mixRangka('/ikon.svg') . '#unduh' }}"
                        xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg>
                BERKAS
            </a>

            @else
            <p class="merah">Tidak ada bukti pendukung terunggah.</p>

            @endif
        </div>

        <div class="isian gspan-4"></div>

        <a class="utama isi-xhr" data-rekam="false" data-tujuan="#pelanggaran-sdm_sematan"
            href="{{ $urlRangka->route('sdm.pelanggaran.ubah', ['uuid' => $langgar->langgar_uuid], false) }}">UBAH
            LAPORAN PELANGGARAN</a>

        <div class="isian gspan-4">
            <h3>Sanksi Aktif Sebelumnya</h3>
            <p>
                @if ($langgar->lap_no_sebelumnya)
                Lap : {{ $langgar->lap_no_sebelumnya }} | Sanksi : {{ $langgar->sanksi_aktif_sebelumnya}} | Berakhirpada
                : {{ strtoupper($dateRangka->make($langgar->sanksi_selesai_sebelumnya)?->translatedFormat('d F Y')) }}

                @else
                Tidak ada

                @endif
            </p>
        </div>

        <div class="isian gspan-4">
            <h3>Sanksi Diberikan</h3>
            <p>
                @if ($langgar->final_sanksi_jenis)
                Sanksi : {{ $langgar->final_sanksi_jenis }} | Tambahan : {!! nl2br($langgar->final_sanksi_tambahan) !!}
                | Mulai : {{ strtoupper($dateRangka->make($langgar->final_sanksi_mulai)?->translatedFormat('d F Y')) }}
                | Selesai : {{ strtoupper($dateRangka->make($langgar->final_sanksi_selesai)?->translatedFormat('d FY'))
                }} | Keterangan : {!!nl2br($langgar->final_sanksi_keterangan) !!}

                @else
                Belum ada sanksi

                @endif
            </p>
        </div>


        @endisset
    </div>

    @include('pemberitahuan')
    @include('komponen')
</div>
@endsection