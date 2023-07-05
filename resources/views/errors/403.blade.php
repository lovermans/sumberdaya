@extends('rangka')

@section('isi')
<div class="pesan-internal">
    {{-- <p>{{__('Forbidden')}}.</p> --}}
    <p>{{ $exception->getMessage() ? $exception->getMessage().' Bisa jadi akses dibatasi untuk menjalankan fungsi ini
        atau durasi akses masuk sudah habis. Cobalah minta akses ke Administrator atau masuk kembali' : 'Bisa jadi akses
        dibatasi untuk menjalankan fungsi ini atau durasi akses masuk sudah habis. Cobalah minta akses ke Administrator
        atau masuk kembali.' }}</p>
    <a class="utama" href="{{ $urlRangka->route('mulai') }}">OKE</a>
    <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.remove()">TUTUP</a>
    <div class="bersih"></div>
</div>
@endsection