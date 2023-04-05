@extends('rangka')

@section('isi')
<div id="unggah_penempatan_sdm">    
    <form id="form_unggah_penempatan_sdm" class="form-xhr kartu" method="POST" data-laju="true" data-tujuan="#unggah_penempatan_sdm" action="{{ $urlRangka->route('sdm.penempatan.unggah', [], false) }}">
        <input type="hidden" name="_token" value="{{ $rekRangka->session()->token() }}">
        
        <div class="gspan-4">
            <a class="tutup-i">
                <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ $mixRangka('/ikon.svg') . '#tutup' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg>
            </a>
            
            <h4 class="form">Unggah Data Penempatan SDM</h4>
            
            <p>Unduh <a class="isi-xhr" href="{{ $urlRangka->route('sdm.penempatan.contoh-unggah', [], false) }}" data-rekam="false" data-tujuan="#unggah_penempatan_sdm" data-laju="true">contoh</a> excel, isi sesuai petunjuk dalam excel lalu unggah kembali.</p>
        </div>

        <div class="isian">
            <label for="unggah_penempatan_sdmBerkas">Berkas</label>
            
            <input id="unggah_penempatan_sdmBerkas" type="file" name="unggah_penempatan_sdm" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
            
            <span class="t-bantu">Berkas excel</span>
        </div>

        <div class="gspan-4"></div>
        
        <button class="utama pelengkap" type="submit">UNGGAH</button>
    </form>

    @includeWhen($rekRangka->session()->has('spanduk') || $rekRangka->session()->has('pesan') || $errors->any(), 'pemberitahuan')
    @include('komponen')
</div>
@endsection
