@extends('rangka')

@section('isi')
<div class="pesan-internal">
    {{-- <p>{{__('Server Error')}}.</p> --}}
    <p><span class="merah">Terdapat Kesalahan Fungsi Aplikasi.</span></p>
    <p>
        Kesalahan Fungsi : {{ $exception?->getMessage() ?? 'Tidak diketahui.'}} Laporkan kesalahan ini ke Personalia
        Pusat.
    </p>
    <a class="utama" href="{{ $urlRangka->route('mulai') }}">OKE</a>
    <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.remove()">TUTUP</a>
    <div class="bersih"></div>
</div>
@endsection