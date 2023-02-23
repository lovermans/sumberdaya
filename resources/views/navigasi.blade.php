@if (!$rekRangka->routeIs('mulai') && $rekRangka->user())
<div class="menu-t">
    <a href="{{ $urlRangka->route('mulai') }}">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#aplikasi' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
        </svg>
        Pilih Sumber Daya
    </a>
</div>
@endif

@if($rekRangka->routeIs('sdm.*', 'register', 'akun', 'ubah-akun'))
<div id="navigasi-sdm"></div>
<script>
    lemparXHR(false, "#navigasi-sdm", "{{ $urlRangka->route('sdm.mulai').'?fragment=navigasi' }}", "GET", "Menunggu Server mengirim menu...", false, false, false, false, true);
</script>
@endif

@include('pengaturan.navigasi')

<div class="menu-t">
    <a @class(['nav-xhr', 'aktif' => $rekRangka->routeIs('tentang-aplikasi')]) href="{{ $urlRangka->route('tentang-aplikasi') }}">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#informasi' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
        </svg>
        Tentang Aplikasi
    </a>
</div>
