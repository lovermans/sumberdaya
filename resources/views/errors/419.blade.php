@extends('rangka')

@section('isi')
    <div class="kesalahan-internal">
        {{-- <p>{{__('Page Expired')}}.</p> --}}
        <p>Tautan kadaluarsa, periksa tautan, data dan atau token yang dikirim.</p>
        <a class="utama" href="{{ $urlRangka->route('mulai') }}">OKE</a>
        <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.remove()" style="margin-right:1em">TUTUP</a>
        <div class="bersih"></div>
    </div>
@endsection