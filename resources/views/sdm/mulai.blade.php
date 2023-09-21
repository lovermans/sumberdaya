@extends('rangka')

@section('isi')
    <div id="sdm_mulai">
        <h4>Papan Informasi</h4>

        <div class="scroll-margin" id="sdm_unggah_sematan"></div>

        @if (str()->contains($app->request->user()?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']))
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
                (async () => {
                    while (!window.aplikasiSiap) {
                        await new Promise((resolve, reject) =>
                            setTimeout(resolve, 1000));
                    }

                    lemparXHR({
                        tujuan: "#sdmIngatKeluar",
                        tautan: "{{ $app->url->route('sdm.mulai', ['fragment' => 'sdmIngatKeluar']) }}",
                        normalview: true,
                        fragmen: true
                    });

                    lemparXHR({
                        tujuan: "#sdmIngatBaru",
                        tautan: "{{ $app->url->route('sdm.mulai', ['fragment' => 'sdmIngatBaru']) }}",
                        normalview: true,
                        fragmen: true
                    });

                    lemparXHR({
                        tujuan: "#sdmIngatPstatus",
                        tautan: "{{ $app->url->route('sdm.mulai', ['fragment' => 'sdmIngatPstatus']) }}",
                        normalview: true,
                        fragmen: true
                    });

                    lemparXHR({
                        tujuan: "#sdmIngatPkpd",
                        tautan: "{{ $app->url->route('sdm.mulai', ['fragment' => 'sdmIngatPkpd']) }}",
                        normalview: true,
                        fragmen: true
                    });

                    lemparXHR({
                        tujuan: "#sdmIngatPtsb",
                        tautan: "{{ $app->url->route('sdm.mulai', ['fragment' => 'sdmIngatPtsb']) }}",
                        normalview: true,
                        fragmen: true
                    });

                    lemparXHR({
                        tujuan: "#sdmIngatNilai",
                        tautan: "{{ $app->url->route('sdm.mulai', ['fragment' => 'sdmIngatNilai']) }}",
                        normalview: true,
                        fragmen: true
                    });

                    lemparXHR({
                        tujuan: "#sdmIngatPelanggaran",
                        tautan: "{{ $app->url->route('sdm.mulai', ['fragment' => 'sdmIngatPelanggaran']) }}",
                        normalview: true,
                        fragmen: true
                    });

                    lemparXHR({
                        tujuan: "#sdmIngatSanksi",
                        tautan: "{{ $app->url->route('sdm.mulai', ['fragment' => 'sdmIngatSanksi']) }}",
                        normalview: true,
                        fragmen: true
                    });
                })();
            </script>
        @endif

        <div id="sdmIngatUltah">
            <p class="kartu">Mengambil data hari lahir SDM...</p>
        </div>

        <script nonce="{{ $app->request->session()->get('sesiNonce') }}">
            (async () => {
                while (!window.aplikasiSiap) {
                    await new Promise((resolve, reject) =>
                        setTimeout(resolve, 1000));
                }

                lemparXHR({
                    tujuan: "#sdmIngatUltah",
                    tautan: "{{ $app->url->route('sdm.mulai', ['fragment' => 'sdmIngatUltah']) }}",
                    normalview: true,
                    fragmen: true
                });
            })();
        </script>

        <div class="pintasan tcetak">
            <a class="tbl-btt" href="#" title="Kembali Ke Atas">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonpanahatas"></use>
                </svg>
            </a>

            @if (str()->contains($app->request->user()?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']))
                <a class="isi-xhr" data-rekam="false" data-tujuan="#sdm_unggah_sematan" href="{{ $app->url->route('sdm.unggah') }}" title="Unggah Data Profil SDM">
                    <svg viewBox="0 0 24 24">
                        <use href="#ikonunggah"></use>
                    </svg>
                </a>

                <a class="isi-xhr" href="{{ $app->url->route('sdm.penempatan.riwayat') }}" title="Cari Semua Riwayat Penempatan SDM">
                    <svg viewBox="0 0 24 24">
                        <use href="#ikoncari"></use>
                    </svg>
                </a>

                <a class="isi-xhr" href="{{ $app->url->route('register') }}" title="Tambah Data SDM">
                    <svg viewBox="0 0 24 24">
                        <use href="#ikontambahorang"></use>
                    </svg>
                </a>
            @endif
        </div>

        @if ($app->request->user() && $app->request->pjax())
            <script nonce="{{ $app->request->session()->get('sesiNonce') }}">
                cariElemen("#navigasi-sdm a[href='{{ $app->url->route('sdm.mulai') }}']")
                    .then((el) => {
                        el.classList.add("aktif");
                    });
            </script>
        @endif

        @include('pemberitahuan')
        @include('komponen')
    </div>
@endsection
