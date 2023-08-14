@extends('rangka')

@section('isi')
<div class="pesan-internal">
    {{-- <p>{{__('Service Unavailable')}}.</p> --}}
    <p><span class="merah">Aplikasi Sedang Tidak Dapat Digunakan.</span></p>
    <p>
        <small>
            Personalia Pusat bisa jadi sedang melakukan perawatan, perbaikan maupun pengembangan Aplikasi. Harap
            menunggu.
        </small>
    </p>
    <a class="utama" href="{{ $app->url->route('mulai') }}">OKE</a>
    <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.remove()">TUTUP</a>
    <div class="bersih"></div>
</div>
@endsection