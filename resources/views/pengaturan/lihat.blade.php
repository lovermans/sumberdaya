@extends('rangka')

@section('isi')
<div id="atur_lihat" class="scroll-margin">
    <div class="kartu form">
        @isset($atur)
        <div class="gspan-4">
            <a class="tutup-i">
                <svg viewbox="0 0 24 24">
                    <use href="#ikontutup"></use>
                </svg>
            </a>

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

        <div class="isian">
            <h3>Keterangan</h3>

            <p>{!! nl2br($atur->atur_detail) !!}</p>
        </div>

        <div class="gspan-4"></div>

        <a class="utama isi-xhr" data-rekam="false" data-tujuan="#atur_lihat"
            href="{{ $urlRangka->route('atur.ubah', ['uuid' => $atur->atur_uuid]) }}">UBAH</a>

        @else
        <div class="isian">
            <p>Periksa kembali data yang diminta.</p>
        </div>
        @endisset
    </div>

    @include('pemberitahuan')
    @include('komponen')
</div>
@endsection