@extends('rangka')

@section('isi')
<div id="atur_unggah">
    <form id="form_atur_unggah" class="form-xhr kartu" method="POST" data-laju="true" data-tujuan="#atur_tabels" action="{{ $urlRangka->route('atur.unggah') }}">
        <input type="hidden" name="_token" value="{{ $sesiRangka->token() }}">
        <div class="gspan-4">
            <a class="tutup-i"><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#tutup' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use></svg></a>
            <h4 class="form">{{$rekRangka->routeIs('atur.tambah') ? 'Tambah' : 'Ubah'}} Data Pengaturan Umum</h4>
            <p>Data <b><i>Jenis Aturan</i></b> dan <b><i>Butir Aturan</i></b> dilindungi dari perubahan data. Jika terdapat data identik dari <b><i>Jenis Aturan</i></b> dan <b><i>Butir Aturan</i></b>, maka hanya akan mengubah data isian lainnya selain kedua data tersebut.</p>
            <p>Unduh <a class="isi-xhr" href="{{ $urlRangka->route('atur.contoh-unggah') }}" data-rekam="false" data-tujuan="#atur_tabels" data-laju="true">contoh</a> excel, isi sesuai petunjuk dalam excel lalu unggah kembali.</p>
        </div>
        <div class="isian gspan-2">
            <label for="atur_unggahBerkas">Berkas</label>
            <input id="atur_unggahBerkas" type="file" name="atur_unggah" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
            <span class="t-bantu">Berkas excel</span>
        </div>
        <div class="gspan-4"></div>
        <button class="utama pelengkap" type="submit">UNGGAH</button>
    </form>

    @includeWhen($sesiRangka->has('spanduk') || $sesiRangka->has('pesan') || $errors->any(), 'pemberitahuan')
</div>
@endsection
