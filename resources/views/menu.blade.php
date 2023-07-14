@fragment('pilih-sumber_daya')
@if ($userRangka)
<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
    <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#aplikasi' }}"
        xmlns:xlink="http://www.w3.org/1999/xlink">
    </use>
</svg>
@endif
@endfragment

@fragment('avatar')
@if($userRangka)
<img id="akun" @class(['svg'=> !$storageRangka->exists('sdm/foto-profil/' . $userRangka->sdm_no_absen .
'.webp'),])
src="{{ $storageRangka->exists('sdm/foto-profil/' . $userRangka->sdm_no_absen . '.webp') ?
$urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' => $userRangka?->sdm_no_absen . '.webp' .
'?' . filemtime($appRangka->storagePath('app/sdm/foto-profil/' . $userRangka->sdm_no_absen . '.webp'))],
false) : $urlRangka->asset($mixRangka('/ikon.svg')) . '#akun' }}"
alt="{{ $userRangka->sdm_nama ?? 'foto akun' }}" title="{{ $userRangka->sdm_nama ?? 'foto akun' }}"
loading="lazy">
@endif
@endfragment

@fragment('menu-avatar')
@if($userRangka)
<div class="menu-akun">
    <a @class(['menu-xhr', 'aktif'=> $rekRangka->routeIs('sdm.akun')]) href="{{ $urlRangka->route('sdm.akun',
        ['uuid' => $userRangka->sdm_uuid]) }}">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#akun' }}"
                xmlns:xlink="http://www.w3.org/1999/xlink">
            </use>
        </svg>
        Profil
    </a>

    <a @class(['menu-xhr', 'aktif'=> $rekRangka->routeIs('sdm.ubah-sandi')]) href="{{
        $urlRangka->route('sdm.ubah-sandi' }}">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#kunci' }}"
                xmlns:xlink="http://www.w3.org/1999/xlink">
            </use>
        </svg>
        Ubah Sandi
    </a>

    <form method="POST" class="form-xhr" action="{{ $urlRangka->route('logout' }}">
        <input type="hidden" name="_token" value="{{ $rekRangka->session()->token() }}">
        <button type="submit" id="keluar-aplikasi" sembunyikan></button>
        <a href="{{ $urlRangka->route('logout' }}" onclick="event.preventDefault();
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
@endif
@endfragment


@fragment('menu-aplikasi')
@if($userRangka)
<div class="menu-akun">
    <a @class(['menu-xhr', 'aktif'=> $rekRangka->routeIs('mulai')]) href="{{ $urlRangka->route('mulai'
        }}">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#rumah' }}"
                xmlns:xlink="http://www.w3.org/1999/xlink">
            </use>
        </svg>
        Beranda
    </a>

    <a @class(['menu-xhr', 'aktif'=> $rekRangka->routeIs('sdm.*', 'register')]) href="{{
        $urlRangka->route('sdm.mulai' }}">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#personil' }}"
                xmlns:xlink="http://www.w3.org/1999/xlink">
            </use>
        </svg>
        Sumber Daya Manusia
    </a>

    @if($strRangka->contains($userRangka?->sdm_hak_akses, 'PENGURUS'))
    <a @class(['menu-xhr', 'aktif'=> $rekRangka->is('atur')]) href="{{ $urlRangka->route('atur.data'
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
@endif
@endfragment