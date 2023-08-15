<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', $app->getLocale()) }}" dir="ltr">

<head id="kepala-dokumen">
    <noscript>
        <meta HTTP-EQUIV="refresh" content="0;url='{{ $app->url->route('perlu-javascript') }}'">
    </noscript>

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

        <a class="isi-xhr" href="{{ $app->url->route('mulai')
                }}">
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
            <div id="navigasi-sdm">
                @includeWhen(!$app->request->pjax(), 'sdm.navigasi')
            </div>

            <div class="menu-t">
                <a @class(['nav-xhr', 'aktif'=> $app->request->routeIs('tentang-aplikasi')]) href="{{
                    $app->url->route('tentang-aplikasi') }}">
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

    <script>
        async function cariElemen(el) {
            while ( document.querySelector(el) === null) {
                await new Promise(resolve => requestAnimationFrame(resolve));
            };
            return document.querySelector(el);
        };
        function ringkasTabel (el) {
            el.previousElementSibling.classList.toggle('ringkas');
        };
        function muatSlimSelect (data) {
            if (!window.SlimSelect) {
                import('{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/slimselect-es.js')) }}').then(({ default: SS }) => {
                    window.SlimSelect = SS;
                    new SlimSelect(data);
                });
            } else {
                window.SlimSelect 
                ? new SlimSelect(data) 
                : (function () {
                    alert('Terjadi kesalahan dalam memuat tombol pilihan. Modul pemrosesan tombol pilihan tidak ditemukan. Harap hubungi Personalia Pusat.');
                })();
            };
        };
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

            if (location.href == "{{ $app->url->route('mulai').'/' }}" && !navigator.onLine) {
                document.getElementById("isi").innerHTML = "<p class='kartu'>Tidak ada koneksi internet. Periksa koneksi internet lalu muat halaman : <a href='{{ $app->url->route('mulai') }}'>Hubungkan Aplikasi</a>.</p>";
            };

            (function () {
                if ('serviceWorker' in navigator && window.location.protocol === 'https:' && window.self == window.top && navigator.onLine) {
                    let updated = false;
                    let activated = false;
                    navigator.serviceWorker.register('{{ $app->request->getBasePath() . '/service-worker.js' }}')
                        .then(registration => {
                            registration.addEventListener("updatefound", () => {
                                const worker = registration.installing;
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
                        updated = true;
                        checkUpdate();
                    });

                    function checkUpdate() {
                        if (activated && updated) {
                            window.location.reload();
                        }
                    }
                };
            })();
            
            (async() => {
                while(!window.aplikasiSiap()) {
                    await new Promise((resolve,reject) =>
                    setTimeout(resolve, 1000));
                }

                lemparXHR({
                    tujuan : "#ikonSVG",
                    tautan : "{!! $app->url->asset($app->make('Illuminate\Foundation\Mix')('/ikon.svg')) !!}",
                    normalview : true
                });
                
                if (location.href == "{{ $app->url->route('mulai').'/' }}" && navigator.onLine) {
                    lemparXHR({
                        tujuan : "#isi",
                        tautan : "{!! $app->url->route('mulai-aplikasi', [ 'aplikasivalet' => $app->config->get('app.aplikasivalet')]) !!}",
                        normalview : true
                    });
                };
                
                document.getElementById('sambutan').remove();
            })();
        });
        if (!window.Echo) {
            import('{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/window-pusher.js')) }}').then(
                function () {
                    import('{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/echo-es.js')) }}').then(({ default: LE }) => {
                        window.Echo = new LE({
                            broadcaster: "{{ $app->config->get('broadcasting.default') }}",
                            key: "{{ $app->config->get('broadcasting.connections.pusher.key') }}",
                            cluster: "{{ $app->config->get('broadcasting.connections.pusher.cluster') }}",
                            wsHost: 127.0.0.1, // Your domain
                            encrypted: false,
                            wssPort: 443, // Https port
                            disableStats: true, // Change this to your liking this disables statistics
                            forceTLS: true,
                            enabledTransports: ['ws'],
                            disabledTransports: ['sockjs', 'xhr_polling', 'xhr_streaming']
                        });
                        console.log(Echo);
                    });
                }
            )
        } else {
            (function () {
                alert('Terjadi kesalahan dalam memuat soket pilihan. Modul pemrosesan soket pilihan tidak ditemukan. Harap hubungi Personalia Pusat.');
            })();
        };
    </script>
</body>

</html>