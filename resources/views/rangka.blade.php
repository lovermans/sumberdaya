<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', $appRangka->getLocale()) }}" dir="ltr">

<head id="kepala-dokumen">
    <noscript>
        <meta HTTP-EQUIV="refresh" content="0;url='{{ $urlRangka->route('perlu-javascript') }}'">
    </noscript>

    @include('informasi-meta')

</head>

<body data-tematerang="" id="badan-dokumen">
    <div id="sambutan">
        <img src="{{ $urlRangka->asset($mixRangka('/images/Lambang Perusahaan.webp')) }}"
            alt="{{ $confRangka->get('app.usaha') }}" title="{{ $confRangka->get('app.usaha') }}">
        <p>
            <b>Memuat Aplikasi, Periksa Koneksi Internet</b> <br>
            Penggunaan Terbaik di <u><b><a href="https://www.google.com/chrome/" target="_blank"
                        rel="noopener noreferrer">Chrome</a></b></u> Browser
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
        <section>
            <label for="nav" id="tbl-nav" title="Menu">
                <svg class="on" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#menu' }}"
                        xmlns:xlink="http://www.w3.org/1999/xlink">
                    </use>
                </svg>

                <svg class="off" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#tutup' }}"
                        xmlns:xlink="http://www.w3.org/1999/xlink">
                    </use>
                </svg>
            </label>

            <a class="isi-xhr" href="{{ $urlRangka->route('mulai')
                }}">
                <img id="logo" src="{{ $urlRangka->asset($mixRangka('/images/Logo Perusahaan.webp')) }}"
                    title="{{ $confRangka->get('app.usaha') }}" alt="{{ $confRangka->get('app.usaha') }}"
                    loading="lazy"></a>

            <label for="pilih-aplikasi" id="pilih-sumber_daya" onclick="" title="Pilih Aplikasi"></label>
            <label for="menu" id="tbl-menu" onclick="" title="Akun"></label>
            <label for="tema" id="tbl-tema" onclick="" title="Ubah Tema">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#tema' }}"
                        xmlns:xlink="http://www.w3.org/1999/xlink">
                    </use>
                </svg>
            </label>

            <div class="bersih"></div>
        </section>
    </header>

    <aside id="menu-avatar" class="tcetak"></aside>
    <aside id="menu-aplikasi" class="tcetak"></aside>

    <nav id="nav-rangka" class="tcetak">
        <div id="navigasi-sdm">
            @includeWhen(!$rekRangka->pjax(), 'sdm.navigasi')
        </div>

        <div class="menu-t">
            <a @class(['nav-xhr', 'aktif'=> $rekRangka->routeIs('tentang-aplikasi')]) href="{{
                $urlRangka->route('tentang-aplikasi') }}">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#informasi' }}"
                        xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg>
                Tentang Aplikasi
            </a>
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

    {{-- <script defer type="module" src="{{ $mixRangka('/interaksi.js') }}"></script> --}}

    <script>
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
            
            (async() => {
                while(!window.aplikasiSiap) {
                    await new Promise((resolve,reject) =>
                    setTimeout(resolve, 1000));
                }

                document.getElementById('sambutan').remove();
            
                
                /* lemparXHR({
                    tujuan : "#pilih-sumber_daya",
                    tautan : "{!! $urlRangka->route('komponen', ['komponen' => 'menu', 'fragment' => 'pilih-sumber_daya']) !!}",
                    normalview : true
                });
                lemparXHR({
                    tujuan : "#tbl-menu",
                    tautan : "{!! $urlRangka->route('komponen', ['komponen' => 'menu', 'fragment' => 'avatar']) !!}",
                    normalview : true
                });
                lemparXHR({
                    tujuan : "#menu-avatar",
                    tautan : "{!! $urlRangka->route('komponen', ['komponen' => 'menu', 'fragment' => 'menu-avatar']) !!}",
                    normalview : true
                });
                lemparXHR({
                    tujuan : "#menu-aplikasi",
                    tautan : "{!! $urlRangka->route('komponen', ['komponen' => 'menu', 'fragment' => 'menu-aplikasi']) !!}",
                    normalview : true
                }); */

                
                if (location.href == "{{ $urlRangka->route('mulai').'/' }}" && navigator.onLine) {
                    lemparXHR({
                        tujuan : "#isi",
                        tautan : "{!! $urlRangka->route('mulai-aplikasi', [ 'aplikasivalet' => $confRangka->get('app.aplikasivalet')]) !!}",
                        normalview : true
                    });
                };
                
            })();
        });
        async function cariElemen(el) {
            while ( document.querySelector(el) === null) {
                await new Promise(resolve => requestAnimationFrame(resolve));
            };
            return document.querySelector(el);
        };
        function ringkasTabel (el) {
            el.previousElementSibling.classList.toggle('ringkas');
        };
        if (!navigator.onLine) {
            document.getElementById("isi").innerHTML = "<p class='kartu'>Tidak ada koneksi internet. Coba periksa koneksi internet lalu muat ulang halaman <a href='/'>Beranda</a>.</p>";
        };
        window.onload = function () {
            if ('serviceWorker' in navigator && window.location.protocol === 'https:' && window.self == window.top && navigator.onLine) {
                let updated = false;
                let activated = false;
                navigator.serviceWorker.register("{{ $urlRangka->route('service-worker') .'?'. filemtime(resource_path('views/service-worker.blade.php')) }}")
                .then(regitration => {
                    regitration.addEventListener("updatefound", () => {
                        const worker = regitration.installing;
                        worker.addEventListener('statechange', () => {
                            console.log({ state: worker.state });
                            if (worker.state === "activated") {
                                activated = true;
                                checkUpdate();
                            }
                        });
                    });
                });
                
                navigator.serviceWorker.addEventListener('controllerchange', () => {
                    console.log({ state: "updated" });
                    updated = true;
                    checkUpdate();
                });

                function checkUpdate() {
                    if (activated && updated) {
                        console.log("Application was updated refreshing the page...");
                        window.location.reload();
                    }
                }
            };
        }
    </script>
</body>

</html>