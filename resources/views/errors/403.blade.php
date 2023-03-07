@extends('rangka')

@section('isi')
    <div class="kesalahan-internal">
        {{-- <p>{{__('Forbidden')}}.</p> --}}
        <p>{{ $exception->getMessage() ? $exception->getMessage().'. Periksa ijin akses dan durasi sesi' : 'Periksa ijin akses dan durasi sesi' }}.</p>
        <a class="utama" href="{{ $urlRangka->route('mulai') }}" style="margin:0 0 1em 1em">OKE</a>
        <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.remove()">TUTUP</a>
        <div class="bersih"></div>
    </div>
@endsection