@extends('rangka')

@section('isi')
<div id="nilai_unggah">
    <form id="form_nilai_unggah" class="form-xhr kartu" method="POST" data-laju="true" data-tujuan="#nilai_unggah"
        action="{{ $app->url->route('sdm.penilaian.unggah') }}">
        <input type="hidden" name="_token" value="{{ $app->request->session()->token() }}">

        <div class="judul-form gspan-4">
            <h4 class="form">Unggah Data Penilaian SDM</h4>

            <a class="tutup-i">
                <svg viewbox="0 0 24 24">
                    <use href="#ikontutup"></use>
                </svg>
            </a>
        </div>

        <p>Unduh <a class="isi-xhr" href="{{ $app->url->route('sdm.penilaian.contoh-unggah') }}" data-rekam="false"
                data-tujuan="#nilai_unggah" data-laju="true">contoh</a> excel, isi sesuai
            petunjuk dalam excel lalu unggah kembali.</p>

        <div class="isian">
            <label for="unggah_nilaikas">Berkas</label>
            <input id="unggah_nilaikas" type="file" name="unggah_nilai_sdm"
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