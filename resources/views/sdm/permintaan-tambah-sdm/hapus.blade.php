@extends('rangka')

@section('isi')
<div id="permintaan_tambah_hapus">
    <form id="form_perminTambahSDMHapus" class="form-xhr kartu" method="POST" action="{{ $urlRangka->current() }}">
        <input type="hidden" name="_token" value="{{ $rekRangka->session()->token() }}">

        <div class="gspan-4">
            <a class="tutup-i"><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#tutup' }}"
                        xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg></a>
            <h4 class="form">Hapus Data Permintaan Tambah SDM</h4>
        </div>

        <div class="isian gspan-4">
            <p>Yakin menghapus data permintaan tambah SDM : {{$permin->tambahsdm_no}} -
                {{$permin->tambahsdm_penempatan}} - {{$permin->tambahsdm_posisi}} - {{$permin->tambahsdm_jumlah}} ?</p>
            <label for="alasan_hapus_penempatan">Alasan Penghapusan</label>
            <textarea id="alasan_hapus_penempatan" name="alasan" cols="3"
                required>{{ $rekRangka->old('alasan') }}</textarea>
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
            
            formatIsian('#form_perminTambahSDMHapus .isian textarea');
        })();
    </script>

    @include('pemberitahuan')
    @include('komponen')
</div>
@endsection