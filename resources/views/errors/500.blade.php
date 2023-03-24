@extends('rangka')

@section('isi')
    <div class="kesalahan-internal">
        {{-- <p>{{__('Server Error')}}.</p> --}}
        <p>Kesalahan Penanganan : {{ $exception?->getMessage() ?? 'Kesalahan tidak diketahui.'}}</p>
        <a class="utama" href="{{ $urlRangka->route('mulai') }}">OKE</a>
        <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.remove()">TUTUP</a>
        <div class="bersih"></div>
    </div>
@endsection