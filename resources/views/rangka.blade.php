<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', $appRangka->getLocale()) }}" dir="ltr">

<head>
    <noscript>
        <meta HTTP-EQUIV="refresh" content="0;url='{{ $urlRangka->route('perlu-javascript', [], false) }}'">
    </noscript>
    @include('informasi-meta')
    <script src="{{ $mixRangka('/interaksi.js') }}"></script>
</head>

<body data-tematerang="">

    <a class="skip-navigation tcetak" href="#main" tabindex="0">Langsung Ke Konten Utama</a>
    <input class="tcetak" type="checkbox" id="nav">
    <input class="tcetak" type="checkbox" id="menu">
    <input class="tcetak" type="checkbox" id="tema">
    <label for="nav" id="nav-kanvas" class="blok-kanvas"></label>
    <label for="menu" id="menu-kanvas" class="blok-kanvas"></label>
    
    <div id="memuat" class="mati">
        <div class="progress">
            <div class="indeterminate"></div>
        </div>
    </div>
    
    <main id="main">
        <section>
            @include('sematan')
            <div id="isi">
                @yield('isi')
            </div>
        </section>
    </main>
    
    @include('menu')
    
    <nav class="tcetak">
        @include('navigasi')
    </nav>

    <div id="brand" class="tcetak"></div>
    <div id="hiasan" class="tcetak"></div>
    <footer class="tcetak">
        <section></section>
    </footer>

</body>
</html>
