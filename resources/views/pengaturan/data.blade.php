@extends('rangka')

@section('isi')
    <div id="atur_data">
        <p class="tcetak kartu">
            <svg fill="var(--taut-umum)" viewbox="0 0 24 24">
                <use href="#ikoninformasi"></use>
            </svg>
            Data pengaturan ini digunakan hanya sebagai alat bantu validasi pengisian data lain dalam aplikasi. Segala
            bentuk perubahan dalam data pengaturan ini tidak akan berdampak pada data lainnya, Anda harus mengubah data
            lainnya secara manual.
        </p>

        @isset($tabels)
            <div class="cari-data tcetak">
                <form class="form-xhr kartu" id="form_atur_data_cari" data-tujuan="#atur_tabels" data-frag="true" method="GET" action="{{ $app->url->current() }}">

                    <input name="fragment" type="hidden" value="atur_tabels">

                    <details class="gspan-4" {{ $app->request->anyFilled(['atur_jenis', 'atur_butir', 'atur_status']) ? 'open' : '' }}>

                        <summary class="cari">
                            <div class="isian gspan-4">
                                <input id="kata_kunci_pengaturan" name="kata_kunci" type="text" value="{{ $app->request->kata_kunci }}" aria-label="Cari Kata Kunci">

                                <button class="cari-cepat" id="tombol_cari_atur" type="submit" title="Cari Data">
                                    <svg viewbox="0 0 24 24">
                                        <use href="#ikoncari"></use>
                                    </svg>
                                </button>
                            </div>
                        </summary>

                        <div class="kartu form gspan-4">

                            <div class="isian">
                                <label for="atur_data_cariStatus">Saring Status</label>

                                <select class="pil-dasar" id="atur_data_cariStatus" name="atur_status[]" multiple onchange="getElementById('tombol_cari_atur').click()">
                                    @foreach ($statuses as $status)
                                        <option @selected(in_array($status, (array) $app->request->atur_status))>
                                            {{ $status }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="isian">
                                <label for="atur_data_cariJenis">1 Saring Jenis</label>

                                <select class="pil-cari" id="atur_data_cariJenis" name="atur_jenis[]" @disabled($jenises->count() < 1) multiple
                                    onchange="getElementById('tombol_cari_atur').click()">
                                    @foreach ($jenises as $jenis)
                                        <option @selected(in_array($jenis, (array) $app->request->atur_jenis))>
                                            {{ $jenis }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="isian">
                                <label for="atur_data_cariButir">2 Saring Butir</label>

                                <select class="pil-cari" id="atur_data_cariButir" name="atur_butir[]" @disabled($butirs->count() < 1) multiple
                                    onchange="getElementById('tombol_cari_atur').click()">
                                    @foreach ($butirs as $butir)
                                        <option @selected(in_array($butir, (array) $app->request->atur_butir))>
                                            {{ $butir }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="gspan-4"></div>

                            <button class="utama pelengkap" id="tombol_saring_atur" type="submit" title="Saring Data">
                                <svg viewbox="0 0 24 24">
                                    <use href="#ikoncari"></use>
                                </svg>
                                SARING
                            </button>
                        </div>
                    </details>
                </form>
            </div>

            <div class="kartu scroll-margin" id="atur_tabels">
                @fragment('atur_tabels')
                    <div class="scroll-margin" id="atur_sematan"></div>

                    <div class="trek-data tcetak">
                        <span class="bph">
                            <label for="atur_data_cariPerHalaman">Baris per halaman : </label>

                            <select class="pil-saja" id="atur_data_cariPerHalaman" name="bph" form="form_atur_data_cari"
                                onchange="getElementById('tombol_cari_atur').click()">
                                <option>25</option>
                                <option @selected($tabels->perPage() == 50)>50</option>
                                <option @selected($tabels->perPage() == 75)>75</option>
                                <option @selected($tabels->perPage() == 100)>100</option>
                            </select>
                        </span>

                        <span class="ket">
                            {{ number_format($tabels->firstItem(), 0, ',', '.') }} -
                            {{ number_format($tabels->lastItem(), 0, ',', '.') }} dari
                            {{ number_format($tabels->total(), 0, ',', '.') }} data
                        </span>

                        @if ($tabels->hasPages())
                            <span class="trek">
                                @if ($tabels->currentPage() > 1)
                                    <a class="isi-xhr" data-tujuan="#atur_tabels" data-frag="true" href="{{ $tabels->url(1) }}" title="Awal">
                                        <svg viewbox="0 0 24 24">
                                            <use href="#ikonawal"></use>
                                        </svg>
                                    </a>
                                @endif

                                @if ($tabels->previousPageUrl())
                                    <a class="isi-xhr" data-tujuan="#atur_tabels" data-frag="true" href="{{ $tabels->previousPageUrl() }}" title="Sebelumnya">
                                        <svg viewbox="0 0 24 24">
                                            <use href="#ikonmundur"></use>
                                        </svg>
                                    </a>
                                @endif

                                @if ($tabels->nextPageUrl())
                                    <a class="isi-xhr" data-tujuan="#atur_tabels" data-frag="true" href="{{ $tabels->nextPageUrl() }}" title="Berikutnya">
                                        <svg viewbox="0 0 24 24">
                                            <use href="#ikonmaju"></use>
                                        </svg>
                                    </a>

                                    <a class="isi-xhr" data-tujuan="#atur_tabels" data-frag="true" href="{{ $tabels->url($tabels->lastPage()) }}" title="Akhir">
                                        <svg viewbox="0 0 24 24">
                                            <use href="#ikonakhir"></use>
                                        </svg>
                                    </a>
                                @endif
                            </span>
                        @endif

                        <details class="gspan-4" {{ $app->request->anyFilled('urut') ? 'open' : '' }}>
                            <summary>Pengurutan :</summary>

                            <div class="kartu form" id="atur_data_cariUrut">
                                <div class="isian" data-indeks="{{ $urutJenis ? $indexJenis : 'X' }}">
                                    <label for="atur_data_cariUrutJenis">{{ $urutJenis ? $indexJenis . '. ' : '' }}Urut
                                        Jenis</label>

                                    <select class="pil-dasar" id="atur_data_cariUrutJenis" name="urut[]" form="form_atur_data_cari"
                                        onchange="getElementById('tombol_cari_atur').click()">
                                        <option selected disabled></option>

                                        <option value="atur_jenis ASC" @selected(in_array('atur_jenis ASC', (array) $app->request->urut))>
                                            A - Z
                                        </option>

                                        <option value="atur_jenis DESC" @selected(in_array('atur_jenis DESC', (array) $app->request->urut))>
                                            Z - A
                                        </option>
                                    </select>

                                    <span class="t-bantu">Pilih satu</span>
                                </div>

                                <div class="isian" data-indeks="{{ $urutButir ? $indexButir : 'X' }}">
                                    <label for="atur_data_cariUrutButir">{{ $urutButir ? $indexButir . '. ' : '' }}Urut
                                        Butir</label>

                                    <select class="pil-dasar" id="atur_data_cariUrutButir" name="urut[]" form="form_atur_data_cari"
                                        onchange="getElementById('tombol_cari_atur').click()">
                                        <option selected disabled></option>

                                        <option value="atur_butir ASC" @selected(in_array('atur_butir ASC', (array) $app->request->urut))>
                                            A - Z
                                        </option>

                                        <option value="atur_butir DESC" @selected(in_array('atur_butir DESC', (array) $app->request->urut))>
                                            Z - A
                                        </option>
                                    </select>

                                    <span class="t-bantu">Pilih satu</span>
                                </div>

                                <div class="isian" data-indeks="{{ $urutStatus ? $indexStatus : 'X' }}">
                                    <label for="atur_data_cariUrutStatus">{{ $urutStatus ? $indexStatus . '. ' : '' }}Urut
                                        Status</label>

                                    <select class="pil-dasar" id="atur_data_cariUrutStatus" name="urut[]" form="form_atur_data_cari"
                                        onchange="getElementById('tombol_cari_atur').click()">
                                        <option selected disabled></option>

                                        <option value="atur_status ASC" @selected(in_array('atur_status ASC', (array) $app->request->urut))>
                                            A - Z
                                        </option>

                                        <option value="atur_status DESC" @selected(in_array('atur_status DESC', (array) $app->request->urut))>
                                            Z - A
                                        </option>
                                    </select>

                                    <span class="t-bantu">Pilih satu</span>
                                </div>
                            </div>
                        </details>
                    </div>

                    <div class="data ringkas">
                        <table class="tabel" id="atur_tabel">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>No</th>
                                    <th>Jenis</th>
                                    <th>Butir</th>
                                    <th>Keterangan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($tabels as $nomor => $tabel)
                                    <tr @class(['merah' => $tabel->atur_status == 'NON-AKTIF'])>
                                        <th>
                                            <div class="pil-aksi">
                                                <button id="{{ 'aksi_penempatan_baris_' . $tabels->firstItem() + $nomor }}" title="Pilih Tindakan">
                                                    <svg viewbox="0 0 24 24">
                                                        <use href="#ikonmenuvert">
                                                        </use>
                                                    </svg>
                                                </button>

                                                <div class="aksi">
                                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#atur_sematan"
                                                        href="{{ $app->url->route('atur.lihat', ['uuid' => $tabel->atur_uuid]) }}" title="Lihat Data">
                                                        Lihat Data
                                                    </a>

                                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#atur_sematan"
                                                        href="{{ $app->url->route('atur.ubah', ['uuid' => $tabel->atur_uuid]) }}" title="Ubah Data">
                                                        Ubah Data
                                                    </a>

                                                    {{-- <a class="isi-xhr" data-rekam="false" data-metode="POST" data-enkode="true"
                                        data-kirim="{{ '_token='.csrf_token() }}"
                                        href="{{ $app->url->route('atur.nonaktifkan', ['uuid' => $atur->atur_uuid]) }}">Non-aktifkan</a>
                                    --}}
                                                </div>
                                            </div>
                                        </th>
                                        <td>{{ $tabels->firstItem() + $nomor }}</td>
                                        <td>{{ $tabel->atur_jenis }}</td>
                                        <td>{{ $tabel->atur_butir }}</td>
                                        <td>{!! nl2br($tabel->atur_detail) !!}</td>
                                        <td>{{ $tabel->atur_status }}</td>
                                    </tr>

                                @empty
                                    <tr>
                                        <th></th>
                                        <td colspan="5">Tidak ada data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <button class="sekunder tcetak ringkas-tabel">Panjang/Pendekkan Tampilan Tabel</button>

                    <script>
                        (async () => {
                            while (!window.aplikasiSiap) {
                                await new Promise((resolve, reject) =>
                                    setTimeout(resolve, 1000));
                            }

                            pilDasar('.trek-data .pil-dasar');
                            pilSaja('.trek-data .pil-saja');
                            urutData('#atur_data_cariUrut', '#atur_data_cariUrut [data-indeks]');
                            formatTabel('#atur_tabel thead th', '#atur_tabel tbody tr');
                        })();
                    </script>

                    @include('pemberitahuan')
                    @include('komponen')
                @endfragment
            </div>
        @else
            <p class="kartu">Tidak ada data.</p>
        @endisset

        <div class="pintasan tcetak">
            <a class="tbl-btt" href="#" title="Kembali Ke Atas">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonpanahatas"></use>
                </svg>
            </a>

            <a class="isi-xhr" data-rekam="false" data-tujuan="#atur_sematan" href="{{ $app->url->route('atur.unggah') }}" title="Unggah Data">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonunggah"></use>
                </svg>
            </a>

            <a href="#" title="Unduh Data"
                onclick="event.preventDefault();lemparXHR({tujuan : '#atur_sematan', tautan : window.location.search ? window.location.pathname + window.location.search + '&unduh=excel' : window.location.pathname + '?unduh=excel', strim : true})">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonunduh"></use>
                </svg>
            </a>

            <a class="isi-xhr" data-rekam="false" data-tujuan="#atur_sematan" href="{{ $app->url->route('atur.tambah') }}" title="Tambah Data">
                <svg viewBox="0 0 24 24">
                    <use href="#ikontambah"></use>
                </svg>
            </a>
        </div>

        @isset($tabels)
            <script>
                (async () => {
                    while (!window.aplikasiSiap) {
                        await new Promise((resolve, reject) =>
                            setTimeout(resolve, 1000));
                    }

                    pilDasar('#form_atur_data_cari .pil-dasar');
                    pilCari('#form_atur_data_cari .pil-cari');
                    pilSaja('#form_atur_data_cari .pil-saja');
                    formatIsian('#form_atur_data_cari .isian :is(textarea,input[type=text],input[type=search])');
                })();
            </script>
        @endisset
    </div>
@endsection
