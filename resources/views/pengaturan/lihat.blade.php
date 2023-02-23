@extends('rangka')

@section('isi')
<div id="atur_lihat">
    <div class="kartu form">
        @isset($atur)
            <div class="gspan-4">
                <h4>Data Pengaturan</h4>
            </div>
            <div class="isian">
                <h3>Jenis Pengaturan</h3>
                <p>{{ $atur->atur_jenis }}</p>
            </div>
            <div class="isian">
                <h3>Butir Pengaturan</h3>
                <p>{{ $atur->atur_butir }}</p>
            </div>
            <div class="isian">
                <h3>Status Pengaturan</h3>
                <p>{{ $atur->atur_status }}</p>
            </div>
            <div class="isian gspan-4">
                <h3>Keterangan</h3>
                <p>{!! nl2br($atur->atur_detail) !!}</p>
            </div>
            <div class="gspan-4"></div>
            <a class="utama isi-xhr" data-rekam="false" data-tujuan="#atur_lihat" href="{{ $urlRangka->route('atur.ubah', ['uuid' => $atur->atur_uuid]) }}">UBAH</a>
            @if ($rekRangka->pjax())
                <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.parentElement.remove()">TUTUP</a>
            @else
                <a class="isi-xhr sekunder" href="{{$urlRangka->to($rekRangka->session()->get('tautan_perujuk') ?? '/')}}">TUTUP</a>
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
