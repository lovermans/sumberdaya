@if($strRangka->contains($userRangka?->sdm_hak_akses, 'PENGURUS'))
<a @class(['nav-xhr' => !$rekRangka->routeIs('mulai'), 'aktif' => $rekRangka->is('/atur*')]) href="{{ $urlRangka->route('atur.data', [], false) }}">
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#pengaturan' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
    </svg>
    Pengaturan Umum
</a>
@endif