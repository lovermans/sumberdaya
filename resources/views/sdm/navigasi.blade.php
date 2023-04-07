@if($userRangka && $rekRangka->routeIs('sdm.*', 'register', 'akun', 'ubah-akun', 'komponen.menu-sdm'))
    @if($strRangka->contains($userRangka->sdm_hak_akses, 'SDM'))
    <h2>Sumber Daya Manusia</h2>
    <div @class(['menu-t', 'aktif' => $rekRangka->routeIs('sdm.mulai')])>
        <a class="nav-xhr" href="{{ $urlRangka->route('sdm.mulai', [], false) }}" >
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#bolalampu' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
            Ringkasan
        </a>
    </div>
    
    <div @class(['menu-t', 'aktif' => $rekRangka->routeIs('sdm.panduan')])>
        <a class="nav-xhr" href="{{ $urlRangka->route('sdm.panduan', [], false) }}" >
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#dokumen' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
            Dokumen Resmi
        </a>
    </div>
    @endif

    @if($strRangka->contains($userRangka->sdm_hak_akses, ['SDM-MANAJEMEN', 'SDM-PENGURUS']))
    <div @class(['menu-t', 'aktif' => $rekRangka->routeIs('sdm.permintaan-tambah-sdm.data')])>
        <a class="nav-xhr" href="{{ $urlRangka->route('sdm.permintaan-tambah-sdm.data', [], false) }}">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#tambahorang' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
            Permintaan Tambah SDM
        </a>
    </div>
    
    <div @class(['menu-t', 'aktif' => $rekRangka->routeIs('sdm.penempatan*')])>
        <a class="nav-xhr" href="{{ $urlRangka->route('sdm.penempatan.data-aktif', [], false) }}">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#personil' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
            Penempatan SDM
        </a>
    </div>
    
    <div @class(['menu-j', 'aktif' => $rekRangka->routeIs('sdm.pelanggaran*')])>
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $mixRangka('/ikon.svg') . '#pelanggaran' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
        </svg>
        Pelanggaran SDM
    </div>
    
    <ul class="submenu">
        <li>
            <a @class(['nav-xhr', 'aktif' => $rekRangka->routeIs('sdm.pelanggaran.data')]) href="{{ $urlRangka->route('sdm.pelanggaran.data', [], false) }}">Laporkan Pelanggaran</a>
        </li>
        
        <li>
            <a @class(['nav-xhr', 'aktif' => $rekRangka->routeIs('sdm.sanksi.data*')]) href="{{ $urlRangka->route('sdm.sanksi.data', [], false) }}">Riwayat Sanksi</a>
        </li>
    </ul>
    
    <div @class(['menu-t', 'aktif' => $rekRangka->routeIs('sdm.posisi.data')])>
        <a class="nav-xhr" href="{{ $urlRangka->route('sdm.posisi.data', [], false) }}">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#posisi' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
            Pengaturan Jabatan
        </a>
    </div>
    @endif
@endif
