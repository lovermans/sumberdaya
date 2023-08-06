@extends('rangka')

@section('isi')
<div id="unggah_profil_sdm" class="scroll-margin">
    <form id="form_unggah_profil_sdm" class="form-xhr kartu" method="POST" data-laju="true"
        data-tujuan="#unggah_profil_sdm" action="{{ $urlRangka->route('sdm.unggah') }}">
        <input type="hidden" name="_token" value="{{ $rekRangka->session()->token() }}">

        <div class="isian gspan-4">
            <a class="tutup-i"><svg viewbox="0 0 24 24">
                    <use href="#ikontutup"></use>
                </svg></a>

            <h4 class="form">Unggah Data Profil SDM</h4>

            <p>Unduh <a class="isi-xhr" href="{{ $urlRangka->route('sdm.contoh-unggah') }}" data-rekam="false"
                    data-tujuan="#unggah_profil_sdm" data-laju="true">contoh</a> excel, isi sesuai petunjuk dalam excel
                lalu unggah kembali.</p>
        </div>

        <div class="isian">
            <label for="unggah_profil_sdmBerkas">Berkas</label>

            <input id="unggah_profil_sdmBerkas" type="file" name="unggah_profil_sdm"
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