@if (!$rekRangka->routeIs('mulai') && $userRangka)
<div class="menu-t">
    <a href="{{ $urlRangka->route('mulai', [], false) }}">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $mixRangka('/ikon.svg') . '#aplikasi' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
        </svg>
        Pilih Sumber Daya
    </a>
</div>
@endif

@if($rekRangka->routeIs('sdm.*', 'register', 'akun', 'ubah-akun'))
<div id="navigasi-sdm"></div>
<script>
    lemparXHR({tujuan : "#navigasi-sdm", tautan : "{{ $urlRangka->route('sdm.mulai', ['fragment' => 'navigasi'], false) }}", topview : true, fragmen : true});
</script>
@endif

@include('pengaturan.navigasi')

<div class="menu-t">
    <a @class(['nav-xhr', 'aktif' => $rekRangka->routeIs('tentang-aplikasi')]) href="{{ $urlRangka->route('tentang-aplikasi', [], false) }}">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $mixRangka('/ikon.svg') . '#informasi' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
        </svg>
        Tentang Aplikasi
    </a>
</div>
