@fragment('pilih-sumber_daya')
@if ($app->request->user())
<svg viewBox="0 0 24 24">
    <use href="#ikonaplikasi"></use>
</svg>
@endif
@endfragment

@fragment('avatar')
@if($app->request->user())
<img id="akun" @class(['svg'=> !$app->filesystem->exists('sdm/foto-profil/' . $app->request->user()->sdm_no_absen .
'.webp')])
src="{{
$app->filesystem->exists('sdm/foto-profil/' . $app->request->user()->sdm_no_absen . '.webp')
? $app->url->route('sdm.tautan-foto-profil', ['berkas_foto_profil' => $app->request->user()?->sdm_no_absen . '.webp'
.'?'
. filemtime($app->storagePath('app/sdm/foto-profil/' . $app->request->user()->sdm_no_absen . '.webp'))])
: $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/blank.webp'))
}}"
alt="{{ $app->request->user()->sdm_nama ?? 'foto akun' }}"
title="{{ $app->request->user()->sdm_nama ?? 'foto akun' }}"
loading="lazy">
@endif
@endfragment

@fragment('menu-avatar')
@if($app->request->user())
<div class="menu-akun">
    <a @class(['menu-xhr', 'aktif'=> $app->request->routeIs('sdm.akun')]) href="{{ $app->url->route('sdm.akun',
        ['uuid' => $app->request->user()->sdm_uuid]) }}">
        <svg viewBox="0 0 24 24">
            <use href="#ikonakun"></use>
        </svg>
        Profil
    </a>

    <a @class(['menu-xhr', 'aktif'=> $app->request->routeIs('sdm.ubah-sandi')]) href="{{
        $app->url->route('sdm.ubah-sandi') }}">
        <svg viewBox="0 0 24 24">
            <use href="#ikonkunci"></use>
        </svg>
        Ubah Sandi
    </a>

    <form method="POST" class="form-xhr" action="{{ $app->url->route('logout') }}">
        <input type="hidden" name="_token" value="{{ $app->request->session()->token() }}">
        <button type="submit" id="keluar-aplikasi" sembunyikan></button>
        <a href="{{ $app->url->route('logout') }}" onclick="event.preventDefault();
                document.getElementById('sematan_umum').replaceChildren();
                document.getElementById('nav').checked = false;
                document.getElementById('menu').checked = false;
                document.getElementById('pilih-aplikasi').checked = false;
                document.getElementById('keluar-aplikasi').click()">
            <svg viewBox="0 0 24 24">
                <use href="#ikonkeluar"></use>
            </svg>
            Keluar
        </a>
    </form>
</div>
<script>
    if ("{{ $app->url->route('sdm.akun', ['uuid' => $app->request->user()->sdm_uuid]) }}" == location.href) cariElemen(".menu-akun a[href='{{ $app->url->route('sdm.akun',
        ['uuid' => $app->request->user()->sdm_uuid]) }}']").then((el) => {el.classList.add("aktif");});
    if ("{{$app->url->route('sdm.ubah-sandi') }}" == location.href) cariElemen(".menu-akun a[href='{{ $app->url->route('sdm.ubah-sandi') }}']").then((el) => {el.classList.add("aktif");});
</script>
@endif
@endfragment


@fragment('menu-aplikasi')
@if($app->request->user())
<div class="menu-akun">
    <a @class(['menu-xhr', 'aktif'=> $app->request->routeIs('mulai')]) href="{{ $app->url->route('mulai')
        }}">
        <svg viewBox="0 0 24 24">
            <use href="#ikonrumah"></use>
        </svg>
        Beranda
    </a>

    <a @class(['menu-xhr', 'aktif'=> $app->request->routeIs('sdm.*', 'register')]) href="{{
        $app->url->route('sdm.mulai') }}">
        <svg viewBox="0 0 24 24">
            <use href="#ikonpersonil"></use>
        </svg>
        Sumber Daya Manusia
    </a>

    @if(str()->contains($app->request->user()?->sdm_hak_akses, 'PENGURUS'))
    <a @class(['menu-xhr', 'aktif'=> $app->request->routeIs('atur.*')]) href="{{ $app->url->route('atur.data')
        }}">
        <svg viewBox="0 0 24 24">
            <use href="#ikonpengaturan"></use>
        </svg>
        Pengaturan Umum
    </a>
    @endif
</div>
<script>
    if ("{{$app->url->route('sdm.mulai') }}" == location.href) cariElemen(".menu-akun a[href='{{ $app->url->route('sdm.mulai') }}']").then((el) => {el.classList.add("aktif");});
    if ("{{ $app->url->route('atur.data') }}" == location.href) cariElemen(".menu-akun a[href='{{ $app->url->route('atur.data') }}']").then((el) => {el.classList.add("aktif");});
</script>
@endif
@endfragment