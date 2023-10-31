@extends('rangka')

@section('isi')
    <div id="sdm_kepuasan">
        <h4>Data Kepuasan SDM</h4>
        @isset($tabels)
            <div class="cari-data tcetak">

                <form class="form-xhr kartu" id="form_sdm_kepuasan_cari" data-tujuan="#kepuasan-sdm_tabels" data-frag="true" data-blank="true" method="GET">
                    <input name="fragment" type="hidden" value="kepuasan-sdm_tabels">

                    <details class="gspan-4" {{ $app->request->anyFilled(['surveysdm_tahun', 'surveysdm_penempatan', 'surveysdm_kontrak']) ? 'open' : '' }}>

                        <summary class="cari">
                            <div class="isian gspan-4">
                                <input id="kata_kunci_nilai_sdm" name="kata_kunci" type="text" value="{{ $app->request->kata_kunci }}" aria-label="Cari Kata Kunci">

                                <button class="cari-cepat" id="tombol_cari_kepuasan" type="submit" title="Cari Data">
                                    <svg viewbox="0 0 24 24">
                                        <use href="#ikoncari"></use>
                                    </svg>
                                </button>
                            </div>
                        </summary>

                        <div class="kartu form">
                            <div class="isian normal">
                                <label for="sdm_kepuasan_cariTahun">Saring Tahun</label>

                                <select class="pil-cari" id="sdm_kepuasan_cariTahun" name="surveysdm_tahun[]" multiple>
                                    @foreach (range(2020, date('Y')) as $tahun)
                                        <option @selected(in_array($tahun, (array) $app->request->surveysdm_tahun))>
                                            {{ $tahun }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="isian normal">
                                <label for="sdm_kepuasan_cariPenempatan">Saring Penempatan</label>

                                <select class="pil-cari" id="sdm_kepuasan_cariPenempatan" name="surveysdm_penempatan[]" multiple>
                                    @foreach ($lokasis as $lokasi)
                                        <option @selected(in_array($lokasi->atur_butir, (array) $app->request->surveysdm_penempatan)) @class(['merah' => $lokasi->atur_status == 'NON-AKTIF'])>
                                            {{ $lokasi->atur_butir }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="isian normal">
                                <label for="sdm_kepuasan_cariKontrak">Saring Status SDM</label>

                                <select class="pil-cari" id="sdm_kepuasan_cariKontrak" name="surveysdm_kontrak[]" multiple>
                                    @foreach ($statusSDMs as $statusSDM)
                                        <option @selected(in_array($statusSDM->atur_butir, (array) $app->request->surveysdm_kontrak)) @class(['merah' => $statusSDM->atur_status == 'NON-AKTIF'])>
                                            {{ $statusSDM->atur_butir }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="gspan-4"></div>

                            <button class="utama pelengkap" id="tombol_saring_kepuasan" type="submit" title="Saring Data">
                                <svg viewbox="0 0 24 24">
                                    <use href="#ikoncari"></use>
                                </svg>
                                SARING
                            </button>
                        </div>
                    </details>
                </form>
            </div>

            <div class="kartu scroll-margin" id="kepuasan-sdm_tabels">
                @fragment('kepuasan-sdm_tabels')
                    @unless ($halamanAkun ?? null)
                        <b>
                            <i>
                                <small>
                                    Jumlah SDM
                                    ({{ $app->request->anyFilled(['surveysdm_tahun', 'surveysdm_penempatan', 'surveysdm_kontrak']) ? 'sesuai data penyaringan' : 'global' }})
                                    : Organik = {{ number_format($jumlahOrganik, 0, ',', '.') }} Personil | Outsource =
                                    {{ number_format($jumlahOS, 0, ',', '.') }} Personil.
                                </small>
                            </i>
                        </b>
                    @endunless
                    <div class="scroll-margin" id="kepuasan-sdm_sematan"></div>

                    <div class="trek-data tcetak">
                        @unless ($halamanAkun ?? null)
                            <span class="bph">
                                <label for="sdm_kepuasan_cariPerHalaman">Baris per halaman : </label>

                                <select class="pil-saja" id="sdm_kepuasan_cariPerHalaman" name="bph" form="form_sdm_kepuasan_cari"
                                    onchange="getElementById('tombol_cari_kepuasan').click()">
                                    <option>25</option>
                                    <option @selected($tabels->perPage() == 50)>50</option>
                                    <option @selected($tabels->perPage() == 75)>75</option>
                                    <option @selected($tabels->perPage() == 100)>100</option>
                                </select>
                            </span>
                        @endunless

                        <span class="ket">
                            {{ number_format($tabels->firstItem(), 0, ',', '.') }} -
                            {{ number_format($tabels->lastItem(), 0, ',', '.') }} dari {{ number_format($tabels->total(), 0, ',', '.') }}
                            data
                        </span>

                        @if ($tabels->hasPages())
                            <span class="trek">
                                @if ($tabels->currentPage() > 1)
                                    <a class="isi-xhr" data-tujuan="#kepuasan-sdm_tabels" data-frag="true" href="{{ $tabels->url(1) }}" title="Awal">
                                        <svg viewbox="0 0 24 24">
                                            <use href="#ikonawal"></use>
                                        </svg>
                                    </a>
                                @endif

                                @if ($tabels->previousPageUrl())
                                    <a class="isi-xhr" data-tujuan="#kepuasan-sdm_tabels" data-frag="true" href="{{ $tabels->previousPageUrl() }}" title="Sebelumnya">
                                        <svg viewbox="0 0 24 24">
                                            <use href="#ikonmundur"></use>
                                        </svg>
                                    </a>
                                @endif

                                @if ($tabels->nextPageUrl())
                                    <a class="isi-xhr" data-tujuan="#kepuasan-sdm_tabels" data-frag="true" href="{{ $tabels->nextPageUrl() }}" title="Berikutnya">
                                        <svg viewbox="0 0 24 24">
                                            <use href="#ikonmaju"></use>
                                        </svg>
                                    </a>

                                    <a class="isi-xhr" data-tujuan="#kepuasan-sdm_tabels" data-frag="true" href="{{ $tabels->url($tabels->lastPage()) }}" title="Akhir">
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
                                <div class="kartu form" id="sdm_kepuasan_cariUrut">
                                    <div class="isian" data-indeks="{{ $urutTahun ? $indexTahun : 'X' }}">
                                        <label for="sdm_kepuasan_tambah_cariUrutTahun">{{ $urutTahun ? $indexTahun . '. ' : '' }}Urut Tahun</label>
                                        <select class="pil-dasar" id="sdm_kepuasan_tambah_cariUrutTahun" name="urut[]" form="form_sdm_kepuasan_cari"
                                            onchange="getElementById('tombol_cari_kepuasan').click()">
                                            <option selected disabled></option>
                                            <option value="surveysdm_tahun ASC" @selected(in_array('surveysdm_tahun ASC', (array) $app->request->urut))>0 - 9</option>
                                            <option value="surveysdm_tahun DESC" @selected(in_array('surveysdm_tahun DESC', (array) $app->request->urut))>9 - 0</option>
                                        </select>
                                        <span class="t-bantu">Pilih satu</span>
                                    </div>
                                </div>
                            </details>
                        @endunless
                    </div>

                    <span class="biru">Biru</span> : Outsource.

                    <div class="data ringkas">
                        <table class="tabel" id="kepuasan-sdm_tabel">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>No</th>
                                    @unless ($halamanAkun ?? null)
                                        <th>Identitas</th>
                                    @endunless
                                    <th>Tahun</th>
                                    <th>Tingkat Kepuasan</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tabels as $nomor => $tabel)
                                    <tr @class(['biru' => str()->contains($tabel->penempatan_kontrak, 'OS-')])>
                                        <th>
                                            <div class="pil-aksi">
                                                <button id="{{ 'aksi_nilai_baris_' . $tabels->firstItem() + $nomor }}" title="Pilih Tindakan">
                                                    <svg viewbox="0 0 24 24">
                                                        <use href="#ikonmenuvert"></use>
                                                    </svg>
                                                </button>

                                                <div class="aksi">
                                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#kepuasan-sdm_sematan"
                                                        href="{{ $app->url->route('sdm.kepuasan.lihat', ['uuid' => $tabel->surveysdm_uuid]) }}" title="Lihat/Ubah">
                                                        Buka/Ubah Data
                                                    </a>
                                                </div>
                                            </div>
                                        </th>
                                        <td>{{ $tabels->firstItem() + $nomor }}</td>
                                        @unless ($halamanAkun ?? null)
                                            <td class="profil">
                                                <div @class(['merah' => $tabel->sdm_tgl_berhenti])>
                                                    <a class="isi-xhr taut-akun" href="{{ $app->url->route('sdm.akun', ['uuid' => $tabel->sdm_uuid]) }}">
                                                        <img src="{{ $app->filesystem->exists('sdm/foto-profil/' . $tabel->surveysdm_no_absen . '.webp')
                                                            ? $app->url->route('sdm.tautan-foto-profil', [
                                                                'berkas_foto_profil' =>
                                                                    $tabel->surveysdm_no_absen . '.webp' . '?id=' . filemtime($app->storagePath('app/sdm/foto-profil/' . $tabel->surveysdm_no_absen . '.webp')),
                                                            ])
                                                            : $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/blank.webp')) }}"
                                                            title="{{ $tabel->sdm_nama ?? 'foto akun' }}" alt="{{ $tabel->sdm_nama ?? 'foto akun' }}"
                                                            @class([
                                                                'akun',
                                                                'svg' => !$app->filesystem->exists(
                                                                    'sdm/foto-profil/' . $tabel->surveysdm_no_absen . '.webp'),
                                                            ]) loading="lazy">

                                                        <small>{{ $tabel->surveysdm_no_absen }} : {{ $tabel->sdm_nama }}</small>
                                                    </a>
                                                    <br>
                                                    {{ $tabel->penempatan_lokasi }} {{ $tabel->penempatan_kontrak }} -
                                                    {{ $tabel->penempatan_posisi }}
                                                    {{ $tabel->sdm_tgl_berhenti ? '(NON-AKTIF)' : '' }}
                                                </div>
                                            </td>
                                        @endunless
                                        <td>
                                            {{ $tabel->surveysdm_tahun }}
                                        </td>
                                        <td>
                                            {{ $tabel->surveysdm_skor }}
                                        </td>
                                        <td>
                                            {!! nl2br($tabel->surveysdm_keterangan) !!}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <th></th>
                                        <td colspan="{{ isset($halamanAkun) ? '4' : '5' }}">Tidak ada data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <button class="sekunder tcetak ringkas-tabel">Panjang/Pendekkan
                        Tampilan Tabel</button>

                    <script>
                        (async () => {
                            while (!window.aplikasiSiap) {
                                await new Promise((resolve, reject) =>
                                    setTimeout(resolve, 1000));
                            }

                            pilDasar('#kepuasan-sdm_tabels .pil-dasar');
                            pilSaja('#kepuasan-sdm_tabels .pil-saja');
                            urutData('#sdm_kepuasan_cariUrut', '#sdm_kepuasan_cariUrut [data-indeks]');
                            formatTabel('#kepuasan-sdm_tabel thead th', '#kepuasan-sdm_tabel tbody tr');
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
            <a class="isi-xhr" data-rekam="false" data-tujuan="#kepuasan-sdm_sematan" href="{{ $app->url->route('sdm.kepuasan.unggah') }}"
                title="Unggah Data Penilaian SDM">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonunggah"></use>
                </svg>
            </a>
            <a href="#" title="Unduh Data"
                onclick="event.preventDefault();lemparXHR({tujuan : '#kepuasan-sdm_sematan', tautan : window.location.search ? window.location.pathname + window.location.search + '&unduh=excel' : window.location.pathname + '?unduh=excel', strim : true})">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonunduh"></use>
                </svg>
            </a>
            <a class="isi-xhr" data-rekam="false" data-tujuan="#kepuasan-sdm_sematan" href="{{ $app->url->route('sdm.kepuasan.tambah') }}"
                title="Tambah Data">
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

                    pilDasar('#form_sdm_kepuasan_cari .pil-dasar');
                    pilCari('#form_sdm_kepuasan_cari .pil-cari');
                    pilSaja('#form_sdm_kepuasan_cari .pil-saja');
                    formatIsian('#form_sdm_kepuasan_cari .isian :is(textarea,input[type=text],input[type=search])');
                })();
            </script>
        @endisset
    </div>
@endsection
