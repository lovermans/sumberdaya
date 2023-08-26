<script>
    async function cariElemen(el) {
        while (document.querySelector(el) === null) {
            await new Promise(resolve => requestAnimationFrame(resolve));
        };
        return document.querySelector(el);
    };

    function ringkasTabel(el) {
        el.previousElementSibling.classList.toggle('ringkas');
    };

    function muatSlimSelect(data) {
        if (!window.SlimSelect) {
            import('{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/slimselect-es.js')) }}')
            .then(({ default: SS }) => {
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

        (async () => {
            while (!window.aplikasiSiap) {
                await new Promise((resolve, reject) =>
                setTimeout(resolve, 1000));
            }

            lemparXHR({
                tujuan: "#ikonSVG",
                tautan: "{!! $app->url->asset($app->make('Illuminate\Foundation\Mix')('/ikon.svg')) !!}",
                normalview: true
            });

            document.getElementById('sambutan').remove();

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
        })();
    });
</script>