@extends('rangka')

@section('isi')
<div id="unggah_profil_sdm">
    <form id="form_unggah_profil_sdm" class="form-xhr kartu" method="POST" data-laju="true" data-tujuan="#unggah_profil_sdm" action="{{ $urlRangka->route('unggah', [], false) }}">
        <input type="hidden" name="_token" value="{{ $sesiRangka->token() }}">
        <div class="isian gspan-4">
            <h4 class="form">Unggah Data Profil SDM</h4>
            <p>Unduh <a class="isi-xhr" href="{{ $urlRangka->route('contoh-unggah', [], false) }}" data-rekam="false" data-tujuan="#unggah_profil_sdm" data-laju="true">contoh</a> excel, isi sesuai petunjuk dalam excel lalu unggah kembali.</p>
        </div>
        <div class="isian gspan-2">
            <label for="unggah_profil_sdmBerkas">Berkas</label>
            <input id="unggah_profil_sdmBerkas" type="file" name="unggah_profil_sdm" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
            <span class="t-bantu">Berkas excel</span>
        </div>
        <div class="gspan-4"></div>
        <button class="utama pelengkap" type="submit">UNGGAH</button>
    </form>

    @includeWhen($sesiRangka->has('spanduk') || $sesiRangka->has('pesan') || $errors->any(), 'pemberitahuan')
</div>
@endsection
