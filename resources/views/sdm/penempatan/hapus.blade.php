@extends('rangka')

@section('isi')
<div id="penempatan_sdm_hapus">
    <h4>Hapus Data Penempatan SDM</h4>
    <form id="form_penempatanSDMHapus" class="form-xhr kartu" method="POST" action="{{ $urlRangka->current() }}">
        <input type="hidden" name="_token" value="{{ $rekRangka->session()->token() }}">
        
        <div class="isian gspan-4">
            <p>Yakin menghapus data penempatan : {{$penem->sdm_no_absen}} - {{$penem->sdm_nama}} - {{strtoupper($dateRangka->make($penem->penempatan_mulai)?->translatedFormat('d F Y'))}} - {{$penem->penempatan_lokasi}} - {{$penem->penempatan_kontrak}} - {{$penem->penempatan_ke}} ?</p>
            <label for="alasan_hapus_penempatan">Alasan Penghapusan</label>
            <textarea id="alasan_hapus_penempatan" name="alasan" cols="3" required>{{ $rekRangka->old('alasan') }}</textarea>
            <span class="t-bantu">Isi alasan penghapusan data</span>
        </div>
        <div class="gspan-4"></div>
        <button class="utama pelengkap" type="submit">SIMPAN</button>
        @if ($rekRangka->pjax())
            <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.parentElement.remove()">TUTUP</a>
        @else
            <a class="isi-xhr sekunder" href="{{$urlRangka->to($rekRangka->session()->get('tautan_perujuk') ?? '/')}}">TUTUP</a>
        @endif
    </form>
    
    <script>
        formatIsian('#form_penempatanSDMHapus .isian textarea');
    </script>
    
    @includeWhen($rekRangka->session()->has('spanduk') || $rekRangka->session()->has('pesan') || $errors->any(), 'pemberitahuan')
</div>
@endsection
