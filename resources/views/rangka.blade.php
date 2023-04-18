<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', $appRangka->getLocale()) }}" dir="ltr">
    
<head id="kepala-dokumen">
    <noscript>
        <meta HTTP-EQUIV="refresh" content="0;url='{{ $urlRangka->route('perlu-javascript', [], false) }}'">
    </noscript>

    @include('informasi-meta')
    
</head>

<body data-tematerang="" id="badan-dokumen">
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
    
    @include('menu')
    
    <nav class="tcetak">
        @include('navigasi')
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
        async function cariElemen(el) {
            while ( document.querySelector(el) === null) {
                await new Promise(resolve => requestAnimationFrame(resolve));
            };
            return document.querySelector(el);
        };
        function ringkasTabel (el) {
            el.previousElementSibling.classList.toggle('ringkas');
        };
    </script>
</body>
</html>
