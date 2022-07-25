@extends('rangka')
@section('isi')
    <h2>Aplikasi</h2>
    <div class="kartu">
        <p>Merupakan aplikasi web sumber terbuka sederhana yang dibangun bertujuan untuk memudahkan pengelolaan sumber daya
            usaha secara umum di Indonesia.</p>
    </div>
    <h2>Fitur</h2>
    <div class="kartu">
        <ol>
            <li>Kelola Sumber Daya Manusia.</li>
            <li>Kelola Sumber Daya Lainnya (dalam tahap pengembangan dan dukungan).</li>
        </ol>
    </div>
    <h2>Sumber Kode Terbuka</h2>
    <div class="kartu">
        <p><a href="https://github.com/lovermans/sumberdaya" target="_blank" rel="noopener noreferrer">Github</a>.</p>
    </div>
    <h2>Lisensi</h2>
    <div class="kartu">
        <p><a href="https://opensource.org/licenses/MIT" target="_blank" rel="noopener noreferrer">MIT</a>.</p>
    </div>
    @includeWhen(session()->has('spanduk') || session()->has('pesan') || $errors->any(), 'pemberitahuan')
@endsection
