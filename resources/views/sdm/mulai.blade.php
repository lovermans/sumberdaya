@extends('rangka')

@section('isi')
<div id="sdm_mulai">
    <h4>Ringkasan</h4>

    <div id="sdm_unggah_sematan" class="scroll-margin"></div>

    @if($strRangka->contains($userRangka->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']))
    <div id="sdmIngatPtsb">
        <p class="kartu">Mengambil data permintaan tambah SDM...</p>
    </div>
    <div id="sdmIngatPkpd">
        <p class="kartu">Mengambil data perjanjian kerja perlu ditinjau...</p>
    </div>
    <div id="sdmIngatPstatus">
        <p class="kartu">Mengambil data perubahan status SDM...</p>
    </div>
    <div id="sdmIngatBaru">
        <p class="kartu">Mengambil data penambahan SDM...</p>
    </div>
    <div id="sdmIngatKeluar">
        <p class="kartu">Mengambil data pengurangan SDM...</p>
    </div>
    <div id="sdmIngatNilai">
        <p class="kartu">Mengambil data penilaian SDM...</p>
    </div>
    <div id="sdmIngatPelanggaran">
        <p class="kartu">Mengambil data pelanggaran SDM...</p>
    </div>
    <div id="sdmIngatSanksi">
        <p class="kartu">Mengambil data sanksi SDM...</p>
    </div>

    <script>
        (async() => {
            while(!window.aplikasiSiap) {
                await new Promise((resolve,reject) =>
                setTimeout(resolve, 1000));
            }
            
            lemparXHR({
                tujuan : "#sdmIngatKeluar",
                tautan : "{{ $urlRangka->route('sdm.mulai', ['fragment' => 'sdmIngatKeluar'], false) }}",
                normalview : true,
                fragmen : true
            });
            lemparXHR({
                tujuan : "#sdmIngatBaru",
                tautan : "{{ $urlRangka->route('sdm.mulai', ['fragment' => 'sdmIngatBaru'], false) }}",
                normalview : true,
                fragmen : true
            });
            lemparXHR({
                tujuan : "#sdmIngatPstatus",
                tautan : "{{ $urlRangka->route('sdm.mulai', ['fragment' => 'sdmIngatPstatus'], false) }}",
                normalview : true,
                fragmen : true
            });
            lemparXHR({
                tujuan : "#sdmIngatPkpd",
                tautan : "{{ $urlRangka->route('sdm.mulai', ['fragment' => 'sdmIngatPkpd'], false) }}",
                normalview : true,
                fragmen : true
            });
            lemparXHR({
                tujuan : "#sdmIngatPtsb",
                tautan : "{{ $urlRangka->route('sdm.mulai', ['fragment' => 'sdmIngatPtsb'], false) }}",
                normalview : true,
                fragmen : true
            });
            lemparXHR({
                tujuan : "#sdmIngatNilai",
                tautan : "{{ $urlRangka->route('sdm.mulai', ['fragment' => 'sdmIngatNilai'], false) }}",
                normalview : true,
                fragmen : true
            });
            lemparXHR({
                tujuan : "#sdmIngatPelanggaran",
                tautan : "{{ $urlRangka->route('sdm.mulai', ['fragment' => 'sdmIngatPelanggaran'], false) }}",
                normalview : true,
                fragmen : true
            });
            lemparXHR({
                tujuan : "#sdmIngatSanksi",
                tautan : "{{ $urlRangka->route('sdm.mulai', ['fragment' => 'sdmIngatSanksi'], false) }}",
                normalview : true,
                fragmen : true
            });
        })();
    </script>
    @endif

    <div id="sdmIngatUltah">
        <p class="kartu">Mengambil data hari lahir SDM...</p>
    </div>

    <script>
        (async() => {
            while(!window.aplikasiSiap) {
                await new Promise((resolve,reject) =>
                setTimeout(resolve, 1000));
            }
            
            lemparXHR({
                tujuan : "#sdmIngatUltah",
                tautan : "{{ $urlRangka->route('sdm.mulai', ['fragment' => 'sdmIngatUltah'], false) }}",
                normalview : true,
                fragmen : true
            });
        })();
    </script>

    <div class="pintasan tcetak">
        <a href="#" onclick="event.preventDefault();window.scrollTo(0,0)" title="Kembali Ke Atas">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#panahatas' }}"
                    xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>

        <a class="isi-xhr" data-rekam="false" data-tujuan="#sdm_unggah_sematan"
            href="{{ $urlRangka->route('sdm.unggah', [], false) }}" title="Unggah Data Profil SDM">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#unggah' }}" xmlns:xlink="http://www.w3.org/1999/xlink">
                </use>
            </svg>
        </a>

        <a class="isi-xhr" href="{{ $urlRangka->route('sdm.penempatan.riwayat', [], false) }}"
            title="Cari Semua Riwayat Penempatan SDM">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#cari' }}" xmlns:xlink="http://www.w3.org/1999/xlink">
                </use>
            </svg>
        </a>

        <a class="isi-xhr" href="{{ $urlRangka->route('register', [], false) }}" title="Tambah Data SDM">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#tambahorang' }}"
                    xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
    </div>

    @include('pemberitahuan')
    @include('komponen')

    @if($userRangka && $rekRangka->pjax())
    <script>
        cariElemen("#navigasi-sdm a[href='/sdm']").then((el) => {el.classList.add("aktif");});
    </script>
    @endif
</div>
@endsection