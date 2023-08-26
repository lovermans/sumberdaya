<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', $app->getLocale()) }}" dir="ltr">

<head id="kepala-dokumen">
    @include('informasi-meta')
</head>

<body data-tematerang="" id="badan-dokumen">
    <div id="ikonSVG"></div>

    <div id="sambutan">
        <img src="{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Lambang Perusahaan.webp')) }}"
            alt="{{ $app->config->get('app.usaha') }}" title="{{ $app->config->get('app.usaha') }}">
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

    <input class="tcetak" type="checkbox" id="nav" aria-label="Navigasi">
    <input class="tcetak" type="checkbox" id="menu" aria-label="Menu">
    <input class="tcetak" type="checkbox" id="pilih-aplikasi" aria-label="Aplikasi">
    <input class="tcetak" type="checkbox" id="tema" aria-label="Tema">

    <label for="nav" id="nav-kanvas" class="blok-kanvas"></label>
    <label for="menu" id="menu-kanvas" class="blok-kanvas"></label>
    <label for="pilih-aplikasi" id="aplikasi-kanvas" class="blok-kanvas"></label>

    <div id="memuat" class="mati">
        <div class="progress">
            <div class="indeterminate"></div>
        </div>
    </div>

    <header id="header-rangka" class="tcetak">
        <label for="nav" id="tbl-nav" title="Menu">
            <svg class="on" viewBox="0 0 24 24">
                <use href="#ikonmenu"></use>
            </svg>

            <svg class="off" viewBox="0 0 24 24">
                <use href="#ikontutup"></use>
            </svg>
        </label>

        <a class="isi-xhr" href="{{ $app->url->route('mulai') . '/' }}">
            <img id="logo"
                src="{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Logo Perusahaan.webp')) }}"
                title="{{ $app->config->get('app.usaha') }}" alt="{{ $app->config->get('app.usaha') }}"
                loading="lazy"></a>

        <label for="pilih-aplikasi" id="pilih-sumber_daya" onclick="" title="Pilih Aplikasi"></label>

        <label for="menu" id="tbl-menu" onclick="" title="Akun"></label>

        <label for="tema" id="tbl-tema" onclick="" title="Ubah Tema">
            <svg viewBox="0 0 24 24">
                <use href="#ikontema"></use>
            </svg>
        </label>

        <div class="bersih"></div>
    </header>

    <aside id="menu-avatar" class="tcetak"></aside>
    <aside id="menu-aplikasi" class="tcetak"></aside>

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

            <div id="isi" class="scroll-margin">
                @yield('isi')
            </div>
        </section>
    </main>

    <div id="brand" class="tcetak"></div>
    <div id="hiasan" class="tcetak"></div>

    <footer class="tcetak">
        <section></section>
    </footer>

    @include('mulai-javascript')
</body>

</html>