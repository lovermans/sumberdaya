<header class="tcetak">
    <section>
        <label for="nav" id="tbl-nav" title="Menu">
            <svg class="on" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#menu' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
            <svg class="off" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#menu' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </label>
        <a href="{{ $urlRangka->route('mulai') }}">
            <img id="logo" src="{{ $urlRangka->asset($mixRangka('/images/Logo Perusahaan.webp')) }}" title="{{ $confRangka->get('app.usaha') }}" alt="{{ $confRangka->get('app.usaha') }}" loading="lazy"></a>
        <h1>{{ $confRangka->get('app.name', 'Laravel') }}</h1>
        <label for="tema" id="tbl-tema" onclick=""title="Ubah Tema">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#tema' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </label>
        @if($userRangka)
        <label for="menu" id="tbl-menu" onclick="" title="Akun">
                <img id="akun" @class(['svg' => !$storageRangka->exists('sdm/foto-profil/' . $userRangka->sdm_no_absen . '.webp'),])
                    src="{{ $storageRangka->exists('sdm/foto-profil/' . $userRangka->sdm_no_absen . '.webp') ? $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' => $userRangka?->sdm_no_absen . '.webp' . '?' . filemtime($appRangka->storagePath('app/sdm/foto-profil/' . $userRangka->sdm_no_absen . '.webp'))]) : $urlRangka->asset($mixRangka('/ikon.svg')) . '#akun' }}"
                    alt="{{ $userRangka->sdm_nama ?? 'foto akun' }}" title="{{ $userRangka->sdm_nama ?? 'foto akun' }}" loading="lazy">
        </label>
        @endif
        <div class="bersih"></div>
    </section>
</header>
<aside class="tcetak">
    <div id="menu-akun">
        @if($userRangka)
        <a @class(['menu-xhr' => !$rekRangka->routeIs('mulai'), 'aktif' => $rekRangka->routeIs('akun')]) href="{{ $urlRangka->route('akun', ['uuid' => $userRangka->sdm_uuid]) }}">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#akun' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
            Profil
        </a>
        <a @class(['menu-xhr', 'aktif' => $rekRangka->routeIs('ubah-sandi')]) href="{{ $urlRangka->route('ubah-sandi') }}">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#kunci' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
            Ubah Sandi
        </a>
        <form method="POST" action="{{ $urlRangka->route('logout') }}">
            <input type="hidden" name="_token" value="{{ $rekRangka->session()->token() }}">
            <a href="{{ $urlRangka->route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit()">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#keluar' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg>
                Keluar
            </a>
        </form>
        @endif
    </div>
</aside>