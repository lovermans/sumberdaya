@extends('rangka')

@section('isi')
    <div id="sanksi_unggah">
        <form class="form-xhr kartu" id="form_sanksi_unggah" data-laju="true" data-tujuan="#sanksi_unggah" method="POST"
            action="{{ $app->url->route('sdm.sanksi.unggah') }}">
            <input name="_token" type="hidden" value="{{ $app->request->session()->token() }}">

            <div class="judul-form gspan-4">
                <h4 class="form">Unggah Data Sanksi SDM</h4>

                <a class="tutup-i">
                    <svg viewbox="0 0 24 24">
                        <use href="#ikontutup"></use>
                    </svg>
                </a>
            </div>

            <p class="merah">
                Gunakan hanya untuk mengunggah data sanksi baru saja.
            </p>

            <p>
                Unduh
                <a class="isi-xhr" data-rekam="false" data-tujuan="#sanksi_unggah" data-laju="true" href="{{ $app->url->route('sdm.sanksi.contoh-unggah') }}">
                    contoh
                </a>
                excel, isi sesuai petunjuk dalam excel lalu unggah kembali.
            </p>

            <div class="isian">
                <label for="unggah_sanksirkas">Berkas</label>
                <input id="unggah_sanksirkas" name="unggah_sanksi_sdm" type="file"
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
