@extends('rangka')

@section('isi')
    <div id="sdm_permintaan_tambah">
        <h4>Kelola Permintaan Tambah SDM</h4>
        @isset($tabels)
            <div class="cari-data tcetak">
                <form class="form-xhr kartu" id="form_sdm_permintaan_tambah_cari" data-tujuan="#tambah_sdm_tabels" data-frag="true" method="GET"
                    action="{{ $app->url->current() }}">
                    <input name="fragment" type="hidden" value="tambah_sdm_tabels">

                    <details class="gspan-4"
                        {{ $app->request->anyFilled(['tgl_diusulkan_mulai', 'tambahsdm_status', 'tgl_diusulkan_sampai', 'tambahsdm_penempatan', 'tambahsdm_laju', 'posisi'])
                            ? 'open'
                            : '' }}>

                        <summary class="cari">
                            <div class="isian gspan-4">
                                <input id="kata_kunci_permintaan_tambah_sdm" name="kata_kunci" type="text" value="{{ $app->request->kata_kunci }}"
                                    aria-label="Cari Kata Kunci">

                                <button class="cari-cepat" id="tombol_cari_permintaan_sdm" type="submit" title="Cari Data">
                                    <svg viewbox="0 0 24 24">
                                        <use href="#ikoncari"></use>
                                    </svg>
                                </button>
                            </div>
                        </summary>

                        <div class="kartu form">
                            <div class="isian pendek">
                                <label for="sdm_permintaan_tambah_cariLokasi">Saring Penempatan</label>

                                <select class="pil-cari" id="sdm_permintaan_tambah_cariLokasi" name="tambahsdm_penempatan[]" multiple>
                                    @foreach ($lokasis as $lokasi)
                                        <option @selected(in_array($lokasi->atur_butir, (array) $app->request->tambahsdm_penempatan)) @class(['merah' => $lokasi->atur_status == 'NON-AKTIF'])>
                                            {{ $lokasi->atur_butir }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="isian pendek">
                                <label for="sdm_permintaan_tambah_cariPemenuhan">Saring Pemenuhan</label>

                                <select class="pil-cari" id="sdm_permintaan_tambah_cariPemenuhan" name="tambahsdm_laju">
                                    <option selected disabled></option>
                                    <option @selected($app->request->tambahsdm_laju == 'BELUM TERPENUHI')>BELUM TERPENUHI</option>
                                    <option @selected($app->request->tambahsdm_laju == 'SUDAH TERPENUHI')>SUDAH TERPENUHI</option>
                                    <option @selected($app->request->tambahsdm_laju == 'KELEBIHAN')>KELEBIHAN</option>
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="isian pendek">
                                <label for="sdm_permintaan_tambah_cariTanggalUsulMulai">Tanggal Diusulkan Mulai</label>

                                <input id="sdm_permintaan_tambah_cariTanggalUsulMulai" name="tgl_diusulkan_mulai" type="date"
                                    value="{{ $app->request->old('tgl_diusulkan_mulai', $app->request->tgl_diusulkan_mulai ?? null) }}">

                                <span class="t-bantu">Isi tanggal</span>
                            </div>

                            <div class="isian pendek">
                                <label for="sdm_permintaan_tambah_cariTanggalUsulSampai">Tanggal Diusulkan Sampai</label>

                                <input id="sdm_permintaan_tambah_cariTanggalUsulSampai" name="tgl_diusulkan_sampai" type="date"
                                    value="{{ $app->request->old('tgl_diusulkan_sampai', $app->request->tgl_diusulkan_sampai ?? null) }}">

                                <span class="t-bantu">Isi tanggal</span>
                            </div>

                            <div class="isian panjang">
                                <label for="sdm_permintaan_tambah_cariStatusJabatanSDM">Saring Jabatan</label>

                                <select class="pil-cari" id="sdm_permintaan_tambah_cariStatusJabatanSDM" name="posisi[]" multiple>
                                    @foreach ($posisis as $posisi)
                                        <option @selected(in_array($posisi->posisi_nama, (array) $app->request->posisi)) @class(['merah' => $posisi->posisi_status == 'NON-AKTIF'])>
                                            {{ $posisi->posisi_nama }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="isian pendek">
                                <label for="sdm_permintaan_tambah_cariStatus">Saring Status</label>

                                <select class="pil-dasar" id="sdm_permintaan_tambah_cariStatus" name="tambahsdm_status[]" multiple>
                                    @foreach ($statuses as $status)
                                        <option @selected(in_array($status->atur_butir, (array) $app->request->tambahsdm_status))>
                                            {{ $status->atur_butir }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="gspan-4"></div>

                            <button class="utama pelengkap" id="tombol_saring_permintaan_sdm" type="submit" title="Saring Data">
                                <svg viewbox="0 0 24 24">
                                    <use href="#ikoncari"></use>
                                </svg>
                                SARING
                            </button>
                        </div>
                    </details>
                </form>
            </div>

            <div class="kartu scroll-margin" id="tambah_sdm_tabels">
                @fragment('tambah_sdm_tabels')
                    <b><i><small>Total permintaan tambah SDM
                                ({{ $app->request->anyFilled([
                                    'kata_kunci',
                                    'tambahsdm_status',
                                    'tgl_diusulkan_mulai',
                                    'tgl_diusulkan_sampai',
                                    'tambahsdm_penempatan',
                                    'tambahsdm_laju',
                                    'posisi',
                                ])
                                    ? 'sesuai data pencarian & penyaringan'
                                    : 'global' }})
                                : Kebutuhan = {{ number_format($kebutuhan, 0, ',', '.') }} Personil -> Terpenuhi =
                                {{ number_format($terpenuhi, 0, ',', '.') }} Personil -> Selisih =
                                {{ number_format($selisih, 0, ',', '.') }} Personil.</small></i></b>

                    <div class="scroll-margin" id="permintaan-sdm_sematan"></div>

                    <div class="trek-data tcetak">
                        <span class="bph">
                            <label class="ket" for="sdm_permintaan_tambah_cariPerHalaman">Baris per halaman : </label>

                            <select class="pil-saja" id="sdm_permintaan_tambah_cariPerHalaman" name="bph" form="form_sdm_permintaan_tambah_cari"
                                onchange="getElementById('tombol_cari_permintaan_sdm').click()">
                                <option>25</option>
                                <option @selected($tabels->perPage() == 50)>50</option>
                                <option @selected($tabels->perPage() == 75)>75</option>
                                <option @selected($tabels->perPage() == 100)>100</option>
                            </select>
                        </span>

                        <span class="ket">{{ number_format($tabels->firstItem(), 0, ',', '.') }} -
                            {{ number_format($tabels->lastItem(), 0, ',', '.') }} dari {{ number_format($tabels->total(), 0, ',', '.') }} data</span>

                        @if ($tabels->hasPages())
                            <span class="trek">
                                @if ($tabels->currentPage() > 1)
                                    <a class="isi-xhr" data-tujuan="#tambah_sdm_tabels" data-frag="true" href="{{ $tabels->url(1) }}" title="Awal">
                                        <svg viewbox="0 0 24 24">
                                            <use href="#ikonawal"></use>
                                        </svg>
                                    </a>
                                @endif

                                @if ($tabels->previousPageUrl())
                                    <a class="isi-xhr" data-tujuan="#tambah_sdm_tabels" data-frag="true" href="{{ $tabels->previousPageUrl() }}" title="Sebelumnya">
                                        <svg viewbox="0 0 24 24">
                                            <use href="#ikonmundur"></use>
                                        </svg>
                                    </a>
                                @endif

                                @if ($tabels->nextPageUrl())
                                    <a class="isi-xhr" data-tujuan="#tambah_sdm_tabels" data-frag="true" href="{{ $tabels->nextPageUrl() }}" title="Berikutnya">
                                        <svg viewbox="0 0 24 24">
                                            <use href="#ikonmaju"></use>
                                        </svg>
                                    </a>

                                    <a class="isi-xhr" data-tujuan="#tambah_sdm_tabels" data-frag="true" href="{{ $tabels->url($tabels->lastPage()) }}"
                                        title="Akhir">
                                        <svg viewbox="0 0 24 24">
                                            <use href="#ikonakhir"></use>
                                        </svg>
                                    </a>
                                @endif
                            </span>
                        @endif

                        <details class="gspan-4" {{ $app->request->anyFilled('urut') ? 'open' : '' }}>
                            <summary>Pengurutan :</summary>

                            <div class="kartu form" id="sdm_permintaan_tambah_cariUrut">
                                <div class="isian" data-indeks="{{ $urutNomor ? $indexNomor : 'X' }}">
                                    <label for="sdm_permintaan_tambah_cariUrutNomor">{{ $urutNomor ? $indexNomor . '. ' : '' }}Urut
                                        Nomor Permintaan</label>

                                    <select class="pil-dasar" id="sdm_permintaan_tambah_cariUrutNomor" name="urut[]" form="form_sdm_permintaan_tambah_cari"
                                        onchange="getElementById('tombol_cari_permintaan_sdm').click()">
                                        <option selected disabled></option>
                                        <option value="tambahsdm_no ASC" @selected(in_array('tambahsdm_no ASC', (array) $app->request->urut))>0 - 9</option>
                                        <option value="tambahsdm_no DESC" @selected(in_array('tambahsdm_no DESC', (array) $app->request->urut))>9 - 0</option>
                                    </select>

                                    <span class="t-bantu">Pilih satu</span>
                                </div>

                                <div class="isian" data-indeks="{{ $urutJumlah ? $indexJumlah : 'X' }}">
                                    <label for="sdm_permintaan_tambah_cariUrutJumlah">{{ $urutJumlah ? $indexJumlah . '. ' : '' }}Urut
                                        Jumlah Dibutuhkan</label>

                                    <select class="pil-dasar" id="sdm_permintaan_tambah_cariUrutJumlah" name="urut[]" form="form_sdm_permintaan_tambah_cari"
                                        onchange="getElementById('tombol_cari_permintaan_sdm').click()">
                                        <option selected disabled></option>
                                        <option value="tambahsdm_jumlah ASC" @selected(in_array('tambahsdm_jumlah ASC', (array) $app->request->urut))>0 - 9</option>
                                        <option value="tambahsdm_jumlah DESC" @selected(in_array('tambahsdm_jumlah DESC', (array) $app->request->urut))>9 - 0</option>
                                    </select>

                                    <span class="t-bantu">Pilih satu</span>
                                </div>

                                <div class="isian" data-indeks="{{ $urutPosisi ? $indexPosisi : 'X' }}">
                                    <label for="sdm_permintaan_tambah_cariUrutPosisi">{{ $urutPosisi ? $indexPosisi . '. ' : '' }}Urut
                                        Posisi Dibutuhkan</label>

                                    <select class="pil-dasar" id="sdm_permintaan_tambah_cariUrutPosisi" name="urut[]" form="form_sdm_permintaan_tambah_cari"
                                        onchange="getElementById('tombol_cari_permintaan_sdm').click()">
                                        <option selected disabled></option>
                                        <option value="tambahsdm_posisi ASC" @selected(in_array('tambahsdm_posisi ASC', (array) $app->request->urut))>A - Z</option>
                                        <option value="tambahsdm_posisi DESC" @selected(in_array('tambahsdm_posisi DESC', (array) $app->request->urut))>Z - A</option>
                                    </select>

                                    <span class="t-bantu">Pilih satu</span>
                                </div>

                                <div class="isian" data-indeks="{{ $urutPenempatan ? $indexPenempatan : 'X' }}">
                                    <label for="sdm_permintaan_tambah_cariUrutPenempatan">
                                        {{ $urutPenempatan ? $indexPenempatan . '. ' : '' }}Urut Penempatan SDM
                                    </label>

                                    <select class="pil-dasar" id="sdm_permintaan_tambah_cariUrutPenempatan" name="urut[]" form="form_sdm_permintaan_tambah_cari"
                                        onchange="getElementById('tombol_cari_permintaan_sdm').click()">
                                        <option selected disabled></option>
                                        <option value="tambahsdm_penempatan ASC" @selected(in_array('tambahsdm_penempatan ASC', (array) $app->request->urut))>A - Z</option>
                                        <option value="tambahsdm_penempatan DESC" @selected(in_array('tambahsdm_penempatan DESC', (array) $app->request->urut))>Z - A</option>
                                    </select>

                                    <span class="t-bantu">Pilih satu</span>
                                </div>
                            </div>
                        </details>
                    </div>

                    <span class="merah">Merah</span> : Kelebihan. <span class="biru">Biru</span> : Sudah Terpenuhi.

                    <div class="data ringkas">
                        <table class="tabel" id="permintaan-sdm_tabel">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>No</th>
                                    <th>Permintaan</th>
                                    <th>Rincian</th>
                                    <th>Lainnya</th>
                                    <th>Tindakan Cepat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tabels as $nomor => $tabel)
                                    <tr @class([
                                        'merah' => $tabel->tambahsdm_jumlah < $tabel->tambahsdm_terpenuhi,
                                        'biru' => $tabel->tambahsdm_jumlah == $tabel->tambahsdm_terpenuhi,
                                    ])>
                                        <th>
                                            <div class="pil-aksi">
                                                <button id="{{ 'aksi_tsdm_baris_' . $tabels->firstItem() + $nomor }}" title="Pilih Tindakan">
                                                    <svg viewbox="0 0 24 24">
                                                        <use href="#ikonmenuvert"></use>
                                                    </svg>
                                                </button>

                                                <div class="aksi">
                                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#permintaan-sdm_sematan"
                                                        href="{{ $app->url->route('sdm.permintaan-tambah-sdm.lihat', ['uuid' => $tabel->tambahsdm_uuid]) }}"
                                                        title="Lihat Data">Buka Data</a>

                                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#permintaan-sdm_sematan"
                                                        href="{{ $app->url->route('sdm.permintaan-tambah-sdm.ubah', ['uuid' => $tabel->tambahsdm_uuid]) }}"
                                                        title="Ubah Data">Ubah Data</a>
                                                </div>
                                            </div>
                                        </th>

                                        <td>{{ $tabels->firstItem() + $nomor }}</td>

                                        <td class="profil">
                                            <b>Pemohon</b> : <br>
                                            <a class="isi-xhr taut-akun" href="{{ $app->url->route('sdm.akun', ['uuid' => $tabel->sdm_uuid]) }}">
                                                <img src="{{ $app->filesystem->exists('sdm/foto-profil/' . $tabel->tambahsdm_sdm_id . '.webp')
                                                    ? $app->url->route('sdm.tautan-foto-profil', [
                                                        'berkas_foto_profil' =>
                                                            $tabel->tambahsdm_sdm_id . '.webp' . '?id=' . filemtime($app->storagePath('app/sdm/foto-profil/' . $tabel->tambahsdm_sdm_id . '.webp')),
                                                    ])
                                                    : $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/blank.webp')) }}"
                                                    title="{{ $tabel->sdm_nama ?? 'foto akun' }}" alt="{{ $tabel->sdm_nama ?? 'foto akun' }}"
                                                    @class([
                                                        'akun',
                                                        'svg' => !$app->filesystem->exists(
                                                            'sdm/foto-profil/' . $tabel->tambahsdm_sdm_id . '.webp'),
                                                    ]) loading="lazy">

                                                <small>{{ $tabel->tambahsdm_sdm_id }} - {{ $tabel->sdm_nama }}</small>
                                            </a>
                                            <br>
                                            <b>Nomor</b> : {{ $tabel->tambahsdm_no }}<br>
                                            <b>Pemohon</b> : {{ $tabel->tambahsdm_sdm_id }} - {{ $tabel->sdm_nama }}<br>
                                            <b>Diusulkan</b> :
                                            {{ strtoupper($app->date->make($tabel->tambahsdm_tgl_diusulkan)?->translatedFormat('d F Y')) }}<br>
                                            <b>Dibutuhkan</b> :
                                            {{ strtoupper($app->date->make($tabel->tambahsdm_tgl_dibutuhkan)?->translatedFormat('d F Y')) }}
                                        </td>

                                        <td>
                                            <b>Penempatan</b> : {{ $tabel->tambahsdm_penempatan }}<br>
                                            <b>Posisi</b> : {{ $tabel->tambahsdm_posisi }}<br>
                                            <b>Jml Kebutuhan</b> : {{ $tabel->tambahsdm_jumlah }}<br>
                                            <b>Jml Terpenuhi</b> :
                                            <u>
                                                <a class="isi-xhr" href="{{ $app->url->route('sdm.penempatan.riwayat', ['kata_kunci' => $tabel->tambahsdm_no]) }}">
                                                    {{ $tabel->tambahsdm_terpenuhi }}
                                                </a>
                                            </u><br>
                                            <b>Pemenuhan Terbaru</b> : {{ strtoupper($app->date->make($tabel->pemenuhan_terkini)?->translatedFormat('d F Y')) }}
                                        </td>

                                        <td>
                                            <b>Alasan</b> : {!! nl2br($tabel->tambahsdm_alasan) !!}<br>
                                            <b>Keterangan</b> : {!! nl2br($tabel->tambahsdm_keterangan) !!}
                                        </td>

                                        <td>
                                            <form class="form-xhr" data-singkat="true" method="POST"
                                                action="{{ $app->url->route('sdm.permintaan-tambah-sdm.ubah', ['uuid' => $tabel->tambahsdm_uuid]) }}">
                                                <input name="_token" type="hidden" value="{{ $app->request->session()->token() }}">
                                                <div class="isian pendek">
                                                    <label class="tcetak" for="{{ 'ubah_cepat_status_tsdm_' . $tabels->firstItem() + $nomor }}">Ubah Status</label>

                                                    <select class="pil-saja" id="{{ 'ubah_cepat_status_tsdm_' . $tabels->firstItem() + $nomor }}"
                                                        name="tambahsdm_status" required onchange="getElementById('kirim-{{ $tabel->tambahsdm_uuid }}').click()">
                                                        <option selected disabled></option>

                                                        @if (!in_array($app->request->old('tambahsdm_status', $tabel->tambahsdm_status ?? null), (array) $statuses->pluck('atur_butir')->toArray()))
                                                            <option class="merah" value="{{ $app->request->old('tambahsdm_status', $tabel->tambahsdm_status ?? null) }}"
                                                                selected>
                                                                {{ $app->request->old('tambahsdm_status', $permin->tambahsdm_status ?? null) }}
                                                            </option>
                                                        @endif

                                                        @foreach ($statuses as $status)
                                                            <option @selected($status->atur_butir == $app->request->old('tambahsdm_status', $tabel->tambahsdm_status ?? null))>
                                                                {{ $status->atur_butir }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    <button id="kirim-{{ $tabel->tambahsdm_uuid }}" type="submit" sembunyikan></button>
                                                </div>
                                            </form>

                                            <p class="tcetak">
                                                <a class="isi-xhr utama" data-rekam="false" data-laju="true" data-tujuan="#permintaan-sdm_sematan"
                                                    href="{{ $app->url->route('sdm.permintaan-tambah-sdm.formulir', ['uuid' => $tabel->tambahsdm_uuid]) }}"
                                                    title="Cetak Formulir Permintaan Tambah SDM">
                                                    <svg viewbox="0 0 24 24">
                                                        <use href="#ikoncetak"></use>
                                                    </svg>
                                                    FORMULIR
                                                </a>
                                            </p>

                                            @if ($app->filesystem->exists($berkasPerminTambahSDM = 'sdm/permintaan-tambah-sdm/berkas/' . $tabel->tambahsdm_no . '.pdf'))
                                                <p class="tcetak">
                                                    <a class="sekunder tcetak"
                                                        href="{{ $app->url->route('sdm.berkas', ['berkas' => $berkasPerminTambahSDM . '?id=' . filemtime($app->storagePath('app/' . $berkasPerminTambahSDM))]) }}"
                                                        title="Unduh Berkas Terunggah" target="_blank">
                                                        <svg viewBox="0 0 24 24">
                                                            <use href="#ikonunduh"></use>
                                                        </svg>
                                                        BERKAS
                                                    </a>
                                                </p>
                                            @endif
                                        </td>
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

                            pilDasar('#tambah_sdm_tabels .pil-dasar');
                            pilSaja('#tambah_sdm_tabels .pil-saja');
                            urutData('#sdm_permintaan_tambah_cariUrut', '#sdm_permintaan_tambah_cariUrut [data-indeks]');
                            formatTabel('#permintaan-sdm_tabel thead th', '#permintaan-sdm_tabel tbody tr');
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

            <a href="#" title="Unduh Data"
                onclick="event.preventDefault();lemparXHR({tujuan : '#permintaan-sdm_sematan', tautan : window.location.search ? window.location.pathname + window.location.search + '&unduh=excel' : window.location.pathname + '?unduh=excel', strim : true})">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonunduh"></use>
                </svg>
            </a>

            <a class="isi-xhr" data-rekam="false" data-tujuan="#permintaan-sdm_sematan" href="{{ $app->url->route('sdm.permintaan-tambah-sdm.tambah') }}"
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

                    pilDasar('#form_sdm_permintaan_tambah_cari .pil-dasar');
                    pilCari('#form_sdm_permintaan_tambah_cari .pil-cari');
                    pilSaja('#form_sdm_permintaan_tambah_cari .pil-saja');
                    formatIsian('#form_sdm_permintaan_tambah_cari .isian :is(textarea,input[type=text],input[type=search])');
                })();
            </script>
        @endisset

    </div>
@endsection
