<script>
    (async() => {
        while(!window.aplikasiSiap) {
            await new Promise((resolve,reject) =>
            setTimeout(resolve, 1000));
        }

        if (navigator.onLine) {
            @if ($app->request->user())
            if (!document.getElementById("tbl-menu")?.innerHTML.trim()) {
                lemparXHR({
                tujuan : "#tbl-menu",
                tautan : "{!! $app->url->route('komponen', ['komponen' => 'menu', 'fragment' => 'avatar']) !!}",
                normalview : true
                });

                lemparXHR({
                tujuan : "#menu-avatar",
                tautan : "{!! $app->url->route('komponen', ['komponen' => 'menu', 'fragment' => 'menu-avatar']) !!}",
                normalview : true
                });
            };

            if (!document.getElementById("pilih-sumber_daya")?.innerHTML.trim()) {
                lemparXHR({
                tujuan : "#pilih-sumber_daya",
                tautan : "{!! $app->url->route('komponen', ['komponen' => 'menu', 'fragment' => 'pilih-sumber_daya']) !!}",
                normalview : true
                });

                lemparXHR({
                tujuan : "#menu-aplikasi",
                tautan : "{!! $app->url->route('komponen', ['komponen' => 'menu', 'fragment' => 'menu-aplikasi']) !!}",
                normalview : true
                });
            };

            if (!document.getElementById("sematan_websoket")?.innerHTML.trim()) {
                lemparXHR({
                tujuan : "#sematan_websoket",
                tautan : "{!! $app->url->route('komponen', ['komponen' => 'koneksi-websoket']) !!}",
                normalview : true
                });
            };

            @else
            document.getElementById("pilih-sumber_daya")?.replaceChildren();
            document.getElementById("menu-aplikasi")?.replaceChildren();
            document.getElementById("tbl-menu")?.replaceChildren();
            document.getElementById("menu-avatar")?.replaceChildren();
            window.Echo?.disconnect();
            document.getElementById("sematan_websoket")?.replaceChildren();
            
            @endif

            @if($app->request->user() && $app->request->routeIs('sdm.*', 'register'))
            if (!document.getElementById("navigasi-sdm")?.innerHTML.trim()) {
                lemparXHR({
                tujuan : "#navigasi-sdm",
                tautan : "{!! $app->url->route('komponen', ['komponen' => 'sdm.navigasi']) !!}",
                normalview : true
                });
            };
            @else
            document.getElementById("navigasi-sdm")?.replaceChildren();

            @endif
        }
    })();
</script>