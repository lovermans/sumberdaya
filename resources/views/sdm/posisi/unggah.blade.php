@extends('rangka')

@section('isi')
<div id="posisi_unggah">
    <h4>Unggah Data Pengaturan Jabatan</h4>
    <p  class="kartu">Unduh <a class="isi-xhr" href="{{ $urlRangka->route('sdm.posisi.contoh-unggah') }}" data-rekam="false" data-tujuan="#posisi_sematan_unggah" data-laju="true">contoh</a> excel, isi sesuai petunjuk dalam excel lalu unggah kembali.</p>
    <div id="posisi_sematan_unggah" style="scroll-margin:4em 0 0 0"></div>
    <form id="form_posisi_unggah" class="form-xhr kartu" method="POST" data-laju="true" data-tujuan="#posisi_sematan_unggah" action="{{ $urlRangka->route('sdm.posisi.unggah') }}">
        <input type="hidden" name="_token" value="{{ $rekRangka->session()->token() }}">
        <div class="isian gspan-2">
            <label for="posisi_unggahBerkas">Berkas</label>
            <input id="posisi_unggahBerkas" type="file" name="posisi_unggah" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
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
