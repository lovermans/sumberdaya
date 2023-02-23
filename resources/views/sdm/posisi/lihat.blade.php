@extends('rangka')

@section('isi')
<div id="posisi_sdm_lihat">
    <h4>Data Pengaturan Jabatan</h4>
    <div class="kartu form">
        @isset($pos)
            <div class="isian gspan-2">
                <h3>Nama Jabatan</h3>
                <p>{{ $pos->posisi_nama }}</p>
            </div>
            <div class="isian">
                <h3>Kode Jabatan WLKP</h3>
                <p>{{ $pos->posisi_wlkp }}</p>
            </div>
            <div class="isian gspan-4">
                <h3>Keterangan</h3>
                <p>{!! nl2br($pos->posisi_keterangan) !!}</p>
            </div>
            <div class="isian gspan-2">
                <h3>Nama Jabatan Atasan</h3>
                <p>{{ $pos->posisi_atasan }}</p>
            </div>
            <div class="isian">
                <h3>Status</h3>
                <p>{{ $pos->posisi_status }}</p>
            </div>
            <div class="isian gspan-4"></div>
            <a class="utama isi-xhr" data-rekam="false" data-tujuan="#posisi_sdm_lihat" href="{{ $urlRangka->route('sdm.posisi.ubah', ['uuid' => $pos->posisi_uuid]) }}">UBAH</a>
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
