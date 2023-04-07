@extends('rangka')

@section('isi')
    <div class="pesan-internal">
        {{-- <p>{{__('Not Found')}}.</p> --}}
        <p>{{ $exception->getMessage() ?: 'Data/halaman/dokumen/tautan tidak ditemukan.' }}</p>
        <a class="utama" href="{{ $urlRangka->route('mulai') }}">OKE</a>
        <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.remove()">TUTUP</a>
        <div class="bersih"></div>
    </div>
@endsection
