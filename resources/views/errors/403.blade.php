@extends('rangka')

@section('isi')
<div class="pesan-internal">
    {{-- <p>{{__('Forbidden')}}.</p> --}}
    <p><span class="merah">Akses Dilarang.</span></p>
    <p>
        <small>
            {{ $exception->getMessage()
            ? $exception->getMessage() . ' Pengguna tidak memiliki wewenang menjalankan fungsi ini, atau bisa jadi
            durasi sesi masuk sudah habis. Cobalah untuk masuk kembali atau minta akses menjalankan fungsi ini ke
            Personalia Pusat' : 'Pengguna tidak memiliki wewenang menjalankan fungsi ini, atau bisa jadi durasi sesi
            masuk sudah habis. Cobalah untuk masuk kembali atau minta akses menjalankan fungsi ini ke Personalia Pusat.'
            }}
        </small>
    </p>
    <a class="utama" href="{{ $app->url->route('mulai') }}">OKE</a>
    <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.remove()">TUTUP</a>
    <div class="bersih"></div>
</div>
@endsection