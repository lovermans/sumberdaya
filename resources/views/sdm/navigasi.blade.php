@if($app->request->user() && $app->request->routeIs('sdm.*', 'register', 'komponen'))
@if(str()->contains($app->request->user()->sdm_hak_akses, 'SDM'))
<h2>Sumber Daya Manusia</h2>
<div class="menu-t">
    <a @class(['nav-xhr', 'aktif'=> $app->request->routeIs('sdm.mulai')]) href="{{ $app->url->route('sdm.mulai') }}" >
        <svg viewBox="0 0 24 24">
            <use href="#ikonpapaninformasi"></use>
        </svg>
        Papan Informasi
    </a>
</div>

<div class="menu-t">
    <a @class(['nav-xhr', 'aktif'=> $app->request->routeIs('sdm.panduan')]) href="{{ $app->url->route('sdm.panduan') }}"
        >
        <svg viewBox="0 0 24 24">
            <use href="#ikondokumen"></use>
        </svg>
        Dokumen Resmi
    </a>
</div>
@endif

@if(str()->contains($app->request->user()->sdm_hak_akses, ['SDM-MANAJEMEN', 'SDM-PENGURUS']))
<div class="menu-t">
    <a @class(['nav-xhr', 'aktif'=> $app->request->routeIs('sdm.permintaan-tambah-sdm.*')]) href="{{
        $app->url->route('sdm.permintaan-tambah-sdm.data') }}">
        <svg viewBox="0 0 24 24">
            <use href="#ikontambahorang"></use>
        </svg>
        Permintaan Tambah SDM
    </a>
</div>

<div class="menu-t">
    <a @class(['nav-xhr', 'aktif'=> $app->request->routeIs('sdm.penempatan.*')]) href="{{
        $app->url->route('sdm.penempatan.data-aktif') }}">
        <svg viewBox="0 0 24 24">
            <use href="#ikonpersonil"></use>
        </svg>
        Penempatan SDM
    </a>
</div>

<div class="menu-t">
    <a @class(['nav-xhr', 'aktif'=> $app->request->routeIs('sdm.penilaian.*')]) href="{{
        $app->url->route('sdm.penilaian.data') }}">
        <svg viewBox="0 0 24 24">
            <use href="#ikonpenilaianberkala"></use>
        </svg>
        Penilaian Berkala SDM
    </a>
</div>

<div @class(['menu-j', 'aktif'=> $app->request->routeIs('sdm.pelanggaran.*', 'sdm.sanksi.*')])>
    <svg viewBox="0 0 24 24">
        <use href="#ikonpelanggaran"></use>
    </svg>
    Pelanggaran SDM
</div>

<ul class="submenu">
    <li>
        <a @class(['nav-xhr', 'aktif'=> $app->request->routeIs('sdm.pelanggaran.*')]) href="{{
            $app->url->route('sdm.pelanggaran.data') }}">Laporan Pelanggaran</a>
    </li>

    <li>
        <a @class(['nav-xhr', 'aktif'=> $app->request->routeIs('sdm.sanksi.*')]) href="{{
            $app->url->route('sdm.sanksi.data') }}">Riwayat Sanksi</a>
    </li>
</ul>

<div class="menu-t">
    <a @class(['nav-xhr', 'aktif'=> $app->request->routeIs('sdm.posisi.*')]) href="{{
        $app->url->route('sdm.posisi.data')
        }}">
        <svg viewBox="0 0 24 24">
            <use href="#ikonposisi"></use>
        </svg>
        Pengaturan Jabatan
    </a>
</div>
@endif
@endif