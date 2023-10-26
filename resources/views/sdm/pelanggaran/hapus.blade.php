@extends('rangka')

@section('isi')
    <div id="pelanggaran_hapus">
        <form class="form-xhr kartu" id="form_pelanggaranSDMHapus" method="POST" action="{{ $app->url->current() }}">
            <input name="_token" type="hidden" value="{{ $app->request->session()->token() }}">

            <div class="judul-form gspan-4">
                <h4 class="form">Hapus Data Laporan Pelanggaran SDM</h4>

                <a class="tutup-i">
                    <svg viewbox="0 0 24 24">
                        <use href="#ikontutup"></use>
                    </svg>
                </a>
            </div>

            <div class="isian gspan-4">
                <p>
                    Yakin menghapus data pelanggaran SDM : {{ $langgar->langgar_lap_no }} - {{ $langgar->langgar_isi }} ?
                </p>
                <label for="alasan_hapus_penempatan">Alasan Penghapusan</label>
                <textarea id="alasan_hapus_penempatan" name="alasan" cols="3" required>{{ $app->request->old('alasan') }}</textarea>
                <span class="t-bantu">Isi alasan penghapusan data</span>
            </div>

            <div class="gspan-4"></div>

            <button class="utama pelengkap" type="submit">SIMPAN</button>
        </form>

        <script>
            (async () => {
                while (!window.aplikasiSiap) {
                    await new Promise((resolve, reject) =>
                        setTimeout(resolve, 1000));
                }

                formatIsian('#form_pelanggaranSDMHapus .isian textarea');
            })();
        </script>

        @include('pemberitahuan')
        @include('komponen')
    </div>
@endsection
