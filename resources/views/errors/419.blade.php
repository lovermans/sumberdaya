@extends('rangka')

@section('isi')
<div class="pesan-internal">
    {{-- <p>{{__('Page Expired')}}.</p> --}}
    <p>Tautan/halaman telah kadaluarsa, cobalah untuk mulai ulang untuk memperbarui.</p>
    <a class="utama" href="{{ $urlRangka->route('mulai') }}">OKE</a>
    <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.remove()">TUTUP</a>
    <div class="bersih"></div>
</div>
@endsection