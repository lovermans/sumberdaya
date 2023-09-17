<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', $app->getLocale()) }}" dir="ltr">

<head id="kepala-dokumen">
    @include('informasi-meta')
</head>

<body id="badan-dokumen" data-tematerang="">

    <div>
        <object type="image/svg+xml" data="{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/ikon.svg')) }}" onload="muatIkonSVG(this)"></object>
    </div>

    <div id="sambutan">
        <img src="{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Lambang Perusahaan.webp')) }}"
            title="{{ $app->config->get('app.usaha') }}" alt="{{ $app->config->get('app.usaha') }}">
        <p>
            <b>Memuat Aplikasi, Periksa Koneksi Internet</b>
            <br>
            Penggunaan Terbaik di
            <u>
                <b><a href="https://www.google.com/chrome/" target="_blank" rel="noopener noreferrer">Chrome</a></b>
            </u>
            Browser
        </p>
    </div>

    <a class="skip-navigation tcetak" href="#main" tabindex="0">Langsung Ke Konten Utama</a>

    <input class="tcetak" id="nav" type="checkbox" aria-label="Navigasi">
    <input class="tcetak" id="menu" type="checkbox" aria-label="Menu">
    <input class="tcetak" id="pilih-aplikasi" type="checkbox" aria-label="Aplikasi">
    <input class="tcetak" id="tema" type="checkbox" aria-label="Tema">

    <label class="blok-kanvas" id="nav-kanvas" for="nav"></label>
    <label class="blok-kanvas" id="menu-kanvas" for="menu"></label>
    <label class="blok-kanvas" id="aplikasi-kanvas" for="pilih-aplikasi"></label>

    <div class="mati" id="memuat">
        <div class="progress">
            <div class="indeterminate"></div>
        </div>
    </div>

    <header class="tcetak" id="header-rangka">
        <label id="tbl-nav" for="nav" title="Menu">
            <svg class="on" viewBox="0 0 24 24">
                <use href="#ikonmenu"></use>
            </svg>

            <svg class="off" viewBox="0 0 24 24">
                <use href="#ikontutup"></use>
            </svg>
        </label>

        <a class="isi-xhr" href="{{ $app->url->route('mulai') . '/' }}">
            <img id="logo" src="{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Logo Perusahaan.webp')) }}"
                title="{{ $app->config->get('app.usaha') }}" alt="{{ $app->config->get('app.usaha') }}" loading="lazy">
        </a>

        <label id="pilih-sumber_daya" for="pilih-aplikasi" title="Pilih Aplikasi"></label>

        <label id="tbl-menu" for="menu" title="Akun"></label>

        <label id="tbl-tema" for="tema" title="Ubah Tema">
            <svg viewBox="0 0 24 24">
                <use href="#ikontema"></use>
            </svg>
        </label>

        <div class="bersih"></div>
    </header>

    <aside class="tcetak" id="menu-avatar"></aside>
    <aside class="tcetak" id="menu-aplikasi"></aside>

    <nav class="tcetak">
        <div id="nav-rangka">
            <div id="navigasi-sdm"></div>

            <div class="menu-t">
                <a class="nav-xhr" href="{{ $app->url->route('tentang-aplikasi') }}">
                    <svg viewBox="0 0 24 24">
                        <use href="#ikoninformasi"></use>
                    </svg>
                    Tentang Aplikasi
                </a>
            </div>
        </div>
    </nav>

    <main id="main">
        <section>
            @include('sematan')

            <div class="scroll-margin" id="isi">
                @yield('isi')
            </div>
        </section>
    </main>

    <div class="tcetak" id="brand"></div>
    <div class="tcetak" id="hiasan"></div>

    <footer class="tcetak">
        <section></section>
    </footer>

    @include('mulai-javascript')
</body>

</html>
