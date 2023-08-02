@extends('rangka')

@section('isi')
<div class="pesan-internal">
    {{-- <p>{{__('Not Found')}}.</p> --}}
    <p><span class="merah">Tidak Ditemukan.</span></p>
    <p>
        <small>
            {{ $exception->getMessage() ?: 'Permintaan Halaman/Data tidak ditemukan. Coba periksa permintaan.' }}
        </small>
    </p>
    <a class="utama" href="{{ $urlRangka->route('mulai') }}">OKE</a>
    <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.remove()">TUTUP</a>
    <div class="bersih"></div>
</div>
@endsection