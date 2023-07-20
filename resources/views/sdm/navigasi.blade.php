@if($rekRangka->user() && $rekRangka->routeIs('sdm.*', 'register', 'komponen'))
@if($strRangka->contains($rekRangka->user()->sdm_hak_akses, 'SDM'))
<h2>Sumber Daya Manusia</h2>
<div class="menu-t">
    <a @class(['nav-xhr', 'aktif'=> $rekRangka->routeIs('sdm.mulai')]) href="{{ $urlRangka->route('sdm.mulai') }}" >
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#bolalampu' }}"
                xmlns:xlink="http://www.w3.org/1999/xlink">
            </use>
        </svg>
        Ringkasan
    </a>
</div>

<div class="menu-t">
    <a @class(['nav-xhr', 'aktif'=> $rekRangka->routeIs('sdm.panduan')]) href="{{ $urlRangka->route('sdm.panduan') }}" >
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#dokumen' }}"
                xmlns:xlink="http://www.w3.org/1999/xlink">
            </use>
        </svg>
        Dokumen Resmi
    </a>
</div>
@endif

@if($strRangka->contains($rekRangka->user()->sdm_hak_akses, ['SDM-MANAJEMEN', 'SDM-PENGURUS']))
<div class="menu-t">
    <a @class(['nav-xhr', 'aktif'=> $rekRangka->routeIs('sdm.permintaan-tambah-sdm.*')]) href="{{
        $urlRangka->route('sdm.permintaan-tambah-sdm.data') }}">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#tambahorang' }}"
                xmlns:xlink="http://www.w3.org/1999/xlink">
            </use>
        </svg>
        Permintaan Tambah SDM
    </a>
</div>

<div class="menu-t">
    <a @class(['nav-xhr', 'aktif'=> $rekRangka->routeIs('sdm.penempatan.*')]) href="{{
        $urlRangka->route('sdm.penempatan.data-aktif') }}">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#personil' }}"
                xmlns:xlink="http://www.w3.org/1999/xlink">
            </use>
        </svg>
        Penempatan SDM
    </a>
</div>

<div class="menu-t">
    <a @class(['nav-xhr', 'aktif'=> $rekRangka->routeIs('sdm.penilaian.*')]) href="{{
        $urlRangka->route('sdm.penilaian.data') }}">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#penilaianberkala' }}"
                xmlns:xlink="http://www.w3.org/1999/xlink">
            </use>
        </svg>
        Penilaian Berkala SDM
    </a>
</div>

<div @class(['menu-j', 'aktif'=> $rekRangka->routeIs('sdm.pelanggaran.*', 'sdm.sanksi.*')])>
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#pelanggaran' }}"
            xmlns:xlink="http://www.w3.org/1999/xlink">
        </use>
    </svg>
    Pelanggaran SDM
</div>

<ul class="submenu">
    <li>
        <a @class(['nav-xhr', 'aktif'=> $rekRangka->routeIs('sdm.pelanggaran.*')]) href="{{
            $urlRangka->route('sdm.pelanggaran.data') }}">Laporan Pelanggaran</a>
    </li>

    <li>
        <a @class(['nav-xhr', 'aktif'=> $rekRangka->routeIs('sdm.sanksi.*')]) href="{{
            $urlRangka->route('sdm.sanksi.data') }}">Riwayat Sanksi</a>
    </li>
</ul>

<div class="menu-t">
    <a @class(['nav-xhr', 'aktif'=> $rekRangka->routeIs('sdm.posisi.*')]) href="{{ $urlRangka->route('sdm.posisi.data')
        }}">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#posisi' }}"
                xmlns:xlink="http://www.w3.org/1999/xlink">
            </use>
        </svg>
        Pengaturan Jabatan
    </a>
</div>
@endif
@endif