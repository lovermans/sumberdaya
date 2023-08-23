@extends('rangka')

@section('isi')
<div class="pesan-internal">
    {{-- <p>{{__('Too Many Requests')}}.</p> --}}
    <div class="judul-form">
        <h4 class="form">Terlalu Banyak Permintaan Akses.</h4>

        <a class="tutup-i">
            <svg viewbox="0 0 24 24">
                <use href="#ikontutup"></use>
            </svg>
        </a>
    </div>

    <p>
        <small>
            Demi keamanan kami membatasi jumlah permintaan untuk halaman/data yang diminta. Tunggu beberapa saat sebelum
            memulai kembali.
        </small>
    </p>

    <div class="pesan-internal-tindaklanjut">
        <a class="sekunder" target="_blank" rel="noopener noreferrer"
            href="https://wa.me/6282234280128?text=Halaman%20%3A%20{!! $app->url->full() !!}.
            %0AKode%20%3A%20{{ $exception->getStatusCode() }}.
            %0APengguna%20%3A%20{{ $app->request->user()?->sdm_no_absen }}.
            %0A%0ATuliskan%20kronologi%20kesalahan%2C%20pesan%20kesalahan%20ataupun%20screenshoot%20di%20bawah%20ini%20%3A%0A%0A">
            KIRIM WHATSAPP
        </a>

        <a class="utama" href="{{ $app->url->route('mulai') }}">BERANDA</a>
    </div>
</div>
@endsection