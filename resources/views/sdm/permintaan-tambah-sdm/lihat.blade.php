@extends('rangka')

@section('isi')
<div id="permintambahsdm_lihat">
    <h4>Data Permintaan Tambah SDM</h4>
    <div class="kartu form">
        @isset($permin)

            <div class="isian">
                <h3>No Permintaan</h3>
                <p>{{ $permin->tambahsdm_no }}</p>
            </div>

            <div class="isian gspan-2">
                <h3>Pemohon</h3>
                <p>{{ $permin->tambahsdm_sdm_id }} - {{ $permin->sdm_nama }}</p>
            </div>
            <div class="isian">
                <h3>Penempatan Dibutuhkan</h3>
                <p>{{ $permin->tambahsdm_penempatan }}</p>
            </div>
            <div class="isian gspan-2">
                <h3>Posisi Dibutuhkan</h3>
                <p>{{ $permin->tambahsdm_posisi }}</p>
            </div>
            <div class="isian" >
                <h3>Jumlah Dibutuhkan</h3>
                <p>{{ $permin->tambahsdm_jumlah }}</p>
            </div>
            <div class="isian" >
                <h3>Jumlah Terpenuhi</h3>
                <p>{{ $permin->tambahsdm_terpenuhi }}</p>
            </div>
            <div class="isian">
                <h3>Tanggal Diusulkan</h3>
                <p>{{ strtoupper($dateRangka->make($permin->tambahsdm_tgl_diusulkan)?->translatedFormat('d F Y')) }}</p>
            </div>
            <div class="isian">
                <h3>Tanggal Dibutuhkan</h3>
                <p>{{ strtoupper($dateRangka->make($permin->tambahsdm_tgl_dibutuhkan)?->translatedFormat('d F Y')) }}</p>
            </div>
            <div class="isian">
                <h3>Status Permohonan</h3>
                <p>{{ $permin->tambahsdm_status }}</p>
            </div>
            <div class="isian gspan-4">
                <h3>Alasan</h3>
                <p>{!! nl2br($permin->tambahsdm_alasan) !!}</p>
            </div>
            <div class="isian gspan-4">
                <h3>Keterangan</h3>
                <p>{!! nl2br($permin->tambahsdm_keterangan) !!}</p>
            </div>
            <div class="isian gspan-4">
                <h3>Berkas Permohonan</h3>
                @if ($storageRangka->exists('sdm/permintaan-tambah-sdm/'.$permin->tambahsdm_no.'.pdf'))
                    <iframe class="tcetak" src="{{ $urlRangka->route('sdm.permintaan-tambah-sdm.berkas', ['berkas' => $permin->tambahsdm_no . '.pdf' . '?' . filemtime($appRangka->storagePath('app/sdm/permintaan-tambah-sdm/' . $permin->tambahsdm_no . '.pdf'))]) }}" title="Berkas Permintaan SDM" loading="lazy" style="width:100%;height:auto;aspect-ratio:4/3"></iframe>
                @else
                    <p class="merah">Tidak ada berkas terunggah.</p>
                @endif
                <a class="isi-xhr sekunder tcetak" data-rekam="false" data-laju="true" data-tujuan="#permintaan-sdm_sematan_lihat" href="{{ $urlRangka->route('sdm.permintaan-tambah-sdm.formulir', ['uuid' => $permin->tambahsdm_uuid]) }}">
                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#cetak' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                    FORMULIR
                </a>
                <div id="permintaan-sdm_sematan_lihat"></div>
            </div>
            <div class="gspan-4"></div>
            <a class="utama isi-xhr tcetak" data-rekam="false" data-tujuan="#permintambahsdm_lihat" href="{{ $urlRangka->route('sdm.permintaan-tambah-sdm.ubah', ['uuid' => $permin->tambahsdm_uuid]) }}">UBAH</a>
            @if ($rekRangka->pjax())
                <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.parentElement.remove()">TUTUP</a>
            @else
                <a class="isi-xhr sekunder" href="{{$urlRangka->to($rekRangka->session()->get('tautan-perujuk') ?? '/')}}">TUTUP</a>
            @endif
        @else
            <div class="isian gspan-4">
                <p>Periksa kembali data yang diminta.</p>
            </div>
        @endisset
    </div>

    @includeWhen($rekRangka->session()->has('spanduk') || $rekRangka->session()->has('pesan') || $errors->any(), 'pemberitahuan')

</div>
@endsection
