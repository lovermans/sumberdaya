@extends('rangka')

@section('isi')
<div id="penempatan_sdm_hapus">
    <form id="form_penempatanSDMHapus" class="form-xhr kartu" method="POST" action="{{ $app->url->current() }}">
        <input type="hidden" name="_token" value="{{ $app->request->session()->token() }}">

        <div class="judul-form gspan-4">
            <h4 class="form">Hapus Data Penempatan SDM</h4>

            <a class="tutup-i">
                <svg viewbox="0 0 24 24">
                    <use href="#ikontutup"></use>
                </svg>
            </a>
        </div>

        <div class="isian gspan-4">
            <p>Yakin menghapus data penempatan : {{$penem->sdm_no_absen}} - {{$penem->sdm_nama}} -
                {{strtoupper($app->date->make($penem->penempatan_mulai)?->translatedFormat('d F Y'))}} -
                {{$penem->penempatan_lokasi}} - {{$penem->penempatan_kontrak}} - {{$penem->penempatan_ke}} ?</p>

            <label for="alasan_hapus_penempatan">Alasan Penghapusan</label>

            <textarea id="alasan_hapus_penempatan" name="alasan" cols="3"
                required>{{ $app->request->old('alasan') }}</textarea>

            <span class="t-bantu">Isi alasan penghapusan data</span>
        </div>

        <div class="gspan-4"></div>

        <button class="utama pelengkap" type="submit">SIMPAN</button>
    </form>

    <script>
        (async() => {
            while(!window.aplikasiSiap) {
                await new Promise((resolve,reject) =>
                setTimeout(resolve, 1000));
            }
            
            formatIsian('#form_penempatanSDMHapus .isian textarea');
        })();
    </script>

    @include('pemberitahuan')
    @include('komponen')
</div>
@endsection