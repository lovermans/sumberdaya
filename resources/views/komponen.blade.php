@if ($userRangka && $rekRangka->pjax())
<script>
    !function(){
        (async() => {
            while(!window.aplikasiSiap) {
                await new Promise((resolve,reject) =>
                setTimeout(resolve, 1000));
            }

            var avatar = document.getElementById('tbl-menu')?.innerHTML.trim();
            if (!avatar) {
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

            @if($strRangka->contains($userRangka?->sdm_hak_akses, 'PENGURUS'))
            var pengaturan = document.getElementById('menu-pengaturan')?.innerHTML.trim();
            if (!pengaturan) {
                lemparXHR({
                tujuan : "#menu-pengaturan",
                tautan : "{{ $urlRangka->route('komponen.menu-pengaturan', [], false) }}",
                normalview : true
                });
            };
            @endif

            @if (!$rekRangka->routeIs('mulai'))
            var pilihSumberdaya = document.getElementById('pilih-sumber_daya')?.innerHTML.trim();
            if (!pilihSumberdaya) {
                lemparXHR({
                tujuan : "#pilih-sumber_daya",
                tautan : "{{ $urlRangka->route('komponen.pilih-sumberdaya', [], false) }}",
                normalview : true
                });
            };
            @endif

            @if(!in_array($rekRangka->url(), [$urlRangka->route('akun', ['uuid' => $userRangka->sdm_uuid]), $urlRangka->route('ubah-akun', ['uuid' => $userRangka->sdm_uuid])]) && $rekRangka->routeIs('sdm.*', 'register', 'akun', 'ubah-akun', 'ubah-sandi'))
            var menuSDM = document.getElementById('navigasi-sdm')?.innerHTML.trim();
            if (!menuSDM) {
                lemparXHR({
                tujuan : "#navigasi-sdm",
                tautan : "{{ $urlRangka->route('komponen.menu-sdm', [], false) }}",
                normalview : true
                });
            };
            @endif

        })();
    }();
</script>
@endif