@extends('rangka')

@section('isi')
    <div class="kesalahan-internal">
        <p>Periksa : {{ $kesalahan ?? 'Kesalahan tidak diketahui.'}}</p>
        <a class="utama" href="{{ $urlRangka->route('mulai') }}" style="margin:0 0 1em 1em">OKE</a>
        <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.remove()">TUTUP</a>
        <div class="bersih"></div>
    </div>
@endsection