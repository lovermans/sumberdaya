<header class="tcetak">
    <section>
        <label for="nav" id="tbl-nav" title="Menu">
            <svg class="on" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#menu' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
            
            <svg class="off" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#tutup' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </label>

        <a class="menu-xhr" href="{{ $urlRangka->route('mulai', [], false) }}">
            <img id="logo" src="{{ $mixRangka('/images/Logo Perusahaan.webp') }}" title="{{ $confRangka->get('app.usaha') }}" alt="{{ $confRangka->get('app.usaha') }}" loading="lazy"></a>
            
            <label for="pilih-aplikasi" id="pilih-sumber_daya" onclick="" title="Pilih Aplikasi">
                @fragment('pilih-sumber_daya')
                @if ($userRangka)
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#aplikasi' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                @endif
                @endfragment
            </label>
            
            <label for="menu" id="tbl-menu" onclick="" title="Akun">
                @fragment('avatar')
                @if($userRangka)
                <img id="akun" @class(['svg' => !$storageRangka->exists('sdm/foto-profil/' . $userRangka->sdm_no_absen . '.webp'),])
                src="{{ $storageRangka->exists('sdm/foto-profil/' . $userRangka->sdm_no_absen . '.webp') ? $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' => $userRangka?->sdm_no_absen . '.webp' . '?' . filemtime($appRangka->storagePath('app/sdm/foto-profil/' . $userRangka->sdm_no_absen . '.webp'))], false) : $mixRangka('/ikon.svg') . '#akun' }}"
                alt="{{ $userRangka->sdm_nama ?? 'foto akun' }}" title="{{ $userRangka->sdm_nama ?? 'foto akun' }}" loading="lazy">
                @endif
                @endfragment
            </label>
            
            <label for="tema" id="tbl-tema" onclick="" title="Ubah Tema">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ $mixRangka('/ikon.svg') . '#tema' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg>
            </label>

        <div class="bersih"></div>
    </section>
</header>

<aside id="menu-avatar" class="tcetak">
    @fragment('menu-avatar')
    @if($userRangka)
    <div class="menu-akun">
        <a @class(['menu-xhr', 'aktif' => $rekRangka->routeIs('sdm.akun')]) href="{{ $urlRangka->route('sdm.akun', ['uuid' => $userRangka->sdm_uuid], false) }}">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#akun' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
            Profil
        </a>
        
        <a @class(['menu-xhr', 'aktif' => $rekRangka->routeIs('sdm.ubah-sandi')]) href="{{ $urlRangka->route('sdm.ubah-sandi', [], false) }}">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#kunci' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
            Ubah Sandi
        </a>
        
        <form method="POST" class="form-xhr" action="{{ $urlRangka->route('logout', [], false) }}">
            <input type="hidden" name="_token" value="{{ $rekRangka->session()->token() }}">
            <button type="submit" id="keluar-aplikasi" sembunyikan></button>
            <a href="{{ $urlRangka->route('logout', [], false) }}" onclick="event.preventDefault();
                document.querySelector('#sematan_umum').innerHTML = '';
                document.querySelector('#nav').checked = false;
                document.querySelector('#menu').checked = false;
                document.querySelector('#pilih-aplikasi').checked = false;
                document.querySelector('#keluar-aplikasi').click()">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ $mixRangka('/ikon.svg') . '#keluar' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg>
                Keluar
            </a>
        </form>
    </div>
    @endif
    @endfragment
</aside>

<aside id="menu-aplikasi" class="tcetak">
    @fragment('menu-aplikasi')
    @if($userRangka)
    <div class="menu-akun">
        <a @class(['menu-xhr', 'aktif' => $rekRangka->routeIs('mulai')]) href="{{ $urlRangka->route('mulai', [], false) }}">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#rumah' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
            Beranda
        </a>
        
        <a @class(['menu-xhr', 'aktif' => $rekRangka->routeIs('sdm.*', 'register')]) href="{{ $urlRangka->route('sdm.mulai', [], false) }}">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#personil' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
            Sumber Daya Manusia
        </a>

        @if($strRangka->contains($userRangka?->sdm_hak_akses, 'PENGURUS'))
        <a @class(['menu-xhr', 'aktif' => $rekRangka->is('atur')]) href="{{ $urlRangka->route('atur.data', [], false) }}">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#pengaturan' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
            Pengaturan Umum
        </a>
        @endif

    </div>
    @endif
    @endfragment
</aside>