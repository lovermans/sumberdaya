@extends('rangka')

@section('isi')
    <div id="sdm_penempatan_status">
        <h4>Kelola Penempatan SDM</h4>
        @isset($tabels)
            <div class="cari-data tcetak">
                <form class="form-xhr kartu" id="form_sdm_penempatan_status_cari" data-tujuan="#riwa-penem-sdm_tabels" data-frag="true" data-blank="true" method="GET">
                    <input name="fragment" type="hidden" value="riwa-penem-sdm_tabels">

                    <details class="gspan-4"
                        {{ $app->request->anyFilled([
                            'lokasi',
                            'kontrak',
                            'kategori',
                            'pangkat',
                            'kelamin',
                            'posisi',
                            'agama',
                            'kawin',
                            'warganegara',
                            'pendidikan',
                            'disabilitas',
                        ])
                            ? 'open'
                            : '' }}>

                        <summary class="cari">
                            <div class="isian gspan-4">
                                <input id="kata_kunci_penempatan_sdm" name="kata_kunci" type="text" value="{{ $app->request->kata_kunci }}"
                                    aria-label="Cari Kata Kunci">

                                <button class="cari-cepat" id="tombol_cari_status_penempatan" type="submit" title="Cari Data">
                                    <svg viewbox="0 0 24 24">
                                        <use href="#ikoncari"></use>
                                    </svg>
                                </button>
                            </div>
                        </summary>

                        <div class="kartu form">
                            <div class="isian pendek">
                                <label for="sdm_penempatan_status_cariStatusPenempatanSDM">Saring Lokasi</label>

                                <select class="pil-cari" id="sdm_penempatan_status_cariStatusPenempatanSDM" name="lokasi[]" multiple>
                                    @foreach ($penempatans as $lokasi)
                                        <option @selected(in_array($lokasi->atur_butir, (array) $app->request->lokasi)) @class(['merah' => $lokasi->atur_status == 'NON-AKTIF'])>
                                            {{ $lokasi->atur_butir }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="isian pendek">
                                <label for="sdm_penempatan_status_cariStatusKontrakSDM">Saring Jenis Kontrak</label>

                                <select class="pil-cari" id="sdm_penempatan_status_cariStatusKontrakSDM" name="kontrak[]" multiple>
                                    @foreach ($kontraks as $kontrak)
                                        <option @selected(in_array($kontrak->atur_butir, (array) $app->request->kontrak)) @class(['merah' => $kontrak->atur_status == 'NON-AKTIF'])>
                                            {{ $kontrak->atur_butir }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="isian normal">
                                <label for="sdm_penempatan_status_cariStatusKategoriSDM">Saring Kategori</label>

                                <select class="pil-cari" id="sdm_penempatan_status_cariStatusKategoriSDM" name="kategori[]" multiple>
                                    @foreach ($kategoris as $kategori)
                                        <option @selected(in_array($kategori->atur_butir, (array) $app->request->kategori)) @class(['merah' => $kategori->atur_status == 'NON-AKTIF'])>
                                            {{ $kategori->atur_butir }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="isian normal">
                                <label for="sdm_penempatan_status_cariStatusPangkatSDM">Saring Pangkat</label>

                                <select class="pil-cari" id="sdm_penempatan_status_cariStatusPangkatSDM" name="pangkat[]" multiple>
                                    @foreach ($pangkats as $pangkat)
                                        <option @selected(in_array($pangkat->atur_butir, (array) $app->request->pangkat)) @class(['merah' => $pangkat->atur_status == 'NON-AKTIF'])>
                                            {{ $pangkat->atur_butir }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="isian panjang">
                                <label for="sdm_penempatan_status_cariStatusJabatanSDM">Saring Jabatan</label>

                                <select class="pil-cari" id="sdm_penempatan_status_cariStatusJabatanSDM" name="posisi[]" multiple>
                                    @foreach ($posisis as $posisi)
                                        <option @selected(in_array($posisi->posisi_nama, (array) $app->request->posisi)) @class(['merah' => $posisi->posisi_status == 'NON-AKTIF'])>
                                            {{ $posisi->posisi_nama }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="isian kecil">
                                <label for="sdm_penempatan_status_cariStatusKelaminSDM">Saring Kelamin</label>

                                <select class="pil-saja" id="sdm_penempatan_status_cariStatusKelaminSDM" name="kelamin[]" multiple>
                                    @foreach ($kelamins as $kelamin)
                                        <option @selected(in_array($kelamin->atur_butir, (array) $app->request->kelamin)) @class(['merah' => $kelamin->atur_status == 'NON-AKTIF'])>
                                            {{ $kelamin->atur_butir }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="isian pendek">
                                <label for="sdm_penempatan_status_cariAgamaSDM">Saring Agama</label>

                                <select class="pil-saja" id="sdm_penempatan_status_cariAgamaSDM" name="agama[]" multiple>
                                    @foreach ($agamas as $agama)
                                        <option @selected(in_array($agama->atur_butir, (array) $app->request->agama)) @class(['merah' => $agama->atur_status == 'NON-AKTIF'])>
                                            {{ $agama->atur_butir }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="isian pendek">
                                <label for="sdm_penempatan_status_cariStatusKawinSDM">Saring Status Kawin</label>

                                <select class="pil-saja" id="sdm_penempatan_status_cariStatusKawinSDM" name="kawin[]" multiple>
                                    @foreach ($kawins as $kawin)
                                        <option @selected(in_array($kawin->atur_butir, (array) $app->request->kawin)) @class(['merah' => $kawin->atur_status == 'NON-AKTIF'])>
                                            {{ $kawin->atur_butir }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="isian pendek">
                                <label for="sdm_penempatan_status_cariPendidikanSDM">Saring Pendidikan</label>

                                <select class="pil-saja" id="sdm_penempatan_status_cariPendidikanSDM" name="pendidikan[]" multiple>
                                    @foreach ($pendidikans as $pendidikan)
                                        <option @selected(in_array($pendidikan->atur_butir, (array) $app->request->pendidikan)) @class(['merah' => $pendidikan->atur_status == 'NON-AKTIF'])>
                                            {{ $pendidikan->atur_butir }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="isian normal">
                                <label for="sdm_penempatan_status_cariWarganegaraSDM">Saring Warganegara</label>

                                <select class="pil-saja" id="sdm_penempatan_status_cariWarganegaraSDM" name="warganegara[]" multiple>
                                    @foreach ($warganegaras as $warganegara)
                                        <option @selected(in_array($warganegara->atur_butir, (array) $app->request->warganegara)) @class(['merah' => $warganegara->atur_status == 'NON-AKTIF'])>
                                            {{ $warganegara->atur_butir }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="isian normal">
                                <label for="sdm_penempatan_status_cariDisabilitasSDM">Saring Disabilitas</label>

                                <select class="pil-saja" id="sdm_penempatan_status_cariDisabilitasSDM" name="disabilitas[]" multiple>
                                    @foreach ($disabilitases as $disabilitas)
                                        <option @selected(in_array($disabilitas->atur_butir, (array) $app->request->disabilitas)) @class(['merah' => $disabilitas->atur_status == 'NON-AKTIF'])>
                                            {{ $disabilitas->atur_butir }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="gspan-4"></div>

                            <button class="utama pelengkap" id="tombol_saring_status_penempatan" type="submit" title="Saring Data">
                                <svg viewbox="0 0 24 24">
                                    <use href="#ikoncari"></use>
                                </svg>
                                SARING
                            </button>
                        </div>
                    </details>
                </form>
            </div>

            <div class="kartu scroll-margin" id="riwa-penem-sdm_tabels">
                @fragment('riwa-penem-sdm_tabels')
                    @unless ($halamanAkun ?? null)
                        <b><i><small>Jumlah SDM
                                    ({{ $app->request->anyFilled([
                                        'lokasi',
                                        'kontrak',
                                        'kategori',
                                        'pangkat',
                                        'kelamin',
                                        'posisi',
                                        'agama',
                                        'kawin',
                                        'warganegara',
                                        'pendidikan',
                                        'disabilitas',
                                    ])
                                        ? 'sesuai data penyaringan'
                                        : 'global' }})
                                    : Organik = {{ number_format($jumlahOrganik, 0, ',', '.') }} Personil | Outsource =
                                    {{ number_format($jumlahOS, 0, ',', '.') }} Personil.</small></i></b>
                    @endunless

                    <div class="scroll-margin" id="sdm_penem_riwa_sematan"></div>

                    <div class="trek-data tcetak">
                        @unless ($halamanAkun ?? null)
                            <div class="saring-cepat">
                                <select class="pil-saja tombol" id="sdm_penempatan_status_cariStatusAktifSDM"
                                    onchange="if (this.value !== '') lemparXHR({rekam : true, tujuan : '#riwa-penem-sdm_tabels', tautan : this.value, fragmen : true})">
                                    <option
                                        value="{{ $app->url->route('sdm.penempatan.riwayat', $app->request->merge(['fragment' => 'riwa-penem-sdm_tabels'])->except(['unduh', 'page', 'bph'])) }}"
                                        @selected($app->request->routeIs('sdm.penempatan.riwayat'))>
                                        SEMUA RIWAYAT
                                    </option>

                                    <option
                                        value="{{ $app->url->route('sdm.penempatan.data-aktif', $app->request->merge(['fragment' => 'riwa-penem-sdm_tabels'])->except(['unduh', 'page', 'bph'])) }}"
                                        @selected($app->request->routeIs('sdm.penempatan.data-aktif'))>
                                        AKTIF
                                    </option>

                                    <option
                                        value="{{ $app->url->route('sdm.penempatan.data-nonaktif', $app->request->merge(['fragment' => 'riwa-penem-sdm_tabels'])->except(['unduh', 'page', 'bph'])) }}"
                                        @selected($app->request->routeIs('sdm.penempatan.data-nonaktif'))>
                                        NON-AKTIF
                                    </option>

                                    <option
                                        value="{{ $app->url->route('sdm.penempatan.data-akanhabis', $app->request->merge(['fragment' => 'riwa-penem-sdm_tabels'])->except(['unduh', 'page', 'bph'])) }}"
                                        @selected($app->request->routeIs('sdm.penempatan.data-akanhabis'))>
                                        AKAN HABIS
                                    </option>

                                    <option
                                        value="{{ $app->url->route('sdm.penempatan.data-kadaluarsa', $app->request->merge(['fragment' => 'riwa-penem-sdm_tabels'])->except(['unduh', 'page', 'bph'])) }}"
                                        @selected($app->request->routeIs('sdm.penempatan.data-kadaluarsa'))>
                                        KADALUARSA
                                    </option>

                                    <option
                                        value="{{ $app->url->route('sdm.penempatan.data-baru', $app->request->merge(['fragment' => 'riwa-penem-sdm_tabels'])->except(['unduh', 'page', 'bph'])) }}"
                                        @selected($app->request->routeIs('sdm.penempatan.data-baru'))>
                                        BELUM DITEMPATKAN
                                    </option>

                                    <option
                                        value="{{ $app->url->route('sdm.penempatan.data-batal', $app->request->merge(['fragment' => 'riwa-penem-sdm_tabels'])->except(['unduh', 'page', 'bph'])) }}"
                                        @selected($app->request->routeIs('sdm.penempatan.data-batal'))>
                                        BATAL DITEMPATKAN
                                    </option>

                                    <option
                                        value="{{ $app->url->route('sdm.penempatan.riwayat-nyata', $app->request->merge(['fragment' => 'riwa-penem-sdm_tabels'])->except(['unduh', 'page', 'bph'])) }}"
                                        @selected($app->request->routeIs('sdm.penempatan.riwayat-nyata'))>
                                        MASA KERJA NYATA (SEMUA)
                                    </option>

                                    <option
                                        value="{{ $app->url->route('sdm.penempatan.riwayat-nyata-aktif', $app->request->merge(['fragment' => 'riwa-penem-sdm_tabels'])->except(['unduh', 'page', 'bph'])) }}"
                                        @selected($app->request->routeIs('sdm.penempatan.riwayat-nyata-aktif'))>
                                        MASA KERJA NYATA (AKTIF)
                                    </option>

                                    <option
                                        value="{{ $app->url->route('sdm.penempatan.riwayat-nyata-nonaktif', $app->request->merge(['fragment' => 'riwa-penem-sdm_tabels'])->except(['unduh', 'page', 'bph'])) }}"
                                        @selected($app->request->routeIs('sdm.penempatan.riwayat-nyata-nonaktif'))>
                                        MASA KERJA NYATA (NON-AKTIF)
                                    </option>
                                </select>
                            </div>

                            <span class="bph">
                                <label for="sdm_penempatan_status_cariPerHalaman">Baris per halaman : </label>

                                <select class="pil-saja" id="sdm_penempatan_status_cariPerHalaman" name="bph" form="form_sdm_penempatan_status_cari"
                                    onchange="getElementById('tombol_cari_status_penempatan').click()">
                                    <option>100</option>

                                    <option @selected($tabels->perPage() == 250)>250</option>
                                    <option @selected($tabels->perPage() == 500)>500</option>
                                    <option @selected($tabels->perPage() == 1000)>1000</option>
                                </select>

                            </span>
                        @endunless

                        <span class="ket">{{ number_format($tabels->firstItem(), 0, ',', '.') }} -
                            {{ number_format($tabels->lastItem(), 0, ',', '.') }} dari {{ number_format($tabels->total(), 0, ',', '.') }} data</span>

                        @if ($tabels->hasPages())
                            <span class="trek">
                                @if ($tabels->currentPage() > 1)
                                    <a class="isi-xhr" data-tujuan="#riwa-penem-sdm_tabels" data-frag="true" href="{{ $tabels->url(1) }}" title="Awal">
                                        <svg viewbox="0 0 24 24">
                                            <use href="#ikonawal"></use>
                                        </svg>
                                    </a>
                                @endif

                                @if ($tabels->previousPageUrl())
                                    <a class="isi-xhr" data-tujuan="#riwa-penem-sdm_tabels" data-frag="true" href="{{ $tabels->previousPageUrl() }}"
                                        title="Sebelumnya">
                                        <svg viewbox="0 0 24 24">
                                            <use href="#ikonmundur"></use>
                                        </svg>
                                    </a>
                                @endif

                                @if ($tabels->nextPageUrl())
                                    <a class="isi-xhr" data-tujuan="#riwa-penem-sdm_tabels" data-frag="true" href="{{ $tabels->nextPageUrl() }}" title="Berikutnya">
                                        <svg viewbox="0 0 24 24">
                                            <use href="#ikonmaju"></use>
                                        </svg>
                                    </a>

                                    <a class="isi-xhr" data-tujuan="#riwa-penem-sdm_tabels" data-frag="true" href="{{ $tabels->url($tabels->lastPage()) }}"
                                        title="Akhir">
                                        <svg viewbox="0 0 24 24">
                                            <use href="#ikonakhir"></use>
                                        </svg>
                                    </a>
                                @endif
                            </span>
                        @endif

                        @unless ($halamanAkun ?? null)
                            <details class="gspan-4" {{ $app->request->anyFilled('urut') ? 'open' : '' }}>
                                <summary>Pengurutan :</summary>
                                <div class="kartu form" id="sdm_penempatan_status_urut">
                                    <div class="isian" data-indeks="{{ $urutAbsen ? $indexAbsen : 'X' }}">
                                        <label for="sdm_penempatan_status_urutAbsen">{{ $urutAbsen ? $indexAbsen . '. ' : '' }}Urut Nomor
                                            Absen</label>
                                        <select class="pil-dasar" id="sdm_penempatan_status_urutAbsen" name="urut[]" form="form_sdm_penempatan_status_cari"
                                            onchange="getElementById('tombol_cari_status_penempatan').click()">
                                            <option selected disabled></option>
                                            <option value="sdm_no_absen ASC" @selected(in_array('sdm_no_absen ASC', (array) $app->request->urut))>0 - 9</option>
                                            <option value="sdm_no_absen DESC" @selected(in_array('sdm_no_absen DESC', (array) $app->request->urut))>9 - 0</option>
                                        </select>

                                        <span class="t-bantu">Pilih satu</span>
                                    </div>

                                    <div class="isian" data-indeks="{{ $urutMasuk ? $indexMasuk : 'X' }}">
                                        <label for="sdm_penempatan_status_urutMasuk">{{ $urutMasuk ? $indexMasuk . '. ' : '' }}Urut
                                            Tanggal Masuk</label>

                                        <select class="pil-dasar" id="sdm_penempatan_status_urutMasuk" name="urut[]" form="form_sdm_penempatan_status_cari"
                                            onchange="getElementById('tombol_cari_status_penempatan').click()">
                                            <option selected disabled></option>
                                            <option value="sdm_tgl_gabung ASC" @selected(in_array('sdm_tgl_gabung ASC', (array) $app->request->urut))>Lama - Baru</option>
                                            <option value="sdm_tgl_gabung DESC" @selected(in_array('sdm_tgl_gabung DESC', (array) $app->request->urut))>Baru - Lama</option>
                                        </select>

                                        <span class="t-bantu">Pilih satu</span>
                                    </div>

                                    <div class="isian" data-indeks="{{ $urutLahir ? $indexLahir : 'X' }}">
                                        <label for="sdm_penempatan_status_urutLahir">{{ $urutLahir ? $indexLahir . '. ' : '' }}Urut
                                            Tanggal Lahir</label>

                                        <select class="pil-dasar" id="sdm_penempatan_status_urutLahir" name="urut[]" form="form_sdm_penempatan_status_cari"
                                            onchange="getElementById('tombol_cari_status_penempatan').click()">
                                            <option selected disabled></option>
                                            <option value="sdm_tgl_lahir ASC" @selected(in_array('sdm_tgl_lahir ASC', (array) $app->request->urut))>Lama - Baru</option>
                                            <option value="sdm_tgl_lahir DESC" @selected(in_array('sdm_tgl_lahir DESC', (array) $app->request->urut))>Baru - Lama</option>
                                        </select>

                                        <span class="t-bantu">Pilih satu</span>
                                    </div>

                                    <div class="isian" data-indeks="{{ $urutKeluar ? $indexKeluar : 'X' }}">
                                        <label for="sdm_penempatan_status_urutKeluar">
                                            {{ $urutKeluar ? $indexKeluar . '. ' : '' }}Urut Tanggal Keluar
                                        </label>

                                        <select class="pil-dasar" id="sdm_penempatan_status_urutKeluar" name="urut[]" form="form_sdm_penempatan_status_cari"
                                            onchange="getElementById('tombol_cari_status_penempatan').click()">
                                            <option selected disabled></option>
                                            <option value="sdm_tgl_berhenti ASC" @selected(in_array('sdm_tgl_berhenti ASC', (array) $app->request->urut))>Lama - Baru</option>
                                            <option value="sdm_tgl_berhenti DESC" @selected(in_array('sdm_tgl_berhenti DESC', (array) $app->request->urut))>Baru - Lama</option>
                                        </select>

                                        <span class="t-bantu">Pilih satu</span>
                                    </div>
                                </div>
                            </details>
                        @endunless
                    </div>

                    <span class="merah">Merah</span> : Non-Aktif. <span class="oranye">Oranye</span> : Kontrak Kadaluarsa/Akan
                    Habis. <span class="biru">Biru</span> : <i>Outsource</i>.

                    <div class="data ringkas">
                        <table class="tabel" id="riwa-penem-sdm_tabel">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>No</th>

                                    @unless ($halamanAkun ?? null)
                                        <th>Identitas</th>
                                    @endunless

                                    <th>Posisi</th>
                                    <th>Rincian</th>

                                    @unless ($halamanAkun ?? null)
                                        <th>Informasi Lain</th>

                                        <th>Tambahan</th>
                                    @endunless

                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tabels as $nomor => $tabel)
                                    <tr @class([
                                        'merah' => $tabel->sdm_tgl_berhenti,
                                        'oranye' =>
                                            $tabel->penempatan_selesai &&
                                            $tabel->penempatan_selesai <= $app->date->today()->addDay(40),
                                        'biru' => str()->startsWith($tabel->penempatan_kontrak, 'OS-'),
                                    ])>
                                        <th>
                                            <div class="pil-aksi">
                                                <button
                                                    id="{{ 'aksi_penempatan_baris_' .rescue(function () use ($tabels, $nomor) {return $tabels->firstItem() + $nomor;},$loop->iteration,false) }}"
                                                    title="Pilih Tindakan">
                                                    <svg viewbox="0 0 24 24">
                                                        <use href="#ikonmenuvert"></use>
                                                    </svg>
                                                </button>

                                                <div class="aksi">
                                                    @isset($tabel->penempatan_uuid)
                                                        <a class="isi-xhr" data-rekam="false" data-tujuan="#sdm_penem_riwa_sematan"
                                                            href="{{ $app->url->route('sdm.penempatan.lihat', ['uuid' => $tabel->penempatan_uuid]) }}" title="Lihat Data">
                                                            Buka Data
                                                        </a>

                                                        <a class="isi-xhr" data-rekam="false" data-tujuan="#sdm_penem_riwa_sematan"
                                                            href="{{ $app->url->route('sdm.penempatan.ubah', ['uuid' => $tabel->penempatan_uuid]) }}" title="Ubah Data">
                                                            Ubah Penempatan
                                                        </a>
                                                    @endisset

                                                    @unless ($tabel->sdm_uuid == $app->request->user()->sdm_uuid)
                                                        <a class="isi-xhr" data-rekam="false" data-tujuan="#sdm_penem_riwa_sematan"
                                                            href="{{ $app->url->route('sdm.penempatan.tambah', ['uuid' => $tabel->sdm_uuid]) }}" title="Tambah Data">
                                                            Tambah Penempatan
                                                        </a>
                                                    @endunless
                                                </div>
                                            </div>
                                        </th>
                                        <td>{{ rescue(
                                            function () use ($tabels, $nomor) {
                                                return $tabels->firstItem() + $nomor;
                                            },
                                            $loop->iteration,
                                            false,
                                        ) }}
                                        </td>

                                        @unless ($halamanAkun ?? null)
                                            <td class="profil">
                                                <a class="isi-xhr taut-akun" href="{{ $app->url->route('sdm.akun', ['uuid' => $tabel->sdm_uuid]) }}">
                                                    <img src="{{ $app->filesystem->exists('sdm/foto-profil/' . $tabel->sdm_no_absen . '.webp')
                                                        ? $app->url->route('sdm.tautan-foto-profil', [
                                                            'berkas_foto_profil' => $tabel->sdm_no_absen . '.webp' . '?id=' . filemtime($app->storagePath('app/sdm/foto-profil/' . $tabel->sdm_no_absen . '.webp')),
                                                        ])
                                                        : $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/blank.webp')) }}"
                                                        title="{{ $tabel->sdm_nama ?? 'foto akun' }}" alt="{{ $tabel->sdm_nama ?? 'foto akun' }}"
                                                        @class([
                                                            'akun',
                                                            'svg' => !$app->filesystem->exists(
                                                                'sdm/foto-profil/' . $tabel->sdm_no_absen . '.webp'),
                                                        ]) loading="lazy">

                                                    <small>{{ $tabel->sdm_no_absen }} : {{ $tabel->sdm_nama }}</small>
                                                </a>
                                                <br>
                                                <b>No Permintaan</b> :
                                                @if ($tabel->sdm_no_permintaan)
                                                    <u>
                                                        <a class="isi-xhr"
                                                            href="{{ $app->url->route('sdm.permintaan-tambah-sdm.data', ['kata_kunci' => $tabel->sdm_no_permintaan]) }}"
                                                            aria-label="Permintaan Tambah SDM No {{ $tabel->sdm_no_permintaan }}">
                                                            {{ $tabel->sdm_no_permintaan }}
                                                        </a>
                                                    </u>
                                                @endif
                                                <br>
                                                <b>Tgl Masuk</b> : {{ strtoupper($app->date->make($tabel->sdm_tgl_gabung)?->translatedFormat('d F Y')) }}<br>
                                                <b>Tgl Keluar</b> : {{ strtoupper($app->date->make($tabel->sdm_tgl_berhenti)?->translatedFormat('d F Y')) }}<br>
                                                <b>PHK</b> : {{ $tabel->sdm_jenis_berhenti }}<br>
                                                <b>Ket PHK</b> : {{ $tabel->sdm_ket_berhenti }}
                                            </td>
                                        @endunless

                                        <td>
                                            <b>Lokasi</b> : {{ $tabel->penempatan_lokasi }}<br>
                                            <b>Posisi</b> : {{ $tabel->penempatan_posisi }}<br>
                                            <b>Kode WLKP</b> : {{ $tabel->posisi_wlkp }}<br>
                                            <b>Status</b> : {{ $tabel->penempatan_kontrak }}<br>
                                            <b>Mulai</b> : {{ strtoupper($app->date->make($tabel->penempatan_mulai)?->translatedFormat('d F Y')) }}<br>
                                            <b>Selesai</b> : {{ strtoupper($app->date->make($tabel->penempatan_selesai)?->translatedFormat('d F Y')) }}<br>
                                            <b>Ke</b> : {{ $tabel->penempatan_ke }}
                                        </td>

                                        <td>
                                            <b>Kategori</b> : {{ $tabel->penempatan_kategori }}<br>
                                            <b>Pangkat</b> : {{ $tabel->penempatan_pangkat }}<br>
                                            <b>Golongan</b> : {{ $tabel->penempatan_golongan }}<br>
                                            <b>Grup</b> : {{ $tabel->penempatan_grup }}<br>
                                            <b>Usia</b> : {{ $tabel->usia }} Tahun<br>
                                            <b>Masa Kerja</b> : {{ $tabel->masa_kerja }} Tahun<br>
                                            <b>Masa Aktif</b> : {{ $tabel->masa_aktif }} Tahun
                                        </td>

                                        @unless ($halamanAkun ?? null)
                                            <td>
                                                <b>E-KTP/Passport</b> :
                                                <u>
                                                    <a class="isi-xhr" href="{{ $app->url->route('sdm.penempatan.riwayat', ['kata_kunci' => $tabel->sdm_no_ktp]) }}">
                                                        {{ $tabel->sdm_no_ktp }}
                                                    </a>
                                                </u><br>
                                                <b>Warganegara</b> : {{ $tabel->sdm_warganegara }}<br>
                                                <b>Lahir</b> : {{ $tabel->sdm_tempat_lahir }},
                                                {{ strtoupper($app->date->make($tabel->sdm_tgl_lahir)?->translatedFormat('d F Y')) }}<br>
                                                <b>Kelamin</b> : {{ $tabel->sdm_kelamin }}<br>
                                                <b>Agama</b> : {{ $tabel->sdm_agama }}<br>
                                                <b>Status Kawin</b> : {{ $tabel->sdm_status_kawin }}<br>
                                                <b>Pendidikan</b> : {{ $tabel->sdm_pendidikan }}<br>
                                                <b>Jurusan</b> : {{ $tabel->sdm_jurusan }}
                                            </td>

                                            <td>
                                                <b>Telepon</b> : {{ $tabel->sdm_telepon }}<br>
                                                <b>Email</b> : {{ $tabel->email }}<br>
                                                <b>No Jamsostek</b> : {{ $tabel->sdm_no_jamsostek }}<br>
                                                <b>No BPJS</b> : {{ $tabel->sdm_no_bpjs }}<br>
                                                <b>Jml Anak</b> : {{ $tabel->sdm_jml_anak }}<br>
                                                <b>Uk Seragam</b> : {{ $tabel->sdm_uk_seragam }}<br>
                                                <b>Uk Sepatu</b> : {{ $tabel->sdm_uk_sepatu }}<br>
                                                <b>Disabilitas</b> : {{ $tabel->sdm_disabilitas }}<br>
                                                <b>ID Atasan</b> : {{ $tabel->sdm_id_atasan }}
                                            </td>
                                        @endunless

                                        <td>{!! nl2br($tabel->penempatan_keterangan) !!}</td>
                                    </tr>

                                @empty
                                    <tr>
                                        <th></th>
                                        <td colspan="{{ isset($halamanAkun) ? '4' : '7' }}">Tidak ada data.</td>
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
                            urutData('#sdm_penempatan_status_urut', '#sdm_penempatan_status_urut [data-indeks]');
                            formatTabel('#riwa-penem-sdm_tabel thead th', '#riwa-penem-sdm_tabel tbody tr');
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

            <a class="isi-xhr" data-rekam="false" data-tujuan="#sdm_penem_riwa_sematan" href="{{ $app->url->route('sdm.penempatan.unggah') }}"
                title="Unggah Data Penempatan SDM">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonunggah"></use>
                </svg>
            </a>

            <a href="#" title="Unduh Data"
                onclick="event.preventDefault();lemparXHR({tujuan : '#sdm_penem_riwa_sematan', tautan : window.location.search ? window.location.pathname + window.location.search + '&unduh=excel' : window.location.pathname + '?unduh=excel', strim : true})">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonunduh"></use>
                </svg>
            </a>

            <a class="isi-xhr" href="{{ $app->url->route('register') }}" title="Tambah Data SDM">
                <svg viewBox="0 0 24 24">
                    <use href="#ikontambahorang"></use>
                </svg>
            </a>

            <a class="isi-xhr" data-rekam="false" data-laju="true" data-tujuan="#sdm_penem_riwa_sematan"
                href="{{ $app->url->route('sdm.penempatan.statistik') }}" title="Unduh Statistik Penempatan SDM ">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonstatistik"></use>
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

                    pilDasar('#form_sdm_penempatan_status_cari .pil-dasar');
                    pilSaja('#form_sdm_penempatan_status_cari .pil-saja');
                    pilCari('#form_sdm_penempatan_status_cari .pil-cari');
                    formatIsian('#form_sdm_penempatan_status_cari .isian :is(textarea,input[type=text],input[type=search])');
                })();
            </script>
        @endisset

    </div>
@endsection
