@extends('rangka')

@section('isi')
<div class="pesan-internal">
    {{-- <p>{{__('Too Many Requests')}}.</p> --}}
    <p><span class="merah">Terlalu Banyak Permintaan Akses.</span></p>
    <p>
        <small>
            Demi keamanan kami membatasi jumlah permintaan untuk halaman/data yang diminta. Tunggu beberapa saat sebelum
            memulai kembali.
        </small>
    </p>
    <a class="utama" href="{{ $urlRangka->route('mulai') }}">OKE</a>
    <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.remove()">TUTUP</a>
    <div class="bersih"></div>
</div>
@endsection