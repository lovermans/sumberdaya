<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', $appRangka->getLocale()) }}" dir="ltr">
    
<head>
    <noscript>
        <meta HTTP-EQUIV="refresh" content="0;url='{{ $urlRangka->route('perlu-javascript', [], false) }}'">
    </noscript>

    @include('informasi-meta')
    
    <link href="{{ $mixRangka('/tampilan.css') }}" rel="stylesheet">
    
    {{-- <script>
        !function(){
            var muatCSS = document.createElement('link');
            muatCSS.href = '{{ $mixRangka('/tampilan.css') }}';
            muatCSS.rel = 'stylesheet';
            document.head.append(muatCSS);
        }();
    </script> --}}

    {{-- <script type="module" defer>
        import styles from '{{ $mixRangka('/tampilan.css') }}' assert { type: "css" };
        document.adoptedStyleSheets = [styles];
    </script> --}}
</head>

<body data-tematerang="">
    <div id="sambutan">
        <img src="{{ $mixRangka('/images/Lambang Perusahaan.webp') }}" alt="{{ $confRangka->get('app.usaha') }}" title="{{ $confRangka->get('app.usaha') }}">
        <p>
            <b>Memulai Aplikasi</b> <br>
            Penggunaan Terbaik di <u><b><a href="https://www.google.com/chrome/" target="_blank" rel="noopener noreferrer">Chrome</a></b></u> Browser
        </p>
    </div>
    
    <a class="skip-navigation tcetak" href="#main" tabindex="0">Langsung Ke Konten Utama</a>
    
    <input class="tcetak" type="checkbox" id="nav" aria-label="Navigasi">
    
    <input class="tcetak" type="checkbox" id="menu" aria-label="Menu">
    
    <input class="tcetak" type="checkbox" id="tema" aria-label="Tema">
    
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
            
            <div id="isi" class="scroll-margin">
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

    {{-- <script defer type="module" src="{{ $mixRangka('/interaksi.js') }}"></script> --}}
    
    <script>
        !function(){
            window.addEventListener('DOMContentLoaded', function () {
                var tema = document.getElementById('tema'),
                    halaman = document.body,
                    muatJS = document.createElement('script');

                tema.checked = 'true' === localStorage.getItem('tematerang');
                halaman.setAttribute('data-tematerang', 'true' === localStorage.getItem('tematerang'));
                tema.addEventListener('change', (function (e) {
                    localStorage.setItem('tematerang', e.currentTarget.checked);
                    halaman.setAttribute('data-tematerang', e.currentTarget.checked);
                }));
                
                muatJS.src = '{{ $mixRangka('/interaksi.js') }}';
                muatJS.type = 'module';
                muatJS.defer = true;
                document.head.append(muatJS);

                (async() => {
                    while(!window.aplikasiSiap) {
                        await new Promise((resolve,reject) =>
                        setTimeout(resolve, 1000));
                    }
                
                    document.getElementById('sambutan')?.remove();
                })();
            });
        }();
    </script>
</body>
</html>
