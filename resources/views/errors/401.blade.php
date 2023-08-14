@extends('rangka')

@section('isi')
<div class="pesan-internal">
    {{-- <p>{{__('Unauthorized')}}.</p> --}}
    <p><span class="merah">Perlu Akses Masuk.</span></p>
    <p>
        <small>
            {{ $exception->getMessage() ? $exception->getMessage() . ' Bisa jadi durasi sesi masuk sudah habis.
            Cobalah masuk kembali.' : 'Bisa jadi durasi sesi masuk sudah habis.
            Cobalah masuk kembali.' }}
        </small>
    </p>
    <a class="utama" href="{{ $app->url->route('mulai') }}">OKE</a>
    <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.remove()">TUTUP</a>
    <div class="bersih"></div>
</div>
@endsection