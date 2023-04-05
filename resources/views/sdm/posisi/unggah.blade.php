@extends('rangka')

@section('isi')
<div id="posisi_unggah">
    <form id="form_posisi_unggah" class="form-xhr kartu" method="POST" data-laju="true" data-tujuan="#posisi_unggah" action="{{ $urlRangka->route('sdm.posisi.unggah', [], false) }}">
        <input type="hidden" name="_token" value="{{ $rekRangka->session()->token() }}">
        
        <div class="gspan-4">
            <a class="tutup-i">
                <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ $mixRangka('/ikon.svg') . '#tutup' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg>
            </a>

            <h4>Unggah Data Pengaturan Jabatan</h4>

            <p>Unduh <a class="isi-xhr" href="{{ $urlRangka->route('sdm.posisi.contoh-unggah', [], false) }}" data-rekam="false" data-tujuan="#posisi_unggah" data-laju="true">contoh</a> excel, isi sesuai petunjuk dalam excel lalu unggah kembali.</p>
        </div>

        <div class="isian">
            <label for="posisi_unggahBerkas">Berkas</label>
            <input id="posisi_unggahBerkas" type="file" name="posisi_unggah" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
            <span class="t-bantu">Berkas excel</span>
        </div>

        <div class="gspan-4"></div>

        <button class="utama pelengkap" type="submit">UNGGAH</button>
    </form>

    @includeWhen($rekRangka->session()->has('spanduk') || $rekRangka->session()->has('pesan') || $errors->any(), 'pemberitahuan')
    @include('komponen')
</div>
@endsection
