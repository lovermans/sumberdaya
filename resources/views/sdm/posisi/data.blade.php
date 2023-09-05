@extends('rangka')

@section('isi')
    <div id="sdm_posisi">
        <h4>Data Pengaturan Jabatan</h4>
        @isset($tabels)
            <div class="cari-data tcetak">
                <form class="form-xhr kartu" id="form_sdm_posisi_cari" data-tujuan="#sdm_posisi_tabels" data-frag="true" method="GET" action="{{ $app->url->current() }}">
                    <input name="fragment" type="hidden" value="sdm_posisi_tabels">

                    <details class="gspan-4" {{ $app->request->anyFilled(['lokasi', 'kontrak', 'posisi_status']) ? 'open' : '' }}>
                        <summary class="cari">
                            <div class="isian gspan-4">
                                <input id="sdm_posisi_cariKataKunci" name="kata_kunci" type="text" value="{{ $app->request->kata_kunci }}"
                                    aria-label="Cari Kata Kunci">

                                <button class="cari-cepat" id="tombol_cari_posisi_sdm" type="submit" title="Cari Data">
                                    <svg viewbox="0 0 24 24">
                                        <use href="#ikoncari"></use>
                                    </svg>
                                </button>
                            </div>
                        </summary>

                        <div class="kartu form gspan-4">
                            <div class="isian pendek">
                                <label for="sdm_posisi_cariStatus">Saring Status</label>

                                <select class="pil-dasar" id="sdm_posisi_cariStatus" name="posisi_status">
                                    <option selected disabled></option>
                                    <option @selected($app->request->posisi_status == 'AKTIF')>AKTIF</option>
                                    <option @selected($app->request->posisi_status == 'NON-AKTIF')>NON-AKTIF</option>
                                </select>

                                <span class="t-bantu">Pilih satu</span>
                            </div>

                            <div class="isian pendek">
                                <label for="sdm_posisi_cariStatusPenempatanSDM">Saring Lokasi</label>

                                <select class="pil-cari" id="sdm_posisi_cariStatusPenempatanSDM" name="lokasi[]" multiple>
                                    @foreach ($lokasis as $lokasi)
                                        <option @selected(in_array($lokasi->atur_butir, (array) $app->request->lokasi)) @class(['merah' => $lokasi->atur_status == 'NON-AKTIF'])>
                                            {{ $lokasi->atur_butir }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="isian pendek">
                                <label for="sdm_posisi_StatusKontrakSDM">Saring Jenis Kontrak</label>

                                <select class="pil-cari" id="sdm_posisi_StatusKontrakSDM" name="kontrak[]" multiple>
                                    @foreach ($kontraks as $kontrak)
                                        <option @selected(in_array($kontrak->atur_butir, (array) $app->request->kontrak)) @class(['merah' => $kontrak->atur_status == 'NON-AKTIF'])>
                                            {{ $kontrak->atur_butir }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="gspan-4"></div>

                            <button class="utama pelengkap" id="tombol_saring_posisi_sdm" type="submit" title="Saring Data">
                                <svg viewbox="0 0 24 24">
                                    <use href="#ikoncari"></use>
                                </svg>
                                SARING
                            </button>
                        </div>
                    </details>
                </form>
            </div>

            <div class="kartu scroll-margin" id="sdm_posisi_tabels">
                @fragment('sdm_posisi_tabels')
                    <b>
                        <i>
                            <small>
                                Jumlah SDM ({{ $app->request->anyFilled(['lokasi', 'kontrak']) ? 'sesuai data penyaringan' : 'global' }}) : Aktif =
                                {{ number_format($aktif, 0, ',', '.') }} Personil -> Non-Aktif =
                                {{ number_format($nonAktif, 0, ',', '.') }} Personil -> Total = {{ number_format($total, 0, ',', '.') }}
                                Personil.
                            </small>
                        </i>
                    </b>

                    <div class="scroll-margin" id="posisi-sdm_sematan"></div>

                    <div class="trek-data tcetak">
                        <span class="bph">
                            <label for="sdm_posisi_cariPerHalaman">Baris per halaman : </label>

                            <select class="pil-saja" id="sdm_posisi_cariPerHalaman" name="bph" form="form_sdm_posisi_cari"
                                onchange="getElementById('tombol_cari_posisi_sdm').click()">
                                <option>25</option>
                                <option @selected($tabels->perPage() == 50)>50</option>
                                <option @selected($tabels->perPage() == 75)>75</option>
                                <option @selected($tabels->perPage() == 100)>100</option>
                            </select>

                        </span>

                        <span class="ket">
                            {{ number_format($tabels->firstItem(), 0, ',', '.') }} -
                            {{ number_format($tabels->lastItem(), 0, ',', '.') }} dari {{ number_format($tabels->total(), 0, ',', '.') }} data
                        </span>

                        @if ($tabels->hasPages())
                            <span class="trek">
                                @if ($tabels->currentPage() > 1)
                                    <a class="isi-xhr" data-tujuan="#sdm_posisi_tabels" data-frag="true" href="{{ $tabels->url(1) }}" title="Awal">
                                        <svg viewbox="0 0 24 24">
                                            <use href="#ikonawal"></use>
                                        </svg>
                                    </a>
                                @endif

                                @if ($tabels->previousPageUrl())
                                    <a class="isi-xhr" data-tujuan="#sdm_posisi_tabels" data-frag="true" href="{{ $tabels->previousPageUrl() }}" title="Sebelumnya">
                                        <svg viewbox="0 0 24 24">
                                            <use href="#ikonmundur"></use>
                                        </svg>
                                    </a>
                                @endif

                                @if ($tabels->nextPageUrl())
                                    <a class="isi-xhr" data-tujuan="#sdm_posisi_tabels" data-frag="true" href="{{ $tabels->nextPageUrl() }}" title="Berikutnya">
                                        <svg viewbox="0 0 24 24">
                                            <use href="#ikonmaju"></use>
                                        </svg>
                                    </a>

                                    <a class="isi-xhr" data-tujuan="#sdm_posisi_tabels" data-frag="true" href="{{ $tabels->url($tabels->lastPage()) }}" title="Akhir">
                                        <svg viewbox="0 0 24 24">
                                            <use href="#ikonakhir"></use>
                                        </svg>
                                    </a>
                                @endif
                            </span>
                        @endif

                        <details class="gspan-4" {{ $app->request->anyFilled('urut') ? 'open' : '' }}>
                            <summary>Pengurutan :</summary>

                            <div class="kartu form" id="sdm_posisi_cariUrut">
                                <div class="isian" data-indeks="{{ $urutPergantian ? $indexPergantian : 'X' }}">
                                    <label for="sdm_posisi_cariUrutPergantian">{{ $urutPergantian ? $indexPergantian . '. ' : '' }}Urut % Pergantian</label>

                                    <select class="pil-dasar" id="sdm_posisi_cariUrutPergantian" name="urut[]" form="form_sdm_posisi_cari"
                                        onchange="getElementById('tombol_cari_posisi_sdm').click()">
                                        <option selected disabled></option>
                                        <option value="pergantian ASC" @selected(in_array('pergantian ASC', (array) $app->request->urut))>0 - 9</option>
                                        <option value="pergantian DESC" @selected(in_array('pergantian DESC', (array) $app->request->urut))>9 - 0</option>
                                    </select>
                                    <span class="t-bantu">Pilih satu</span>
                                </div>

                                <div class="isian" data-indeks="{{ $urutPosisi ? $indexPosisi : 'X' }}">
                                    <label for="sdm_posisi_cariUrutPosisi">{{ $urutPosisi ? $indexPosisi . '. ' : '' }}Urut
                                        Jabatan</label>

                                    <select class="pil-dasar" id="sdm_posisi_cariUrutPosisi" name="urut[]" form="form_sdm_posisi_cari"
                                        onchange="getElementById('tombol_cari_posisi_sdm').click()">
                                        <option selected disabled></option>
                                        <option value="posisi_nama ASC" @selected(in_array('posisi_nama ASC', (array) $app->request->urut))>A - Z</option>
                                        <option value="posisi_nama DESC" @selected(in_array('posisi_nama DESC', (array) $app->request->urut))>Z - A</option>
                                    </select>

                                    <span class="t-bantu">Pilih satu</span>
                                </div>
                                <div class="isian" data-indeks="{{ $urutAktif ? $indexAktif : 'X' }}">
                                    <label for="sdm_posisi_cariUrutAktif">
                                        {{ $urutAktif ? $indexAktif . '. ' : '' }}Urut Jml Aktif
                                    </label>

                                    <select class="pil-dasar" id="sdm_posisi_cariUrutAktif" name="urut[]" form="form_sdm_posisi_cari"
                                        onchange="getElementById('tombol_cari_posisi_sdm').click()">
                                        <option selected disabled></option>
                                        <option value="jml_aktif ASC" @selected(in_array('jml_aktif ASC', (array) $app->request->urut))>0 - 9</option>
                                        <option value="jml_aktif DESC" @selected(in_array('jml_aktif DESC', (array) $app->request->urut))>9 - 0</option>
                                    </select>

                                    <span class="t-bantu">Pilih satu</span>
                                </div>

                                <div class="isian" data-indeks="{{ $urutNonAktif ? $indexNonAktif : 'X' }}">
                                    <label for="sdm_posisi_cariUrutNonAktif">
                                        {{ $urutNonAktif ? $indexNonAktif . '. ' : '' }}Urut Jml Non-Aktif
                                    </label>

                                    <select class="pil-dasar" id="sdm_posisi_cariUrutNonAktif" name="urut[]" form="form_sdm_posisi_cari"
                                        onchange="getElementById('tombol_cari_posisi_sdm').click()">
                                        <option selected disabled></option>
                                        <option value="jml_nonaktif ASC" @selected(in_array('jml_nonaktif ASC', (array) $app->request->urut))>0 - 9</option>
                                        <option value="jml_nonaktif DESC" @selected(in_array('jml_nonaktif DESC', (array) $app->request->urut))>9 - 0</option>
                                    </select>

                                    <span class="t-bantu">Pilih satu</span>
                                </div>
                            </div>
                        </details>
                    </div>

                    <div class="data ringkas">
                        <table class="tabel" id="posisi-sdm_tabel">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>No</th>
                                    <th>Nama Jabatan</th>
                                    <th>Jml Aktif</th>
                                    <th>Jml Non-Aktif</th>
                                    <th>% Pergantian</th>
                                    <th>Jabatan Atasan</th>
                                    <th>Kode WLKP</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tabels as $nomor => $tabel)
                                    <tr @class(['merah' => $tabel->posisi_status == 'NON-AKTIF'])>
                                        <th>
                                            <div class="pil-aksi">
                                                <button id="{{ 'aksi_posisi_baris_' . $tabels->firstItem() + $nomor }}" title="Pilih Tindakan">
                                                    <svg viewbox="0 0 24 24">
                                                        <use href="#ikonmenuvert"></use>
                                                    </svg>
                                                </button>

                                                <div class="aksi">
                                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#posisi-sdm_sematan"
                                                        href="{{ $app->url->route('sdm.posisi.lihat', ['uuid' => $tabel->posisi_uuid]) }}" title="Lihat Data">
                                                        Lihat Data
                                                    </a>

                                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#posisi-sdm_sematan"
                                                        href="{{ $app->url->route('sdm.posisi.ubah', ['uuid' => $tabel->posisi_uuid]) }}" title="Ubah Data">
                                                        Ubah Data
                                                    </a>
                                                </div>
                                            </div>
                                        </th>

                                        <td>{{ $tabels->firstItem() + $nomor }}</td>
                                        <td>{{ $tabel->posisi_nama }}</td>

                                        <td>
                                            <u>
                                                <a class="isi-xhr"
                                                    href="{{ $app->url->route('sdm.penempatan.data-aktif', ['posisi' => $tabel->posisi_nama, ...$app->request->only(['lokasi', 'kontrak'])]) }}">
                                                    {{ number_format($tabel->jml_aktif, 0, ',', '.') }}
                                                </a>
                                            </u>
                                        </td>

                                        <td>
                                            <u>
                                                <a class="isi-xhr"
                                                    href="{{ $app->url->route('sdm.penempatan.data-nonaktif', ['posisi' => $tabel->posisi_nama, ...$app->request->only(['lokasi', 'kontrak'])]) }}">
                                                    {{ number_format($tabel->jml_nonaktif, 0, ',', '.') }}
                                                </a>
                                            </u>
                                        </td>

                                        <td>{{ number_format($tabel->pergantian, 2, ',', '.') }} %</td>
                                        <td>{{ $tabel->posisi_atasan }}</td>
                                        <td>{{ $tabel->posisi_wlkp }}</td>
                                        <td>{{ $tabel->posisi_status }}</td>
                                        <td>{!! nl2br($tabel->posisi_keterangan) !!}</td>
                                    </tr>

                                @empty
                                    <tr>
                                        <th></th>
                                        <td colspan="8">Tidak ada data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <button class="sekunder tcetak" onclick="ringkasTabel(this)">Panjang/Pendekkan Tampilan Tabel</button>

                    <script>
                        (async () => {
                            while (!window.aplikasiSiap) {
                                await new Promise((resolve, reject) =>
                                    setTimeout(resolve, 1000));
                            }

                            pilDasar('#sdm_posisi_tabels .pil-dasar');
                            pilSaja('#sdm_posisi_tabels .pil-saja');
                            urutData('#sdm_posisi_cariUrut', '#sdm_posisi_cariUrut [data-indeks]');
                            formatTabel('#posisi-sdm_tabel thead th', '#posisi-sdm_tabel tbody tr');
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
            <a href="#" title="Kembali Ke Atas" onclick="event.preventDefault();window.scrollTo(0,0)">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonpanahatas"></use>
                </svg>
            </a>

            <a class="isi-xhr" data-rekam="false" data-tujuan="#posisi-sdm_sematan" href="{{ $app->url->route('sdm.posisi.unggah') }}" title="Unggah Data">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonunggah"></use>
                </svg>
            </a>

            <a href="#" title="Unduh Data"
                onclick="event.preventDefault();lemparXHR({tujuan : '#posisi-sdm_sematan', tautan : window.location.search ? window.location.pathname + window.location.search + '&unduh=excel' : window.location.pathname + '?unduh=excel', strim : true})">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonunduh"></use>
                </svg>
            </a>

            <a class="isi-xhr" data-rekam="false" data-tujuan="#posisi-sdm_sematan" href="{{ $app->url->route('sdm.posisi.tambah') }}" title="Tambah Data">
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

                    pilDasar('#form_sdm_posisi_cari .pil-dasar');
                    pilCari('#form_sdm_posisi_cari .pil-cari');
                    pilSaja('#form_sdm_posisi_cari .pil-saja');
                    formatIsian('#form_sdm_posisi_cari .isian :is(textarea,input[type=text],input[type=search])');
                })();
            </script>
        @endisset
    </div>
@endsection
