@if ($rekRangka->pjax())
<script>
    !function(){
        (async() => {
            while(!window.aplikasiSiap) {
                await new Promise((resolve,reject) =>
                setTimeout(resolve, 1000));
            }

            var avatar = document.getElementById('tbl-menu'),
                pengaturan = document.getElementById('menu-pengaturan'),
                pilihSumberdaya = document.getElementById('pilih-sumber_daya'),
                menuSDM = document.getElementById('navigasi-sdm'),
                menuAvatar = document.getElementById('menu-avatar'),
                pemberitahuan = document.getElementById('pemberitahuan');

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
            @else
            avatar.innerHTML = "";
            menuAvatar.innerHTML = "";
            @endif

            @if($strRangka->contains($userRangka?->sdm_hak_akses, 'PENGURUS'))
            if (!pengaturan.innerHTML.trim()) {
                lemparXHR({
                tujuan : "#menu-pengaturan",
                tautan : "{{ $urlRangka->route('komponen.menu-pengaturan', [], false) }}",
                normalview : true
                });
            };
            @else
            pengaturan.innerHTML = "";
            @endif

            @if (!$rekRangka->routeIs('mulai') && $userRangka)
            if (!pilihSumberdaya.innerHTML.trim()) {
                lemparXHR({
                tujuan : "#pilih-sumber_daya",
                tautan : "{{ $urlRangka->route('komponen.pilih-sumberdaya', [], false) }}",
                normalview : true
                });
            };
            @else
            pilihSumberdaya.innerHTML = "";
            @endif

            @if($userRangka && !in_array($rekRangka->url(), [$urlRangka->route('akun', ['uuid' => $userRangka?->sdm_uuid]), $urlRangka->route('ubah-akun', ['uuid' => $userRangka?->sdm_uuid])]) && $rekRangka->routeIs('sdm.*', 'register', 'akun', 'ubah-akun'))
            if (!menuSDM.innerHTML.trim()) {
                lemparXHR({
                tujuan : "#navigasi-sdm",
                tautan : "{{ $urlRangka->route('komponen.menu-sdm', [], false) }}",
                normalview : true
                });
            };
            @else
            menuSDM.innerHTML = "";
            @endif

            @if ($rekRangka->routeIs('logout'))
            pemberitahuan.innerHTML = "";
            @endif

        })();
    }();
</script>
@endif