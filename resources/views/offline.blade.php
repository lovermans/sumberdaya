@extends('rangka')

@section('isi')
<div id="PWA-Offline">
    <p class="kartu">Tidak ada koneksi internet. Periksa koneksi internet lalu muat halaman : <a
            href="{{ $urlRangka->route('mulai') }}">Hubungkan Aplikasi</a>.</p>
</div>
@endsection