@extends('rangka')

@section('isi')
<div id="unggah_sanksi_sdm">
    <h4>Unggah Data Sanksi SDM</h4>
    <p class="kartu">Unduh <a class="isi-xhr" href="{{ $urlRangka->route('sdm.sanksi.contoh-unggah') }}" data-rekam="false" data-tujuan="#sanksi_sematan_unggah" data-laju="true">contoh</a> excel, isi sesuai petunjuk dalam excel lalu unggah kembali.</p>
    <div id="sanksi_sematan_unggah" style="scroll-margin:4em 0 0 0"></div>
    <form id="form_unggah_sanksi_sdm" class="form-xhr kartu" method="POST" data-laju="true" data-tujuan="#sanksi_sematan_unggah" action="{{ $urlRangka->route('sdm.sanksi.unggah') }}">
        <input type="hidden" name="_token" value="{{ $rekRangka->session()->token() }}">
        <div class="isian gspan-2">
            <label for="unggah_sanksirkas">Berkas</label>
            <input id="unggah_sanksirkas" type="file" name="unggah_sanksi_sdm" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
            <span class="t-bantu">Berkas excel</span>
        </div>
        <div class="gspan-4"></div>
        <button class="utama pelengkap" type="submit">UNGGAH</button>
        @if ($rekRangka->pjax())
            <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.parentElement.remove()">TUTUP</a>
        @else
            <a class="isi-xhr sekunder" href="{{$urlRangka->to($rekRangka->session()->get('tautan_perujuk') ?? '/')}}">TUTUP</a>
        @endif
    </form>

    @includeWhen($rekRangka->session()->has('spanduk') || $rekRangka->session()->has('pesan') || $errors->any(), 'pemberitahuan')
</div>
@endsection
