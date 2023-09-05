@extends('rangka')

@section('isi')
    <div id="unggah_penempatan_sdm">
        <form class="form-xhr kartu" id="form_unggah_penempatan_sdm" data-laju="true" data-tujuan="#unggah_penempatan_sdm" method="POST"
            action="{{ $app->url->route('sdm.penempatan.unggah') }}">
            <input name="_token" type="hidden" value="{{ $app->request->session()->token() }}">

            <div class="judul-form gspan-4">
                <h4 class="form">Unggah Data Penempatan SDM</h4>

                <a class="tutup-i">
                    <svg viewbox="0 0 24 24">
                        <use href="#ikontutup"></use>
                    </svg>
                </a>
            </div>

            <p>
                Unduh
                <a class="isi-xhr" data-rekam="false" data-tujuan="#unggah_penempatan_sdm" data-laju="true"
                    href="{{ $app->url->route('sdm.penempatan.contoh-unggah') }}">
                    contoh
                </a>
                excel, isi sesuai petunjuk dalam excel lalu unggah kembali.
            </p>

            <div class="isian">
                <label for="unggah_penempatan_sdmBerkas">Berkas</label>

                <input id="unggah_penempatan_sdmBerkas" name="unggah_penempatan_sdm" type="file"
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
