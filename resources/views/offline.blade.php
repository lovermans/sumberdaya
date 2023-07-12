@extends('rangka')

@section('isi')
<div id="PWA-Offline">
    <p class="kartu">Tidak ada koneksi internet. Coba periksa koneksi internet lalu muat ulang halaman <a
            href="{{ $urlRangka->route('mulai') }}">Beranda</a>.</p>
</div>
@endsection