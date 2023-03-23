@extends('rangka')

@section('isi')
<div id="sdm_mulai">
    <h4>Kelola SDM</h4>
    
    @if($strRangka->contains($userRangka->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']))
    <div id="sdmIngatPtsb"><p class="kartu">Mengambil data permintaan tambah SDM...</p></div>
    <div id="sdmIngatPkpd"><p class="kartu">Mengambil data perjanjian kerja perlu ditinjau...</p></div>
    <div id="sdmIngatPstatus"><p class="kartu">Mengambil data perubahan status SDM...</p></div>
    <div id="sdmIngatBaru"><p class="kartu">Mengambil data penambahan SDM...</p></div>
    <div id="sdmIngatKeluar"><p class="kartu">Mengambil data pengurangan SDM...</p></div>

    <script>
        lemparXHR(false, "#sdmIngatKeluar", "{{ $urlRangka->route('sdm.mulai', ['fragment' => 'sdmIngatKeluar'], false) }}", "GET", false, false, false, false, false, true);
        lemparXHR(false, "#sdmIngatBaru", "{{ $urlRangka->route('sdm.mulai', ['fragment' => 'sdmIngatBaru'], false) }}", "GET", false, false, false, false, false, true);
        lemparXHR(false, "#sdmIngatPstatus", "{{ $urlRangka->route('sdm.mulai', ['fragment' => 'sdmIngatPstatus'], false) }}", "GET", false, false, false, false, false, true);
        lemparXHR(false, "#sdmIngatPkpd", "{{ $urlRangka->route('sdm.mulai', ['fragment' => 'sdmIngatPkpd'], false) }}", "GET", false, false, false, false, false, true);
        lemparXHR(false, "#sdmIngatPtsb", "{{ $urlRangka->route('sdm.mulai', ['fragment' => 'sdmIngatPtsb'], false) }}", "GET", false, false, false, false, false, true);
    </script>
    @endif

    <div id="sdmIngatUltah"><p class="kartu">Mengambil data hari lahir SDM...</p></div>
    
    <script>
        lemparXHR(false, "#sdmIngatUltah", "{{ $urlRangka->route('sdm.mulai', ['fragment' => 'sdmIngatUltah'], false) }}", "GET", false, false, false, false, false, true);
    </script>

    <div class="pintasan tcetak">
        <a href="#" onclick="event.preventDefault();window.scrollTo(0,0)" title="Kembali Ke Atas">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#panahatas' }}"
                    xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
        
        <a class="isi-xhr" href="{{ $urlRangka->route('unggah', [], false) }}" title="Unggah Data Profil SDM">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#unggah' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
        
        <a class="isi-xhr" href="{{ $urlRangka->route('sdm.penempatan.riwayat', [], false) }}" title="Cari Semua Riwayat Penempatan SDM">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#cari' }}"
                    xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
        
        <a class="isi-xhr" href="{{ $urlRangka->route('register', [], false) }}" title="Tambah Data SDM">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#tambahorang' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
    </div>

    @includeWhen($rekRangka->session()->has('spanduk') || $rekRangka->session()->has('pesan') || $errors->any(), 'pemberitahuan')
</div>
@endsection