@if (str()->contains($app->request->user()->sdm_hak_akses, 'SDM'))
    <h2>Sumber Daya Manusia</h2>

    <div class="menu-t">
        <a class="nav-xhr" href="{{ $app->url->route('sdm.mulai') }}">
            <svg viewBox="0 0 24 24">
                <use href="#ikonpapaninformasi"></use>
            </svg>
            Papan Informasi
        </a>
    </div>

    <div class="menu-t">
        <a class="nav-xhr" href="{{ $app->url->route('sdm.panduan') }}">
            <svg viewBox="0 0 24 24">
                <use href="#ikondokumen"></use>
            </svg>
            Dokumen Resmi
        </a>
    </div>

    <script nonce="{{ $app->request->session()->get('sesiNonce') }}">
        if (location.href.includes("{{ $app->url->route('sdm.mulai') }}"))
            cariElemen("#navigasi-sdm a[href='{{ $app->url->route('sdm.mulai') }}']")
            .then((el) => {
                el.classList.add("aktif");
            });

        if (location.href.includes("{{ $app->url->route('sdm.panduan') }}"))
            cariElemen("#navigasi-sdm a[href='{{ $app->url->route('sdm.panduan') }}']")
            .then((el) => {
                el.classList.add("aktif");
            });
    </script>
@endif

@if (str()->contains($app->request->user()->sdm_hak_akses, ['SDM-MANAJEMEN', 'SDM-PENGURUS']))
    <div class="menu-t">
        <a class="nav-xhr" href="{{ $app->url->route('sdm.permintaan-tambah-sdm.data') }}">
            <svg viewBox="0 0 24 24">
                <use href="#ikontambahorang"></use>
            </svg>
            Permintaan Tambah SDM
        </a>
    </div>

    <div class="menu-t">
        <a class="nav-xhr" href="{{ $app->url->route('sdm.penempatan.data-aktif') }}">
            <svg viewBox="0 0 24 24">
                <use href="#ikonpersonil"></use>
            </svg>
            Penempatan SDM
        </a>
    </div>

    <div class="menu-t">
        <a class="nav-xhr" href="{{ $app->url->route('sdm.penilaian.data') }}">
            <svg viewBox="0 0 24 24">
                <use href="#ikonpenilaianberkala"></use>
            </svg>
            Penilaian Berkala SDM
        </a>
    </div>

    <div class="menu-t">
        <a class="nav-xhr" href="{{ $app->url->route('sdm.kepuasan.data') }}">
            <svg viewBox="0 0 24 24">
                <use href="#ikonsenyum"></use>
            </svg>
            Kepuasan SDM
        </a>
    </div>

    <div class="menu-j pelanggaran-sdm">
        <svg viewBox="0 0 24 24">
            <use href="#ikonpelanggaran"></use>
        </svg>
        Pelanggaran SDM
    </div>

    <ul class="submenu">
        <li>
            <a class="nav-xhr" href="{{ $app->url->route('sdm.pelanggaran.data') }}">Laporan Pelanggaran</a>
        </li>

        <li>
            <a class="nav-xhr" href="{{ $app->url->route('sdm.sanksi.data') }}">Riwayat Sanksi</a>
        </li>
    </ul>

    <div class="menu-t">
        <a class="nav-xhr" href="{{ $app->url->route('sdm.posisi.data') }}">
            <svg viewBox="0 0 24 24">
                <use href="#ikonposisi"></use>
            </svg>
            Pengaturan Jabatan
        </a>
    </div>

    <script nonce="{{ $app->request->session()->get('sesiNonce') }}">
        if (location.href.includes("{{ $app->url->route('sdm.permintaan-tambah-sdm.data') }}"))
            cariElemen("#navigasi-sdm a[href='{{ $app->url->route('sdm.permintaan-tambah-sdm.data') }}']")
            .then((el) => {
                el.classList.add("aktif");
            });

        if (location.href.includes("{{ $app->request->getBasePath() . '/sdm/penempatan' }}"))
            cariElemen("#navigasi-sdm a[href='{{ $app->url->route('sdm.penempatan.data-aktif') }}']")
            .then((el) => {
                el.classList.add("aktif");
            });

        if (location.href.includes("{{ $app->url->route('sdm.penilaian.data') }}"))
            cariElemen("#navigasi-sdm a[href='{{ $app->url->route('sdm.penilaian.data') }}']")
            .then((el) => {
                el.classList.add("aktif");
            });

        if (location.href.includes("{{ $app->url->route('sdm.pelanggaran.data') }}"))
            cariElemen("#navigasi-sdm a[href='{{ $app->url->route('sdm.pelanggaran.data') }}']")
            .then((el) => {
                el.classList.add("aktif");
            });

        if (location.href.includes("{{ $app->url->route('sdm.sanksi.data') }}"))
            cariElemen("#navigasi-sdm a[href='{{ $app->url->route('sdm.sanksi.data') }}']")
            .then((el) => {
                el.classList.add("aktif");
            });

        if (location.href.includes("{{ $app->url->route('sdm.posisi.data') }}"))
            cariElemen("#navigasi-sdm a[href='{{ $app->url->route('sdm.posisi.data') }}']")
            .then((el) => {
                el.classList.add("aktif");
            });

        if (location.href.includes("{{ $app->url->route('sdm.sanksi.data') }}") ||
            location.href.includes("{{ $app->url->route('sdm.pelanggaran.data') }}"))
            cariElemen("#navigasi-sdm .menu-j.pelanggaran-sdm")
            .then((el) => {
                el.classList.add("aktif");
            });
    </script>
@endif
