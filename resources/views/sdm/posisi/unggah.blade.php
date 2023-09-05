@extends('rangka')

@section('isi')
    <div id="posisi_unggah">
        <form class="form-xhr kartu" id="form_posisi_unggah" data-laju="true" data-tujuan="#posisi_unggah" method="POST"
            action="{{ $app->url->route('sdm.posisi.unggah') }}">
            <input name="_token" type="hidden" value="{{ $app->request->session()->token() }}">

            <div class="judul-form gspan-4">
                <h4 class="form">Unggah Data Pengaturan Jabatan</h4>

                <a class="tutup-i">
                    <svg viewbox="0 0 24 24">
                        <use href="#ikontutup"></use>
                    </svg>
                </a>
            </div>

            <p>
                Unduh
                <a class="isi-xhr" data-rekam="false" data-tujuan="#posisi_unggah" data-laju="true" href="{{ $app->url->route('sdm.posisi.contoh-unggah') }}">
                    contoh
                </a>
                excel, isi sesuai petunjuk dalam excel lalu unggah kembali.
            </p>

            <div class="isian">
                <label for="posisi_unggahBerkas">Berkas</label>
                <input id="posisi_unggahBerkas" name="posisi_unggah" type="file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                    required>
                <span class="t-bantu">Berkas excel</span>
            </div>

            <div class="gspan-4"></div>

            <button class="utama pelengkap" type="submit">UNGGAH</button>
        </form>

        @include('pemberitahuan')
        @include('komponen')
    </div>
@endsection
