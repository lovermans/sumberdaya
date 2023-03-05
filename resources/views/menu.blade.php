<header class="tcetak">
    <section>
        <label for="nav" id="tbl-nav" title="Menu">
            <svg class="on" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#menu' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
            <svg class="off" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#menu' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </label>
        <a href="{{ $urlRangka->route('mulai', [], false) }}">
            <img id="logo" src="{{ $mixRangka('/images/Logo Perusahaan.webp') }}" title="{{ $confRangka->get('app.usaha') }}" alt="{{ $confRangka->get('app.usaha') }}" loading="lazy"></a>
            <label for="tema" id="tbl-tema" onclick=""title="Ubah Tema">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ $mixRangka('/ikon.svg') . '#tema' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg>
            </label>
            @if($userRangka)
            <label for="menu" id="tbl-menu" onclick="" title="Akun">
                <img id="akun" @class(['svg' => !$storageRangka->exists('sdm/foto-profil/' . $userRangka->sdm_no_absen . '.webp'),])
                src="{{ $storageRangka->exists('sdm/foto-profil/' . $userRangka->sdm_no_absen . '.webp') ? $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' => $userRangka?->sdm_no_absen . '.webp' . '?' . filemtime($appRangka->storagePath('app/sdm/foto-profil/' . $userRangka->sdm_no_absen . '.webp'))], false) : $mixRangka('/ikon.svg') . '#akun' }}"
                alt="{{ $userRangka->sdm_nama ?? 'foto akun' }}" title="{{ $userRangka->sdm_nama ?? 'foto akun' }}" loading="lazy">
            </label>
            @endif
        <h1>{{ $confRangka->get('app.name', 'Laravel') }}</h1>
        <div class="bersih"></div>
    </section>
</header>
<aside class="tcetak">
    <div id="menu-akun">
        @if($userRangka)
        <a @class(['menu-xhr' => !$rekRangka->routeIs('mulai'), 'aktif' => $rekRangka->routeIs('akun')]) href="{{ $urlRangka->route('akun', ['uuid' => $userRangka->sdm_uuid], false) }}">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#akun' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
            Profil
        </a>
        <a @class(['menu-xhr', 'aktif' => $rekRangka->routeIs('ubah-sandi')]) href="{{ $urlRangka->route('ubah-sandi', [], false) }}">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#kunci' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
            Ubah Sandi
        </a>
        <form method="POST" action="{{ $urlRangka->route('logout', [], false) }}">
            <input type="hidden" name="_token" value="{{ $sesiRangka->token() }}">
            <a href="{{ $urlRangka->route('logout', [], false) }}" onclick="event.preventDefault(); this.closest('form').submit()">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ $mixRangka('/ikon.svg') . '#keluar' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg>
                Keluar
            </a>
        </form>
        @endif
    </div>
</aside>