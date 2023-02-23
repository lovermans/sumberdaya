@if($strRangka->contains($userRangka?->sdm_hak_akses, 'PENGURUS'))
<div class="menu-t">
    <a @class(['nav-xhr' => !$rekRangka->routeIs('mulai'), 'aktif' => $rekRangka->is('/atur*')]) href="{{ $urlRangka->route('atur.data') }}">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#pengaturan' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
        </svg>
        Pengaturan Umum
    </a>
</div>
@endif