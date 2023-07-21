@fragment('pilih-sumber_daya')
@if ($rekRangka->user())
<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
    <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#aplikasi' }}"
        xmlns:xlink="http://www.w3.org/1999/xlink">
    </use>
</svg>
@endif
@endfragment

@fragment('avatar')
@if($rekRangka->user())
<img id="akun" @class(['svg'=> !$storageRangka->exists('sdm/foto-profil/' . $rekRangka->user()->sdm_no_absen .
'.webp')])
src="{{ $storageRangka->exists('sdm/foto-profil/' . $rekRangka->user()->sdm_no_absen . '.webp') ?
$urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' => $rekRangka->user()?->sdm_no_absen . '.webp' .
'?' . filemtime($appRangka->storagePath('app/sdm/foto-profil/' . $rekRangka->user()->sdm_no_absen . '.webp'))]) :
$urlRangka->asset($mixRangka('/ikon.svg')) . '#akun' }}"
alt="{{ $rekRangka->user()->sdm_nama ?? 'foto akun' }}" title="{{ $rekRangka->user()->sdm_nama ?? 'foto akun' }}"
loading="lazy">
@endif
@endfragment

@fragment('menu-avatar')
@if($rekRangka->user())
<div class="menu-akun">
    <a @class(['menu-xhr', 'aktif'=> $rekRangka->routeIs('sdm.akun')]) href="{{ $urlRangka->route('sdm.akun',
        ['uuid' => $rekRangka->user()->sdm_uuid]) }}">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#akun' }}"
                xmlns:xlink="http://www.w3.org/1999/xlink">
            </use>
        </svg>
        Profil
    </a>

    <a @class(['menu-xhr', 'aktif'=> $rekRangka->routeIs('sdm.ubah-sandi')]) href="{{
        $urlRangka->route('sdm.ubah-sandi') }}">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#kunci' }}"
                xmlns:xlink="http://www.w3.org/1999/xlink">
            </use>
        </svg>
        Ubah Sandi
    </a>

    <form method="POST" class="form-xhr" action="{{ $urlRangka->route('logout') }}">
        <input type="hidden" name="_token" value="{{ $rekRangka->session()->token() }}">
        <button type="submit" id="keluar-aplikasi" sembunyikan></button>
        <a href="{{ $urlRangka->route('logout') }}" onclick="event.preventDefault();
                document.getElementById('sematan_umum').replaceChildren();
                document.getElementById('nav').checked = false;
                document.getElementById('menu').checked = false;
                document.getElementById('pilih-aplikasi').checked = false;
                document.getElementById('keluar-aplikasi').click()">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#keluar' }}"
                    xmlns:xlink="http://www.w3.org/1999/xlink">
                </use>
            </svg>
            Keluar
        </a>
    </form>
</div>
@if(!$rekRangka->pjax())
<script>
    if ("{{ $urlRangka->route('sdm.akun', ['uuid' => $rekRangka->user()->sdm_uuid]) }}" == location.href) cariElemen(".menu-akun a[href='{{ $urlRangka->route('sdm.akun',
        ['uuid' => $rekRangka->user()->sdm_uuid]) }}']").then((el) => {el.classList.add("aktif");});
    if ("{{$urlRangka->route('sdm.ubah-sandi') }}" == location.href) cariElemen(".menu-akun a[href='{{ $urlRangka->route('sdm.ubah-sandi') }}']").then((el) => {el.classList.add("aktif");});
</script>
@endif
@endif
@endfragment


@fragment('menu-aplikasi')
@if($rekRangka->user())
<div class="menu-akun">
    <a @class(['menu-xhr', 'aktif'=> $rekRangka->routeIs('mulai')]) href="{{ $urlRangka->route('mulai')
        }}">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#rumah' }}"
                xmlns:xlink="http://www.w3.org/1999/xlink">
            </use>
        </svg>
        Beranda
    </a>

    <a @class(['menu-xhr', 'aktif'=> $rekRangka->routeIs('sdm.*', 'register')]) href="{{
        $urlRangka->route('sdm.mulai') }}">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#personil' }}"
                xmlns:xlink="http://www.w3.org/1999/xlink">
            </use>
        </svg>
        Sumber Daya Manusia
    </a>

    @if($strRangka->contains($rekRangka->user()?->sdm_hak_akses, 'PENGURUS'))
    <a @class(['menu-xhr', 'aktif'=> $rekRangka->routeIs('atur.*')]) href="{{ $urlRangka->route('atur.data')
        }}">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#pengaturan' }}"
                xmlns:xlink="http://www.w3.org/1999/xlink">
            </use>
        </svg>
        Pengaturan Umum
    </a>
    @endif
</div>
@if(!$rekRangka->pjax())
<script>
    if ("{{$urlRangka->route('sdm.mulai') }}" == location.href) cariElemen(".menu-akun a[href='{{ $urlRangka->route('sdm.mulai') }}']").then((el) => {el.classList.add("aktif");});
    if ("{{ $urlRangka->route('atur.data') }}" == location.href) cariElemen(".menu-akun a[href='{{ $urlRangka->route('atur.data') }}']").then((el) => {el.classList.add("aktif");});
</script>
@endif
@endif
@endfragment