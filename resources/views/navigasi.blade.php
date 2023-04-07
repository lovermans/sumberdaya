<div id="navigasi-sdm">
    @include('sdm.navigasi')
</div>

<div id="menu-pengaturan" class="menu-t">
@include('pengaturan.navigasi')
</div>

<div class="menu-t">
    <a @class(['nav-xhr', 'aktif' => $rekRangka->routeIs('tentang-aplikasi')]) href="{{ $urlRangka->route('tentang-aplikasi', [], false) }}">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $mixRangka('/ikon.svg') . '#informasi' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
        </svg>
        Tentang Aplikasi
    </a>
</div>
