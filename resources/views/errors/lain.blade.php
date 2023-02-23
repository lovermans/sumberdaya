@extends('rangka')

@section('isi')
    <div class="kesalahan-internal">
        <p>Periksa : {{ $kesalahan ?? 'Kesalahan tidak diketahui.'}}.</p>
        <a class="utama" href="{{ $urlRangka->route('mulai') }}">OKE</a>
        <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.remove()" style="margin-right:1em">TUTUP</a>
        <div class="bersih"></div>
    </div>
@endsection