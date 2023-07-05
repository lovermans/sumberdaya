@extends('rangka')

@section('isi')
<div class="pesan-internal">
    {{-- <p>{{__('Unauthorized')}}.</p> --}}
    <p>{{ $exception->getMessage() ?: 'Bisa jadi belum mendapat akses masuk atau durasi akses masuk sudah habis. Cobalah
        masuk kembali.' }}</p>
    <a class="utama" href="{{ $urlRangka->route('mulai') }}">OKE</a>
    <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.remove()">TUTUP</a>
    <div class="bersih"></div>
</div>
@endsection