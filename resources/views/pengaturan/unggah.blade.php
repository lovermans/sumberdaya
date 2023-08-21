@extends('rangka')

@section('isi')
<div id="atur_unggah" class="scroll-margin">
    <form id="form_atur_unggah" class="form-xhr kartu" method="POST" data-laju="true" data-tujuan="#atur_unggah"
        action="{{ $app->url->route('atur.unggah') }}">
        <input type="hidden" name="_token" value="{{ $app->request->session()->token() }}">

        <div class="judul-form gspan-4">
            <h4 class="form">Unggah Data Pengaturan Umum</h4>

            <a class="tutup-i">
                <svg viewbox="0 0 24 24">
                    <use href="#ikontutup"></use>
                </svg>
            </a>
        </div>

        <p>Data <b><i>Jenis Aturan</i></b> dan <b><i>Butir Aturan</i></b> dilindungi dari perubahan data. Jika
            terdapat data identik dari <b><i>Jenis Aturan</i></b> dan <b><i>Butir Aturan</i></b>, maka hanya akan
            mengubah data isian lainnya selain kedua data tersebut.</p>

        <p>Unduh <a class="isi-xhr" href="{{ $app->url->route('atur.contoh-unggah') }}" data-rekam="false"
                data-tujuan="#atur_unggah" data-laju="true">contoh</a> excel, isi sesuai petunjuk dalam excel lalu
            unggah kembali.</p>

        <div class="isian">
            <label for="atur_unggahBerkas">Berkas</label>

            <input id="atur_unggahBerkas" type="file" name="atur_unggah"
                accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>

            <span class="t-bantu">Berkas excel</span>
        </div>

        <div class="gspan-4"></div>

        <button class="utama pelengkap" type="submit">UNGGAH</button>
    </form>

    @include('pemberitahuan')
    @include('komponen')
</div>
@endsection