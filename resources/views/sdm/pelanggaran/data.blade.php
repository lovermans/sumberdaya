@extends('rangka')

@section('isi')
<div id="sdm_pelanggaran">
    <h4>Data Riwayat Laporan Pelanggaran SDM</h4>
    @isset($tabels)
    <div class="cari-data tcetak">

        <form id="form_sdm_pelanggaran_cari" class="form-xhr kartu" data-tujuan="#pelanggaran-sdm_tabels"
            data-frag="true" method="GET" data-blank="true">
            <input type="hidden" name="fragment" value="pelanggaran-sdm_tabels">

            <details class="gspan-4" {{ $rekRangka->anyFilled(['tgl_langgar_mulai', 'langgar_status', 'langgar_proses',
                'tgl_langgar_sampai', 'langgar_penempatan', 'langgar_sanksi', 'status_sdm']) ? 'open' : '' }}>

                <summary class="cari">
                    <div class="isian gspan-4">
                        <input type="text" id="kata_kunci_pelanggaran_sdm" name="kata_kunci"
                            value="{{ $rekRangka->kata_kunci }}" aria-label="Cari Kata Kunci">

                        <button id="tombol_cari_pelanggaran" class="cari-cepat" type="submit" title="Cari Data">
                            <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#cari' }}"
                                    xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                            </svg>
                        </button>
                    </div>
                </summary>

                <div class="kartu form">
                    <div class="isian normal">
                        <label for="sdm_pelanggaran_cariStatus">Saring Status Laporan</label>

                        <select id="sdm_pelanggaran_cariStatus" name="langgar_status[]" class="pil-dasar" multiple>
                            <option @selected($rekRangka->langgar_status == 'DIPROSES')>DIPROSES</option>
                            <option @selected($rekRangka->langgar_status == 'DIBATALKAN')>DIBATALKAN</option>
                        </select>

                        <span class="t-bantu">Pilih satu atau lebih</span>
                    </div>

                    <div class="isian normal">
                        <label for="sdm_pelanggaran_cariStatusPenanganan">Saring Status Penanganan</label>

                        <select id="sdm_pelanggaran_cariStatusPenanganan" name="langgar_proses" class="pil-dasar">
                            <option selected disabled></option>
                            <option @selected($rekRangka->langgar_proses == 'SELESAI')>SELESAI</option>
                            <option @selected($rekRangka->langgar_proses == 'BELUM SELESAI')>BELUM SELESAI
                            </option>
                        </select>

                        <span class="t-bantu">Pilih satu atau lebih</span>
                    </div>

                    <div class="isian normal">
                        <label for="sdm_pelanggaran_cariLokasi">Saring Lokasi</label>

                        <select id="sdm_pelanggaran_cariLokasi" name="langgar_penempatan[]" class="pil-cari" multiple>
                            @foreach ($lokasis as $lokasi)
                            <option @selected(in_array($lokasi->atur_butir, (array)
                                $rekRangka->langgar_penempatan)) @class(['merah' => $lokasi->atur_status ==
                                'NON-AKTIF'])>{{ $lokasi->atur_butir }}</option>
                            @endforeach
                        </select>

                        <span class="t-bantu">Pilih satu atau lebih</span>
                    </div>

                    <div class="isian normal">
                        <label for="sdm_pelanggaran_cariStatusSDM">Saring Status SDM</label>

                        <select id="sdm_pelanggaran_cariStatusSDM" name="status_sdm[]" class="pil-cari" multiple>
                            @foreach ($statusSDMs as $statusSDM)
                            <option @selected(in_array($statusSDM->atur_butir, (array)
                                $rekRangka->status_sdm)) @class(['merah' => $statusSDM->atur_status ==
                                'NON-AKTIF'])>{{ $statusSDM->atur_butir }}</option>
                            @endforeach
                        </select>

                        <span class="t-bantu">Pilih satu atau lebih</span>
                    </div>

                    <div class="isian normal">
                        <label for="sdm_pelanggaran_cariTanggalMulai">Tanggal Laporan Mulai</label>
                        <input id="sdm_pelanggaran_cariTanggalMulai" type="date" name="tgl_langgar_mulai"
                            value="{{ $rekRangka->old('tgl_langgar_mulai', $rekRangka->tgl_langgar_mulai ?? null) }}">
                        <span class="t-bantu">Isi tanggal</span>
                    </div>

                    <div class="isian normal">
                        <label for="sdm_pelanggaran_cariTanggalSampai">Tanggal Laporan Sampai</label>
                        <input id="sdm_pelanggaran_cariTanggalSampai" type="date" name="tgl_langgar_sampai"
                            value="{{ $rekRangka->old('tgl_langgar_sampai', $rekRangka->tgl_langgar_sampai ?? null) }}">
                        <span class="t-bantu">Isi tanggal</span>
                    </div>

                    <div class="gspan-4"></div>

                    <button id="tombol_saring_pelanggaran" class="utama pelengkap" type="submit" title="Saring Data">
                        <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#cari' }}"
                                xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                        </svg> Saring
                    </button>
                </div>
            </details>
        </form>
    </div>

    <div id="pelanggaran-sdm_tabels" class="kartu scroll-margin">
        @fragment('pelanggaran-sdm_tabels')
        <b><i><small>Jumlah SDM ({{ $rekRangka->anyFilled(['tgl_langgar_mulai', 'langgar_status', 'langgar_proses',
                    'tgl_langgar_sampai', 'langgar_penempatan', 'langgar_sanksi', 'status_sdm']) ? 'sesuai data
                    penyaringan'
                    : 'global'
                    }}) : Organik = {{number_format($jumlahOrganik, 0, ',', '.')}} Personil | Outsource =
                    {{number_format($jumlahOS, 0, ',', '.')}} Personil.</small></i></b>
        <div id="pelanggaran-sdm_sematan" class="scroll-margin"></div>

        <div class="trek-data tcetak">
            @unless ($halamanAkun ?? null)
            <span class="bph">
                <label for="sdm_pelanggaran_cariPerHalaman">Baris per halaman : </label>

                <select id="sdm_pelanggaran_cariPerHalaman" name="bph" class="pil-saja" form="form_sdm_pelanggaran_cari"
                    onchange="getElementById('tombol_cari_pelanggaran').click()">
                    <option>25</option>
                    <option @selected($tabels->perPage() == 50)>50</option>
                    <option @selected($tabels->perPage() == 75)>75</option>
                    <option @selected($tabels->perPage() == 100)>100</option>
                </select>
            </span>
            @endunless

            <span class="ket">{{number_format($tabels->firstItem(), 0, ',', '.')}} -
                {{number_format($tabels->lastItem(), 0, ',', '.')}} dari {{number_format($tabels->total(), 0,
                ',',
                '.')}} data
            </span>

            @if($tabels->hasPages())
            <span class="trek">
                @if($tabels->currentPage() > 1)
                <a class="isi-xhr" data-tujuan="#pelanggaran-sdm_tabels" data-frag="true" href="{{ $tabels->url(1) }}"
                    title="Awal">
                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#awal' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                </a>
                @endif

                @if($tabels->previousPageUrl())
                <a class="isi-xhr" data-tujuan="#pelanggaran-sdm_tabels" data-frag="true"
                    href="{{ $tabels->previousPageUrl() }}" title="Sebelumnya">
                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#mundur' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                </a>
                @endif

                @if($tabels->nextPageUrl())
                <a class="isi-xhr" data-tujuan="#pelanggaran-sdm_tabels" data-frag="true"
                    href="{{ $tabels->nextPageUrl() }}" title="Berikutnya">
                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#maju' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                </a>

                <a class="isi-xhr" data-tujuan="#pelanggaran-sdm_tabels" data-frag="true"
                    href="{{ $tabels->url($tabels->lastPage()) }}" title="Akhir">
                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#akhir' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                </a>
                @endif
            </span>
            @endif

            @unless ($halamanAkun ?? null)
            <details class="gspan-4" {{ $rekRangka->anyFilled('urut') ? 'open' : '' }}>
                <summary>Pengurutan :</summary>
                <div class="kartu form" id="sdm_pelanggaran_cariUrut">
                    <div class="isian" data-indeks="{{ $urutNomor ? $indexNomor : 'X' }}">
                        <label for="sdm_pelanggaran_tambah_cariUrutNomor">{{ $urutNomor ? $indexNomor.'. ' : ''
                            }}Urut Nomor Laporan</label>
                        <select id="sdm_pelanggaran_tambah_cariUrutNomor" name="urut[]" class="pil-dasar"
                            form="form_sdm_pelanggaran_cari"
                            onchange="getElementById('tombol_cari_pelanggaran').click()">
                            <option selected disabled></option>
                            <option @selected(in_array('langgar_lap_no ASC', (array) $rekRangka->urut))
                                value="langgar_lap_no ASC">0 - 9</option>
                            <option @selected(in_array('langgar_lap_no DESC', (array) $rekRangka->urut))
                                value="langgar_lap_no DESC">9 - 0</option>
                        </select>
                        <span class="t-bantu">Pilih satu</span>
                    </div>

                    <div class="isian" data-indeks="{{ $urutTanggal ? $indexTanggal : 'X' }}">
                        <label for="sdm_pelanggaran_tambah_cariUrutTanggal">{{ $urutTanggal ? $indexTanggal.'. '
                            : '' }}Urut Tanggal Laporan</label>
                        <select id="sdm_pelanggaran_tambah_cariUrutTanggal" name="urut[]" class="pil-dasar"
                            form="form_sdm_pelanggaran_cari"
                            onchange="getElementById('tombol_cari_pelanggaran').click()">
                            <option selected disabled></option>
                            <option @selected(in_array('langgar_tanggal ASC', (array) $rekRangka->urut))
                                value="langgar_tanggal ASC">Lama - Baru</option>
                            <option @selected(in_array('langgar_tanggal DESC', (array) $rekRangka->urut))
                                value="langgar_tanggal DESC">Baru - Lama</option>
                        </select>
                        <span class="t-bantu">Pilih satu</span>
                    </div>
                </div>
            </details>
            @endunless
        </div>

        <span class="merah">Merah</span> : Dibatalkan. <span class="biru">Biru</span> : Selesai diberikan sanksi.

        <div class="data ringkas">
            <table id="pelanggaran-sdm_tabel" class="tabel">
                <thead>
                    <tr>
                        <th></th>
                        <th>No</th>
                        <th>Laporan</th>
                        <th>Pelanggaran</th>
                        <th>Sanksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tabels as $nomor => $tabel)
                    <tr @class(['merah'=> $tabel->langgar_status == 'DIBATALKAN', 'biru' =>
                        $tabel->final_sanksi_jenis])>
                        <th>
                            <div class="pil-aksi">
                                <button id="{{ 'aksi_pelanggaran_baris_' .$tabels->firstItem() + $nomor}}"
                                    title="Pilih Tindakan">
                                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#menuvert' }}"
                                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                                    </svg>
                                </button>
                                <div class="aksi">
                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#pelanggaran-sdm_sematan"
                                        href="{{ $urlRangka->route('sdm.pelanggaran.lihat', ['uuid' => $tabel->langgar_uuid]) }}"
                                        title="Tindaklanjuti">Tindaklanjuti</a>
                                </div>
                            </div>
                        </th>
                        <td>{{$tabels->firstItem() + $nomor}}</td>
                        <td>
                            <div @class(['merah'=> $tabel->langgar_tsdm_tgl_berhenti])>
                                <b><i><u>Terlapor</u></i></b> :<br />
                                <a class="isi-xhr taut-akun"
                                    href="{{ $urlRangka->route('sdm.akun', ['uuid' => $tabel->langgar_tsdm_uuid]) }}">
                                    <img @class(['akun', 'svg'=> !$storageRangka->exists('sdm/foto-profil/' .
                                    $tabel->langgar_no_absen . '.webp')]) src="{{
                                    $storageRangka->exists('sdm/foto-profil/' . $tabel->langgar_no_absen .
                                    '.webp')
                                    ? $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' =>
                                    $tabel->langgar_no_absen . '.webp' . '?' .
                                    filemtime($appRangka->storagePath('app/sdm/foto-profil/' .
                                    $tabel->langgar_no_absen . '.webp'))]) :
                                    $urlRangka->asset($mixRangka('/ikon.svg')) . '#akun' }}" alt="{{
                                    $tabel->langgar_tsdm_nama ?? 'foto akun' }}" title="{{
                                    $tabel->langgar_tsdm_nama
                                    ?? 'foto akun' }}" loading="lazy">
                                </a>
                                {{$tabel->langgar_no_absen}} - {{$tabel->langgar_tsdm_nama}} <br />
                                {{$tabel->langgar_tlokasi}} {{$tabel->langgar_tkontrak}} -
                                {{$tabel->langgar_tposisi}} {{ $tabel->langgar_tsdm_tgl_berhenti ? '(NON-AKTIF)'
                                :
                                '' }}
                            </div>
                            <div @class(['merah'=> $tabel->langgar_psdm_tgl_berhenti])>
                                <b><i><u>Pelapor</u></i></b> :<br />
                                <a class="isi-xhr taut-akun"
                                    href="{{ $urlRangka->route('sdm.akun', ['uuid' => $tabel->langgar_psdm_uuid]) }}">
                                    <img @class(['akun', 'svg'=> !$storageRangka->exists('sdm/foto-profil/' .
                                    $tabel->langgar_pelapor . '.webp')]) src="{{
                                    $storageRangka->exists('sdm/foto-profil/' . $tabel->langgar_pelapor .
                                    '.webp') ?
                                    $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' =>
                                    $tabel->langgar_pelapor . '.webp' . '?' .
                                    filemtime($appRangka->storagePath('app/sdm/foto-profil/' .
                                    $tabel->langgar_pelapor . '.webp'))]) :
                                    $urlRangka->asset($mixRangka('/ikon.svg')) . '#akun' }}" alt="{{
                                    $tabel->langgar_psdm_nama ?? 'foto akun' }}" title="{{
                                    $tabel->langgar_psdm_nama
                                    ?? 'foto akun' }}" loading="lazy">
                                </a>
                                {{$tabel->langgar_pelapor}} - {{$tabel->langgar_psdm_nama}} <br />
                                {{$tabel->langgar_plokasi}} {{$tabel->langgar_pkontrak}} -
                                {{$tabel->langgar_pposisi}} {{ $tabel->langgar_psdm_tgl_berhenti ? '(NON-AKTIF)'
                                :
                                '' }}
                            </div>
                        </td>
                        <td>
                            <form class="form-xhr" method="POST"
                                action="{{ $urlRangka->route('sdm.pelanggaran.ubah', [ 'uuid' => $tabel->langgar_uuid]) }}"
                                data-singkat="true">
                                <input type="hidden" name="_token" value="{{ $rekRangka->session()->token() }}">
                                <div class="isian">
                                    <label for="{{'ubah_cepat_pelanggaran_' . $tabels->firstItem() + $nomor}}"
                                        class="tcetak">Ubah Status</label>
                                    <select id="{{'ubah_cepat_pelanggaran_' . $tabels->firstItem() + $nomor}}"
                                        name="langgar_status" class="pil-saja" required
                                        onchange="getElementById('kirim-{{$tabel->langgar_uuid}}').click()">
                                        <option @selected($rekRangka->old('langgar_status',
                                            $tabel->langgar_status
                                            ?? null) == 'DIPROSES')>DIPROSES</option>
                                        <option @selected($rekRangka->old('langgar_status',
                                            $tabel->langgar_status
                                            ?? null) == 'DIBATALKAN')>DIBATALKAN</option>
                                    </select>
                                    <button type="submit" id="kirim-{{ $tabel->langgar_uuid }}" sembunyikan></button>
                                </div>
                            </form><br />
                            <b>Nomor</b> : {{$tabel->langgar_lap_no}}<br />
                            <b>Tanggal</b> : {{
                            strtoupper($dateRangka->make($tabel->langgar_tanggal)?->translatedFormat('d F Y'))
                            }}<br />
                            <b>Aduan</b> : {!! nl2br($tabel->langgar_isi) !!}<br />
                            <b>Keterangan</b> : {!! nl2br($tabel->langgar_keterangan) !!}
                        </td>
                        <td>
                            <b><i><u>Sanksi Aktif Sebelumnya</u></i></b> :<br />
                            <b>No Laporan</b> : {{ $tabel->lap_no_sebelumnya}}<br />
                            <b>Sanksi </b> : {{ $tabel->sanksi_aktif_sebelumnya}}<br />
                            <b>Berakhir pada </b> : {{
                            strtoupper($dateRangka->make($tabel->sanksi_selesai_sebelumnya)?->translatedFormat('d
                            F
                            Y')) }}<br /><br />
                            <b><i><u>Sanksi Diberikan</u></i></b> :<br />
                            <b>Sanksi </b> : {{ $tabel->final_sanksi_jenis}}<br />
                            <b>Tambahan </b> : {{ $tabel->final_sanksi_tambahan}}<br />
                            <b>Mulai </b> : {{
                            strtoupper($dateRangka->make($tabel->final_sanksi_mulai)?->translatedFormat('d F
                            Y'))}}<br />
                            <b>Selesai </b> : {{
                            strtoupper($dateRangka->make($tabel->final_sanksi_selesai)?->translatedFormat('d F
                            Y'))
                            }}<br />
                            <b>Keterangan </b> : {!! nl2br($tabel->final_sanksi_keterangan) !!}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <th></th>
                        <td colspan="4">Tidak ada data.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <button class="sekunder tcetak" onclick="ringkasTabel(this)">Panjang/Pendekkan
            Tampilan Tabel</button>

        <script>
            (async() => {
                    while(!window.aplikasiSiap) {
                        await new Promise((resolve,reject) =>
                        setTimeout(resolve, 1000));
                    }
                    
                    pilDasar('#pelanggaran-sdm_tabels .pil-dasar');
                    pilSaja('#pelanggaran-sdm_tabels .pil-saja');
                    urutData('#sdm_pelanggaran_cariUrut','#sdm_pelanggaran_cariUrut [data-indeks]');
                    formatTabel('#pelanggaran-sdm_tabel thead th', '#pelanggaran-sdm_tabel tbody tr');
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
        <a href="#" onclick="event.preventDefault();window.scrollTo(0,0)" title="Kembali Ke Atas">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#panahatas' }}"
                    xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
        <a href="#" title="Unduh Data"
            onclick="event.preventDefault();lemparXHR({tujuan : '#pelanggaran-sdm_sematan', tautan : window.location.search ? window.location.pathname + window.location.search + '&unduh=excel' : window.location.pathname + '?unduh=excel', strim : true})">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#unduh' }}"
                    xmlns:xlink="http://www.w3.org/1999/xlink">
                </use>
            </svg>
        </a>
        <a class="isi-xhr" data-rekam="false" data-tujuan="#pelanggaran-sdm_sematan"
            href="{{ $urlRangka->route('sdm.pelanggaran.tambah') }}" title="Tambah Data">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#tambah' }}"
                    xmlns:xlink="http://www.w3.org/1999/xlink">
                </use>
            </svg>
        </a>
    </div>

    @isset($tabels)
    <script>
        (async() => {
            while(!window.aplikasiSiap) {
                await new Promise((resolve,reject) =>
                setTimeout(resolve, 1000));
            }

            pilDasar('#form_sdm_pelanggaran_cari .pil-dasar');
            pilCari('#form_sdm_pelanggaran_cari .pil-cari');
            pilSaja('#form_sdm_pelanggaran_cari .pil-saja');
            formatIsian('#form_sdm_pelanggaran_cari .isian :is(textarea,input[type=text],input[type=search])');
        })();
    </script>
    @endisset
</div>
@endsection