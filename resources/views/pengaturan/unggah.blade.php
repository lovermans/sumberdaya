@extends('rangka')

@section('isi')
<div id="atur_unggah">
    <form id="form_atur_unggah" class="form-xhr kartu" method="POST" data-laju="true" data-tujuan="#atur_sematan_unggah" action="{{ $urlRangka->route('atur.unggah') }}">
        <input type="hidden" name="_token" value="{{ $rekRangka->session()->token() }}">
        <div class="gspan-4">
            <h4>Unggah Data Pengaturan</h4>
            <p>Data <b><i>Jenis Aturan</i></b> dan <b><i>Butir Aturan</i></b> dilindungi dari perubahan data. Jika terdapat data identik dari <b><i>Jenis Aturan</i></b> dan <b><i>Butir Aturan</i></b>, maka hanya akan mengubah data isian lainnya selain kedua data tersebut.</p>
            <p>Unduh <a class="isi-xhr" href="{{ $urlRangka->route('atur.contoh-unggah') }}" data-rekam="false" data-tujuan="#atur_sematan_unggah" data-laju="true">contoh</a> excel, isi sesuai petunjuk dalam excel lalu unggah kembali.</p>
            <div id="atur_sematan_unggah" style="scroll-margin:4em 0 0 0"></div>
        </div>
        <div class="isian gspan-2">
            <label for="atur_unggahBerkas">Berkas</label>
            <input id="atur_unggahBerkas" type="file" name="atur_unggah" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
            <span class="t-bantu">Berkas excel</span>
        </div>
        <div class="gspan-4"></div>
        <button class="utama pelengkap" type="submit">UNGGAH</button>
        @if ($rekRangka->pjax())
            <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.parentElement.remove()">TUTUP</a>
        @else
            <a class="isi-xhr sekunder" href="{{$urlRangka->to($rekRangka->session()->get('tautan_perujuk') ?? '/')}}">TUTUP</a>
        @endif
    </form>

    @includeWhen($rekRangka->session()->has('spanduk') || $rekRangka->session()->has('pesan') || $errors->any(), 'pemberitahuan')
</div>
@endsection
