@extends('rangka')

@section('isi')
    <div id="sdm_sanksi">
        <h4>Data Riwayat Sanksi SDM</h4>
        @isset($tabels)
            <div class="cari-data tcetak">

                <form class="form-xhr kartu" id="form_sdm_sanksi_cari" data-tujuan="#sanksi-sdm_tabels" data-frag="true" data-blank="true" method="GET">
                    <input name="fragment" type="hidden" value="sanksi-sdm_tabels">

                    <details class="gspan-4"
                        {{ $app->request->anyFilled(['sanksi_jenis', 'sanksi_penempatan', 'status_sdm', 'sanksi_status', 'tgl_sanksi_mulai', 'tgl_sanksi_sampai'])
                            ? 'open'
                            : '' }}>

                        <summary class="cari">
                            <div class="isian gspan-4">
                                <input id="kata_kunci_sanksi_sdm" name="kata_kunci" type="text" value="{{ $app->request->kata_kunci }}" aria-label="Cari Kata Kunci">

                                <button class="cari-cepat" id="tombol_cari_sanksi" type="submit" title="Cari Data">
                                    <svg viewbox="0 0 24 24">
                                        <use href="#ikoncari"></use>
                                    </svg>
                                </button>
                            </div>
                        </summary>

                        <div class="kartu form">
                            <div class="isian normal">
                                <label for="sdm_sanksi_cariJenis">Saring Jenis Sanksi</label>

                                <select class="pil-cari" id="sdm_sanksi_cariJenis" name="sanksi_jenis[]" multiple>
                                    @foreach ($jenisSanksis as $jenisSanksi)
                                        <option @selected(in_array($jenisSanksi->atur_butir, (array) $app->request->sanksi_jenis)) @class(['merah' => $jenisSanksi->atur_status == 'NON-AKTIF'])>
                                            {{ $jenisSanksi->atur_butir }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="isian normal">
                                <label for="sdm_sanksi_cariStatusSanksi">Saring Status Sanksi</label>

                                <select class="pil-dasar" id="sdm_sanksi_cariStatusSanksi" name="sanksi_status">
                                    <option selected disabled></option>
                                    <option @selected($app->request->sanksi_status == 'AKTIF')>AKTIF</option>
                                    <option @selected($app->request->sanksi_status == 'BERAKHIR')>BERAKHIR</option>
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="isian normal">
                                <label for="sdm_sanksi_cariLokasi">Saring Penempatan</label>

                                <select class="pil-cari" id="sdm_sanksi_cariLokasi" name="sanksi_penempatan[]" multiple>
                                    @foreach ($lokasis as $lokasi)
                                        <option @selected(in_array($lokasi->atur_butir, (array) $app->request->sanksi_penempatan)) @class(['merah' => $lokasi->atur_status == 'NON-AKTIF'])>
                                            {{ $lokasi->atur_butir }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="isian normal">
                                <label for="sdm_sanksi_cariStatusSDM">Saring Status SDM</label>

                                <select class="pil-cari" id="sdm_sanksi_cariStatusSDM" name="status_sdm[]" multiple>
                                    @foreach ($statusSDMs as $statusSDM)
                                        <option @selected(in_array($statusSDM->atur_butir, (array) $app->request->status_sdm)) @class(['merah' => $statusSDM->atur_status == 'NON-AKTIF'])>
                                            {{ $statusSDM->atur_butir }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="t-bantu">Pilih satu atau lebih</span>
                            </div>

                            <div class="isian normal">
                                <label for="sdm_sanksi_cariTanggalMulai">Terbit Sanksi Mulai</label>
                                <input id="sdm_sanksi_cariTanggalMulai" name="tgl_sanksi_mulai" type="date"
                                    value="{{ $app->request->old('tgl_sanksi_mulai', $app->request->tgl_sanksi_mulai ?? null) }}">
                                <span class="t-bantu">Isi tanggal</span>
                            </div>

                            <div class="isian normal">
                                <label for="sdm_sanksi_cariTanggalSampai">Terbit Sanksi Sampai</label>
                                <input id="sdm_sanksi_cariTanggalSampai" name="tgl_sanksi_sampai" type="date"
                                    value="{{ $app->request->old('tgl_sanksi_sampai', $app->request->tgl_sanksi_sampai ?? null) }}">
                                <span class="t-bantu">Isi tanggal</span>
                            </div>

                            <div class="gspan-4"></div>

                            <button class="utama pelengkap" id="tombol_saring_sanksi" type="submit" title="Saring Data">
                                <svg viewbox="0 0 24 24">
                                    <use href="#ikoncari"></use>
                                </svg>
                                SARING
                            </button>
                        </div>
                    </details>
                </form>
            </div>

            <div class="kartu scroll-margin" id="sanksi-sdm_tabels">
                @fragment('sanksi-sdm_tabels')
                    @unless ($halamanAkun ?? null)
                        <b>
                            <i>
                                <small>
                                    Jumlah SDM
                                    ({{ $app->request->anyFilled(['sanksi_jenis', 'sanksi_penempatan', 'status_sdm', 'sanksi_status', 'tgl_sanksi_mulai', 'tgl_sanksi_sampai'])
                                        ? 'sesuai data penyaringan'
                                        : 'global' }})
                                    : Organik = {{ number_format($jumlahOrganik, 0, ',', '.') }} Personil | Outsource =
                                    {{ number_format($jumlahOS, 0, ',', '.') }} Personil.
                                </small>
                            </i>
                        </b>
                    @endunless
                    <div class="scroll-margin" id="sanksi-sdm_sematan"></div>

                    <div class="trek-data tcetak">
                        @unless ($halamanAkun ?? null)
                            <span class="bph">
                                <label for="sdm_sanksi_cariPerHalaman">Baris per halaman : </label>

                                <select class="pil-saja" id="sdm_sanksi_cariPerHalaman" name="bph" form="form_sdm_sanksi_cari"
                                    onchange="getElementById('tombol_cari_sanksi').click()">
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
                                    <a class="isi-xhr" data-tujuan="#sanksi-sdm_tabels" data-frag="true" href="{{ $tabels->url(1) }}" title="Awal">
                                        <svg viewbox="0 0 24 24">
                                            <use href="#ikonawal"></use>
                                        </svg>
                                    </a>
                                @endif

                                @if ($tabels->previousPageUrl())
                                    <a class="isi-xhr" data-tujuan="#sanksi-sdm_tabels" data-frag="true" href="{{ $tabels->previousPageUrl() }}" title="Sebelumnya">
                                        <svg viewbox="0 0 24 24">
                                            <use href="#ikonmundur"></use>
                                        </svg>
                                    </a>
                                @endif

                                @if ($tabels->nextPageUrl())
                                    <a class="isi-xhr" data-tujuan="#sanksi-sdm_tabels" data-frag="true" href="{{ $tabels->nextPageUrl() }}" title="Berikutnya">
                                        <svg viewbox="0 0 24 24">
                                            <use href="#ikonmaju"></use>
                                        </svg>
                                    </a>

                                    <a class="isi-xhr" data-tujuan="#sanksi-sdm_tabels" data-frag="true" href="{{ $tabels->url($tabels->lastPage()) }}"
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
                                <div class="kartu form" id="sdm_sanksi_cariUrut">
                                    <div class="isian" data-indeks="{{ $urutTanggalMulai ? $indexTanggalMulai : 'X' }}">
                                        <label for="sdm_sanksi_tambah_cariUrutTanggalMulai">
                                            {{ $urutTanggalMulai ? $indexTanggalMulai . '. ' : '' }}Urut Tanggal Mulai Sanksi
                                        </label>

                                        <select class="pil-dasar" id="sdm_sanksi_tambah_cariUrutTanggalMulai" name="urut[]" form="form_sdm_sanksi_cari"
                                            onchange="getElementById('tombol_cari_sanksi').click()">
                                            <option selected disabled></option>
                                            <option value="sanksi_mulai ASC" @selected(in_array('sanksi_mulai ASC', (array) $app->request->urut))>Lama - Baru</option>
                                            <option value="sanksi_mulai DESC" @selected(in_array('sanksi_mulai DESC', (array) $app->request->urut))>Baru - Lama</option>
                                        </select>
                                        <span class="t-bantu">Pilih satu</span>
                                    </div>

                                    <div class="isian" data-indeks="{{ $urutTanggalSelesai ? $indexTanggalSelesai : 'X' }}">
                                        <label for="sdm_sanksi_tambah_cariUrutTanggalSelesai">{{ $urutTanggalSelesai ? $indexTanggalSelesai . '. ' : '' }}Urut
                                            Tanggal Akhir Sanksi</label>
                                        <select class="pil-dasar" id="sdm_sanksi_tambah_cariUrutTanggalSelesai" name="urut[]" form="form_sdm_sanksi_cari"
                                            onchange="getElementById('tombol_cari_sanksi').click()">
                                            <option selected disabled></option>
                                            <option value="sanksi_selesai ASC" @selected(in_array('sanksi_selesai ASC', (array) $app->request->urut))>Lama - Baru</option>
                                            <option value="sanksi_selesai DESC" @selected(in_array('sanksi_selesai DESC', (array) $app->request->urut))>Baru - Lama</option>
                                        </select>
                                        <span class="t-bantu">Pilih satu</span>
                                    </div>
                                </div>
                            </details>
                        @endunless
                    </div>

                    <span class="merah">Merah</span> : Sanksi Berakhir.

                    <div class="data ringkas">
                        <table class="tabel" id="sanksi-sdm_tabel">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>No</th>
                                    @unless ($halamanAkun ?? null)
                                        <th>Identitas</th>
                                    @endunless
                                    <th>Sanksi</th>
                                    <th>Laporan</th>
                                    <th>Pelapor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tabels as $nomor => $tabel)
                                    <tr @class(['merah' => $tabel->sanksi_selesai <= $app->date->today()])>
                                        <th>
                                            <div class="pil-aksi">
                                                <button id="{{ 'aksi_sanksi_baris_' . $tabels->firstItem() + $nomor }}" title="Pilih Tindakan">
                                                    <svg viewbox="0 0 24 24">
                                                        <use href="#ikonmenuvert"></use>
                                                    </svg>
                                                </button>
                                                <div class="aksi">
                                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#sanksi-sdm_sematan"
                                                        href="{{ $app->url->route('sdm.sanksi.lihat', ['uuid' => $tabel->sanksi_uuid]) }}" title="Lihat/Ubah Sanksi">
                                                        Lihat/Ubah Sanksi
                                                    </a>
                                                </div>
                                            </div>
                                        </th>
                                        <td>{{ $tabels->firstItem() + $nomor }}</td>
                                        @unless ($halamanAkun ?? null)
                                            <td class="profil">
                                                <div @class(['merah' => $tabel->langgar_tsdm_tgl_berhenti])>
                                                    <a class="isi-xhr taut-akun" href="{{ $app->url->route('sdm.akun', ['uuid' => $tabel->langgar_tsdm_uuid]) }}">
                                                        <img src="{{ $app->filesystem->exists('sdm/foto-profil/' . $tabel->sanksi_no_absen . '.webp')
                                                            ? $app->url->route('sdm.tautan-foto-profil', [
                                                                'berkas_foto_profil' =>
                                                                    $tabel->sanksi_no_absen . '.webp' . '?id=' . filemtime($app->storagePath('app/sdm/foto-profil/' . $tabel->sanksi_no_absen . '.webp')),
                                                            ])
                                                            : $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/blank.webp')) }}"
                                                            title="{{ $tabel->langgar_tsdm_nama ?? 'foto akun' }}" alt="{{ $tabel->langgar_tsdm_nama ?? 'foto akun' }}"
                                                            @class([
                                                                'akun',
                                                                'svg' => !$app->filesystem->exists(
                                                                    'sdm/foto-profil/' . $tabel->sanksi_no_absen . '.webp'),
                                                            ]) loading="lazy">

                                                        <small>{{ $tabel->sanksi_no_absen }} : {{ $tabel->langgar_tsdm_nama }}</small>
                                                    </a>
                                                    <br>
                                                    {{ $tabel->langgar_tlokasi }} {{ $tabel->langgar_tkontrak }} -
                                                    {{ $tabel->langgar_tposisi }}
                                                    {{ $tabel->langgar_tsdm_tgl_berhenti ? '(NON-AKTIF)' : '' }}
                                                </div>
                                            </td>
                                        @endunless
                                        <td>
                                            <b>Sanksi</b> : {{ $tabel->sanksi_jenis }}<br>
                                            <b>Berlaku</b> : {{ strtoupper($app->date->make($tabel->sanksi_mulai)?->translatedFormat('d F Y')) }} s.d
                                            {{ strtoupper($app->date->make($tabel->sanksi_selesai)?->translatedFormat('d F Y')) }}<br>
                                            <b>Tambahan</b> : {!! nl2br($tabel->sanksi_tambahan) !!}<br>
                                            <b>Keterangan</b> : {!! nl2br($tabel->sanksi_keterangan) !!}
                                        </td>
                                        <td>
                                            <b>Nomor</b> : <u><a class="isi-xhr"
                                                    href="{{ $app->url->route('sdm.pelanggaran.data', ['kata_kunci' => $tabel->sanksi_lap_no]) }}"
                                                    aria-label="Lap Pelanggaran SDM No {{ $tabel->sanksi_lap_no }}">{{ $tabel->sanksi_lap_no }}</a></u><br>
                                            <b>Tanggal</b> : {{ strtoupper($app->date->make($tabel->langgar_tanggal)?->translatedFormat('d F Y')) }}<br>
                                            <b>Aduan</b> : {!! nl2br($tabel->langgar_isi) !!}
                                        </td>
                                        <td class="profil">
                                            @if ($tabel->langgar_psdm_uuid)
                                                <div @class(['merah' => $tabel->langgar_psdm_tgl_berhenti])>
                                                    <b><i><u>Pelapor</u></i></b> :<br>
                                                    <a class="isi-xhr taut-akun" href="{{ $app->url->route('sdm.akun', ['uuid' => $tabel->langgar_psdm_uuid]) }}">
                                                        <img src="{{ $app->filesystem->exists('sdm/foto-profil/' . $tabel->langgar_pelapor . '.webp')
                                                            ? $app->url->route('sdm.tautan-foto-profil', [
                                                                'berkas_foto_profil' =>
                                                                    $tabel->langgar_pelapor . '.webp' . '?id=' . filemtime($app->storagePath('app/sdm/foto-profil/' . $tabel->langgar_pelapor . '.webp')),
                                                            ])
                                                            : $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/blank.webp')) }}"
                                                            title="{{ $tabel->langgar_psdm_nama ?? 'foto akun' }}" alt="{{ $tabel->langgar_psdm_nama ?? 'foto akun' }}"
                                                            @class([
                                                                'akun',
                                                                'svg' => !$app->filesystem->exists(
                                                                    'sdm/foto-profil/' . $tabel->langgar_pelapor . '.webp'),
                                                            ]) loading="lazy">

                                                        <small>{{ $tabel->langgar_pelapor }} - {{ $tabel->langgar_psdm_nama }}</small>
                                                    </a>
                                                    <br>
                                                    {{ $tabel->langgar_plokasi }} {{ $tabel->langgar_pkontrak }} -
                                                    {{ $tabel->langgar_pposisi }}
                                                    {{ $tabel->langgar_psdm_tgl_berhenti ? '(NON-AKTIF)' : '' }}
                                                </div>
                                            @endif
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

                            pilDasar('#sanksi-sdm_tabels .pil-dasar');
                            pilSaja('#sanksi-sdm_tabels .pil-saja');
                            urutData('#sdm_sanksi_cariUrut', '#sdm_sanksi_cariUrut [data-indeks]');
                            formatTabel('#sanksi-sdm_tabel thead th', '#sanksi-sdm_tabel tbody tr');
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
            <a class="isi-xhr" data-rekam="false" data-tujuan="#sanksi-sdm_sematan" href="{{ $app->url->route('sdm.sanksi.unggah') }}"
                title="Unggah Data Sanksi SDM">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonunggah"></use>
                </svg>
            </a>
            <a href="#" title="Unduh Data"
                onclick="event.preventDefault();lemparXHR({tujuan : '#sanksi-sdm_sematan', tautan : window.location.search ? window.location.pathname + window.location.search + '&unduh=excel' : window.location.pathname + '?unduh=excel', strim : true})">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonunduh"></use>
                </svg>
            </a>
            <a class="isi-xhr" data-rekam="false" data-tujuan="#sanksi-sdm_sematan" href="{{ $app->url->route('sdm.pelanggaran.tambah') }}"
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

                    pilDasar('#form_sdm_sanksi_cari .pil-dasar');
                    pilCari('#form_sdm_sanksi_cari .pil-cari');
                    pilSaja('#form_sdm_sanksi_cari .pil-saja');
                    formatIsian('#form_sdm_sanksi_cari .isian :is(textarea,input[type=text],input[type=search])');
                })();
            </script>
        @endisset
    </div>
@endsection
