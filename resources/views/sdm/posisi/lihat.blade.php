@extends('rangka')

@section('isi')
<div id="posisi_sdm_lihat" class="scroll-margin">    
    <div class="kartu form">
        @isset($pos)
        <div class="gspan-4">
            <a class="tutup-i"><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><use xlink:href="{{ $mixRangka('/ikon.svg') . '#tutup' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use></svg></a>
            <h4 class="form">Data Pengaturan Jabatan</h4>
        </div>

        <div class="isian ">
            <h3>Nama Jabatan</h3>
            <p>{{ $pos->posisi_nama }}</p>
        </div>

        <div class="isian">
            <h3>Kode Jabatan WLKP</h3>
            <p>{{ $pos->posisi_wlkp }}</p>
        </div>

        <div class="isian">
            <h3>Keterangan</h3>
            <p>{!! nl2br($pos->posisi_keterangan) !!}</p>
        </div>

        <div class="isian ">
            <h3>Nama Jabatan Atasan</h3>
            <p>{{ $pos->posisi_atasan }}</p>
        </div>

        <div class="isian">
            <h3>Status</h3>
            <p>{{ $pos->posisi_status }}</p>
        </div>

        <div class="isian gspan-4"></div>
        
        <a class="utama isi-xhr" data-rekam="false" data-tujuan="#posisi_sdm_lihat" href="{{ $urlRangka->route('sdm.posisi.ubah', ['uuid' => $pos->posisi_uuid], false) }}">UBAH</a>

        @else
        <div class="isian gspan-4">
            <p>Periksa kembali data yang diminta.</p>
        </div>

        @endisset
    </div>

    @includeWhen($rekRangka->session()->has('spanduk') || $rekRangka->session()->has('pesan') || $errors->any(), 'pemberitahuan')
</div>
@endsection
