@if ($app -> request -> user())
<script>
    (async() => {
        while(!window.aplikasiSiap) {
            await new Promise((resolve,reject) =>
            setTimeout(resolve, 1000));
        }

        if (navigator.onLine) {
            import('{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/window-pusher.js'))}}')
            .then(function () {
                import('{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/echo-es.js')) }}')
                .then(({ default: LE }) => {
                    window.Echo = new LE({
                        broadcaster: "{{ $app->config->get('broadcasting.default') }}",
                        key: "{{ $app->config->get('broadcasting.connections.pusher.key') }}",
                        cluster: "{{ $app->config->get('broadcasting.connections.pusher.options.cluster') }}",
                        wsHost: window.location.hostname,
                        authEndpoint: "{{ $app->request->getBasePath() . '/broadcasting/auth' }}",
                        userAuthentication: {
                            endpoint: "{{ $app->request->getBasePath() . '/broadcasting/user-auth' }}",
                            headers: {},
                        },
                        csrfToken: "{{ $app->session->token() }}",
                        encrypted: false,
                        wsPort: 80,
                        disableStats: true,
                        forceTLS: false,
                        enabledTransports: ['ws', 'wss'],
                        disabledTransports: ['sockjs', 'xhr_polling', 'xhr_streaming']
                    });

                    Echo.connector.pusher.connection.bind('connected', function () {
                        soket = Echo.socketId();
                        console.log(soket);
                    });

                    Echo.channel('umum').listen('Umum', function (e) {
                        var wadahSoketUmum = document.getElementById('pemberitahuan-soket');
                        var idWaktuUmum = new Date().toISOString();
                        var idPesanUmum = idWaktuUmum.replace('T', '_').replaceAll(':', '-').replace(/\..*/, '');
                        var pesanSoketUmum = '<div class="pesan-soket"><p><b>' + e.message + '</b></p><button class="tutup-i" id="Umum_' + idPesanUmum + '"><svg viewBox="0 0 24 24"><use href="#ikontutup"></use></svg></button></div>';
                        wadahSoketUmum.prepend(range.createContextualFragment(pesanSoketUmum));
                    });
                });
            });
        }
    })();
</script>
@endif