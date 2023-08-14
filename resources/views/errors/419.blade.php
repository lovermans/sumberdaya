@extends('rangka')

@section('isi')
<div class="pesan-internal">
    {{-- <p>{{__('Page Expired')}}.</p> --}}
    <p><span class="merah">Permintaan Kadaluarsa.</span></p>
    <p>
        <small>
            Masa berlaku permintaan halaman/data telah melewati batas waktu demi keamanan. Cobalah muat ulang
            halaman/permintaan.
        </small>
    </p>
    <a class="utama" href="{{ $app->url->route('mulai') }}">OKE</a>
    <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.remove()">TUTUP</a>
    <div class="bersih"></div>
</div>
@endsection