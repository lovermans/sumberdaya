@extends('rangka')

@section('isi')
<div id="sdm_mulai">
    <h4>Kelola Sumber Daya Manusia</h4>
    @if($strRangka->contains($userRangka->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']))
    
    <div id="sdmIngatPtsb"></div>
    <div id="sdmIngatPkpd"></div>
    <div id="sdmIngatPstatus"></div>
    <div id="sdmIngatBaru"></div>
    <div id="sdmIngatKeluar"></div>

    <script>
        lemparXHR(false, "#sdmIngatKeluar", "{{ $urlRangka->route('sdm.mulai').'?fragment=sdmIngatKeluar' }}", "GET", "Menunggu Server memberi data SDM keluar...", false, false, false, false, true);
        lemparXHR(false, "#sdmIngatBaru", "{{ $urlRangka->route('sdm.mulai').'?fragment=sdmIngatBaru' }}", "GET", "Menunggu Server memberi data SDM baru...", false, false, false, false, true);
        lemparXHR(false, "#sdmIngatPstatus", "{{ $urlRangka->route('sdm.mulai').'?fragment=sdmIngatPstatus' }}", "GET", "Menunggu Server memberi data perubahan status SDM terkini...", false, false, false, false, true);
        lemparXHR(false, "#sdmIngatPkpd", "{{ $urlRangka->route('sdm.mulai').'?fragment=sdmIngatPkpd' }}", "GET", "Menunggu Server memberi data PKWT yang perlu ditinjau...", false, false, false, false, true);
        lemparXHR(false, "#sdmIngatPtsb", "{{ $urlRangka->route('sdm.mulai').'?fragment=sdmIngatPtsb' }}", "GET", "Menunggu Server memberi data permintaan tambah SDM...", false, false, false, false, true);
    </script>

    @endif

    <div id="sdmIngatUltah"></div>
    <script>
        lemparXHR(false, "#sdmIngatUltah", "{{ $urlRangka->route('sdm.mulai').'?fragment=sdmIngatUltah' }}", "GET", "Menunggu Server memberi data hari lahir SDM...", false, false, false, false, true);
    </script>

    <div class="pintasan tcetak">
        <a href="#" onclick="event.preventDefault();window.scrollTo(0,0)" title="Kembali Ke Atas">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#panahatas' }}"
                    xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
        <a class="isi-xhr" href="{{ $urlRangka->route('unggah') }}" title="Unggah Data Profil SDM">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#unggah' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
        <a class="isi-xhr" href="{{ $urlRangka->route('sdm.penempatan.riwayat') }}">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#cari' }}"
                    xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
        <a class="isi-xhr" href="{{ $urlRangka->route('register') }}" title="Tambah Data SDM">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#tambahorang' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
    </div>

    @includeWhen($rekRangka->session()->has('spanduk') || $rekRangka->session()->has('pesan') || $errors->any(), 'pemberitahuan')
</div>
@endsection