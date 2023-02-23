@extends('rangka')

@section('isi')
<div id="unggah_profil_sdm">
    <h4>Unggah Data Profil SDM</h4>
    <p class="kartu">Unduh <a class="isi-xhr" href="{{ $urlRangka->route('contoh-unggah') }}" data-rekam="false" data-tujuan="#umum_sematan_unggah" data-laju="true">contoh</a> excel, isi sesuai petunjuk dalam excel lalu unggah kembali.</p>
    <div id="umum_sematan_unggah" style="scroll-margin:4em 0 0 0"></div>
    <form id="form_unggah_profil_sdm" class="form-xhr kartu" method="POST" data-laju="true" data-tujuan="#umum_sematan_unggah" action="{{ $urlRangka->route('unggah') }}">
        <input type="hidden" name="_token" value="{{ $rekRangka->session()->token() }}">
        <div class="isian gspan-2">
            <label for="unggah_profil_sdmBerkas">Berkas</label>
            <input id="unggah_profil_sdmBerkas" type="file" name="unggah_profil_sdm" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
            <span class="t-bantu">Berkas excel</span>
        </div>
        <div class="gspan-4"></div>
        <button class="utama pelengkap" type="submit">UNGGAH</button>
    </form>

    @includeWhen($rekRangka->session()->has('spanduk') || $rekRangka->session()->has('pesan') || $errors->any(), 'pemberitahuan')
</div>
@endsection
