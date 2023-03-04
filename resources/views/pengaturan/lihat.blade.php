@extends('rangka')

@section('isi')
<div id="atur_lihat">
    <div class="kartu form">
        @isset($atur)
        <div class="gspan-4">
            <a class="tutup-i"><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><use xlink:href="{{ $mixRangka('/ikon.svg') . '#tutup' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use></svg></a>
            <h4 class="form">{{$rekRangka->routeIs('atur.tambah') ? 'Tambah' : 'Ubah'}} Data Pengaturan Umum</h4>
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
        <a class="utama isi-xhr" data-rekam="false" data-tujuan="#atur_sematan" href="{{ $urlRangka->route('atur.ubah', ['uuid' => $atur->atur_uuid], false) }}">UBAH</a>
        @else
        <div class="isian gspan-4">
            <p>Periksa kembali data yang diminta.</p>
        </div>
        @endisset
    </div>
    
    @includeWhen($sesiRangka->has('spanduk') || $sesiRangka->has('pesan') || $errors->any(), 'pemberitahuan')
</div>
@endsection
