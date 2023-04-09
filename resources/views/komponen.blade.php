@if ($rekRangka->pjax())
<script>
    (async() => {
        while(!window.aplikasiSiap) {
            await new Promise((resolve,reject) =>
            setTimeout(resolve, 1000));
        }

        var avatar = document.getElementById('tbl-menu'),
            pilihSumberdaya = document.getElementById('pilih-sumber_daya'),
            menuSDM = document.getElementById('navigasi-sdm'),
            menuAvatar = document.getElementById('menu-avatar'),
            menuAplikasi = document.getElementById('menu-aplikasi');

        @if ($userRangka)
        if (!avatar.innerHTML.trim()) {
            lemparXHR({
            tujuan : "#tbl-menu",
            tautan : "{{ $urlRangka->route('komponen.avatar', [], false) }}",
            normalview : true
            });

            lemparXHR({
            tujuan : "#menu-avatar",
            tautan : "{{ $urlRangka->route('komponen.menu-avatar', [], false) }}",
            normalview : true
            });
        };

        if (!pilihSumberdaya.innerHTML.trim()) {
            lemparXHR({
            tujuan : "#pilih-sumber_daya",
            tautan : "{{ $urlRangka->route('komponen.pilih-sumberdaya', [], false) }}",
            normalview : true
            });

            lemparXHR({
            tujuan : "#menu-aplikasi",
            tautan : "{{ $urlRangka->route('komponen.menu-aplikasi', [], false) }}",
            normalview : true
            });
        };

        @else
        avatar.innerHTML = "";
        menuAvatar.innerHTML = "";
        pilihSumberdaya.innerHTML = "";
        menuAplikasi.innerHTML = "";
        
        @endif

        @if($userRangka && $rekRangka->routeIs('sdm.*', 'register'))
        if (!menuSDM.innerHTML.trim()) {
            lemparXHR({
            tujuan : "#navigasi-sdm",
            tautan : "{{ $urlRangka->route('komponen.menu-sdm', [], false) }}",
            normalview : true
            });
        };
        @else
        var NavSDM = ['/atur', '/tentang-aplikasi'];
        if (!NavSDM.includes(location.pathname)) menuSDM.innerHTML = "";
        @endif

    })();
</script>
@endif