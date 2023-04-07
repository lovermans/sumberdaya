@extends('rangka')

@section('isi')
    <div class="pesan-internal">
        {{-- <p>{{__('Forbidden')}}.</p> --}}
        <p>{{ $exception->getMessage() ? $exception->getMessage().' Periksa ijin akses dan durasi sesi' : 'Periksa ijin akses dan durasi sesi.' }}</p>
        <a class="utama" href="{{ $urlRangka->route('mulai') }}">OKE</a>
        <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.remove()">TUTUP</a>
        <div class="bersih"></div>
    </div>
@endsection