@extends('rangka')

@section('isi')
    <div class="pesan-internal">
        {{-- <p>{{__('Too Many Requests')}}.</p> --}}
        <p>Terlalu banyak percobaan akses, coba lagi nanti.</p>
        <a class="utama" href="{{ $urlRangka->route('mulai') }}">OKE</a>
        <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.remove()">TUTUP</a>
        <div class="bersih"></div>
    </div>
@endsection