@extends('rangka')

@section('isi')
    <div id="tentangAplikasi">
        <h2>Aplikasi</h2>

        <div class="kartu">
            <p>
                Merupakan aplikasi web sumber terbuka sederhana yang dibangun bertujuan untuk memudahkan pengelolaan
                sumberdaya usaha secara umum di Indonesia.
            </p>
        </div>

        <h2>Fitur</h2>

        <div class="kartu">
            <ol>
                <li>Kelola Sumber Daya Manusia.</li>
                <li>Kelola Sumber Daya Lainnya (dalam tahap pengembangan dan dukungan).</li>
            </ol>
        </div>

        <h2>Kontribusi Sumber Kode</h2>

        <div class="kartu">
            <p>
                <a href="https://github.com/lovermans/sumberdaya" target="_blank" rel="noopener noreferrer">lovermans/sumberdaya</a>.
            </p>
        </div>

        <h2>Perangkat Uji Pengembangan Lokal</h2>

        <div class="kartu">
            <ul>
                <li>Sistem Operasi : Windows 10 64 bit Intel Core i3 8 GB RAM.</li>
                <li>Basis Bahasa : {{ 'PHP ' . PHP_VERSION }}.</li>
                <li>Kerangka : {{ 'Laravel ' . $app->version() }}.</li>
                <li>Basis Data : MariaDB 10.8.3.</li>
                <li>Webserver : Nginx 1.24.0.</li>
                <li>Pengelola Rantai Paket : Composer 2.5.5.</li>
                <li>Pengelola Aset Paket Antarmuka Pengguna : Node 16.14.0 + NPM 9.8.0 + Laravel Mix 6.0.43.</li>
                <li>Paket Tambahan :
                    <ul>
                        <li>
                            Laravel Breeze :
                            <a href="https://github.com/laravel/breeze" target="_blank" rel="noopener noreferrer">laravel/breeze</a>.
                        </li>

                        <li>
                            PHP Spreadsheet :
                            <a href="https://github.com/PHPOffice/PhpSpreadsheet" target="_blank" rel="noopener noreferrer">PHPOffice/PhpSpreadsheet</a>.
                        </li>

                        <li>
                            PHP Word :
                            <a href="https://github.com/PHPOffice/PHPWord" target="_blank" rel="noopener noreferrer">PHPOffice/PHPWord</a>.
                        </li>

                        <li>
                            PHP QRCode :
                            <a href="https://github.com/aferrandini/PHPQRCode" target="_blank" rel="noopener noreferrer">aferrandini/PHPQRCode</a>.
                        </li>

                        <li>
                            Pusher Websocket SDK-PHP :
                            <a href="https://github.com/pusher/pusher-http-php" target="_blank" rel="noopener noreferrer">pusher/pusher-http-php</a>.
                        </li>
                    </ul>
                </li>

                <li>Aset Paket Antarmuka Pengguna :
                    <ul>
                        <li>
                            Slim Select JS :
                            <a href="https://github.com/brianvoe/slim-select" target="_blank" rel="noopener noreferrer">brianvoe/slim-select</a>.
                        </li>

                        <li>
                            Laravel Echo JS :
                            <a href="https://github.com/laravel/echo" target="_blank" rel="noopener noreferrer">laravel/echo</a>.
                        </li>

                        <li>
                            Pusher JS :
                            <a href="https://github.com/pusher/pusher-js" target="_blank" rel="noopener noreferrer">pusher/pusher-js</a>.
                        </li>
                    </ul>
                </li>

                <li>
                    Server <i>Websocket</i> Lokal :
                    <a href="https://github.com/soketi/soketi" target="_blank" rel="noopener noreferrer">soketi/soketi</a>.
                </li>

                <li>
                    <i>DevOps</i> Lokal :
                    <a href="https://github.com/leokhoa/laragon" target="_blank" rel="noopener noreferrer">leokhoa/laragon</a>.
                </li>
            </ul>

            <p>
                Penggunaan terbaik aplikasi di
                <i>
                    <a href="https://www.google.com/chrome/" target="_blank" rel="noopener noreferrer">
                        Chrome Browser
                    </a>
                </i>
                oleh Google.
            </p>
        </div>

        <h2>Status Aplikasi</h2>

        <div class="kartu">
            <p>
                Uji Coba Versi 0.<br>
                <a href="//www.kepuhkencanaarum.com/" target="_blank" rel="noopener noreferrer">PT. Kepuh Kencana Arum</a>
                {{ '@ ' . date('Y') }}.<br>
                Email : <a href="mailto:kka.hrga.adm@gmail.com" target="_blank" rel="noopener noreferrer">kka.hrga.adm@gmail.com</a>.<br>
                Whatsapp : <a href="https://wa.me/6282234280128" target="_blank" rel="noopener noreferrer">6282234280128</a>.<br>
            </p>
        </div>

        <div class="pintasan tcetak">
            <a href="#" title="Kembali Ke Atas" onclick="event.preventDefault();window.scrollTo(0,0)">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonpanahatas"></use>
                </svg>
            </a>

            <a class="isi-xhr" data-tn="true" href="{{ $app->url->route('mulai') . '/' }}">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonrumah"></use>
                </svg>
            </a>
        </div>

        <script nonce="{{ $app->request->session()->get('sesiNonce') }}">
            document.querySelector("nav a[href='{{ $app->url->route('tentang-aplikasi') }}']").classList.add("aktif");
        </script>

        @include('pemberitahuan')
        @include('komponen')
    </div>
@endsection
