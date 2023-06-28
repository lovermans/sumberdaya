@extends('rangka')

@section('isi')
<div id="sdm_sanksi">
    <h4>Data Riwayat Sanksi SDM</h4>
    @isset($tabels)
    <div class="cari-data tcetak">

        <form id="form_sdm_sanksi_cari" class="form-xhr kartu" data-tujuan="#sanksi-sdm_tabels" data-frag="true"
            method="GET" data-blank="true">
            <input type="hidden" name="fragment" value="sanksi-sdm_tabels">

            <details class="gspan-4" {{ $rekRangka->anyFilled(['sanksi_jenis', 'sanksi_penempatan']) ? 'open' : '' }}>

                <summary class="cari">
                    <div class="isian gspan-4">
                        <input type="text" id="kata_kunci_pelanggaran_sdm" name="kata_kunci"
                            value="{{ $rekRangka->kata_kunci }}" aria-label="Cari Kata Kunci">

                        <button id="tombol_cari_sanksi" class="cari-cepat" type="submit" title="Cari Data">
                            <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#cari' }}"
                                    xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                            </svg>
                        </button>
                    </div>
                </summary>

                <div class="kartu form">
                    <div class="isian normal">
                        <label for="sdm_sanksi_cariJenis">Saring Jenis Sanksi</label>

                        <select id="sdm_sanksi_cariJenis" name="sanksi_jenis[]" class="pil-cari" multiple>
                            @foreach ($jenisSanksis as $jenisSanksi)
                            <option @selected(in_array($jenisSanksi->atur_butir, (array)
                                $rekRangka->sanksi_jenis)) @class(['merah' => $jenisSanksi->atur_status ==
                                'NON-AKTIF'])>{{ $jenisSanksi->atur_butir }}</option>
                            @endforeach
                        </select>

                        <span class="t-bantu">Pilih satu atau lebih</span>
                    </div>

                    <div class="isian normal">
                        <label for="sdm_sanksi_cariLokasi">Saring Penempatan</label>

                        <select id="sdm_sanksi_cariLokasi" name="sanksi_penempatan[]" class="pil-cari" multiple>
                            @foreach ($lokasis as $lokasi)
                            <option @selected(in_array($lokasi->atur_butir, (array)
                                $rekRangka->sanksi_penempatan)) @class(['merah' => $lokasi->atur_status ==
                                'NON-AKTIF'])>{{ $lokasi->atur_butir }}</option>
                            @endforeach
                        </select>

                        <span class="t-bantu">Pilih satu atau lebih</span>
                    </div>
                </div>
            </details>
        </form>
    </div>

    <div id="sanksi-sdm_tabels" class="kartu scroll-margin">
        @fragment('sanksi-sdm_tabels')
        <div id="sanksi-sdm_sematan" class="scroll-margin"></div>

        <div class="trek-data tcetak">
            @unless ($halamanAkun ?? null)
            <span class="bph">
                <label for="sdm_sanksi_cariPerHalaman">Baris per halaman : </label>

                <select id="sdm_sanksi_cariPerHalaman" name="bph" class="pil-saja" form="form_sdm_sanksi_cari"
                    onchange="getElementById('tombol_cari_sanksi').click()">
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
                <a class="isi-xhr" data-tujuan="#sanksi-sdm_tabels" data-frag="true" href="{{ $tabels->url(1) }}"
                    title="Awal">
                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#awal' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                </a>
                @endif

                @if($tabels->previousPageUrl())
                <a class="isi-xhr" data-tujuan="#sanksi-sdm_tabels" data-frag="true"
                    href="{{ $tabels->previousPageUrl() }}" title="Sebelumnya">
                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#mundur' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                </a>
                @endif

                @if($tabels->nextPageUrl())
                <a class="isi-xhr" data-tujuan="#sanksi-sdm_tabels" data-frag="true" href="{{ $tabels->nextPageUrl() }}"
                    title="Berikutnya">
                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#maju' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                </a>

                <a class="isi-xhr" data-tujuan="#sanksi-sdm_tabels" data-frag="true"
                    href="{{ $tabels->url($tabels->lastPage()) }}" title="Akhir">
                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#akhir' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                </a>
                @endif
            </span>
            @endif

            @unless ($halamanAkun ?? null)
            <details class="gspan-4" {{ $rekRangka->anyFilled('urut') ? 'open' : '' }}>
                <summary>Pengurutan :</summary>
                <div class="kartu form" id="sdm_sanksi_cariUrut">
                    <div class="isian" data-indeks="{{ $urutTanggalMulai ? $indexTanggalMulai : 'X' }}">
                        <label for="sdm_sanksi_tambah_cariUrutTanggalMulai">{{ $urutTanggalMulai ?
                            $indexTanggalMulai.'. '
                            : '' }}Urut Tanggal Mulai Sanksi</label>
                        <select id="sdm_sanksi_tambah_cariUrutTanggalMulai" name="urut[]" class="pil-dasar"
                            form="form_sdm_sanksi_cari" onchange="getElementById('tombol_cari_sanksi').click()">
                            <option selected disabled></option>
                            <option @selected(in_array('sanksi_mulai ASC', (array) $rekRangka->urut))
                                value="sanksi_mulai ASC">Lama - Baru</option>
                            <option @selected(in_array('sanksi_mulai DESC', (array) $rekRangka->urut))
                                value="sanksi_mulai DESC">Baru - Lama</option>
                        </select>
                        <span class="t-bantu">Pilih satu</span>
                    </div>

                    <div class="isian" data-indeks="{{ $urutTanggalSelesai ? $indexTanggalSelesai : 'X' }}">
                        <label for="sdm_sanksi_tambah_cariUrutTanggalSelesai">{{ $urutTanggalSelesai ?
                            $indexTanggalSelesai.'. '
                            : '' }}Urut Tanggal Akhir Sanksi</label>
                        <select id="sdm_sanksi_tambah_cariUrutTanggalSelesai" name="urut[]" class="pil-dasar"
                            form="form_sdm_sanksi_cari" onchange="getElementById('tombol_cari_sanksi').click()">
                            <option selected disabled></option>
                            <option @selected(in_array('sanksi_selesai ASC', (array) $rekRangka->urut))
                                value="sanksi_selesai ASC">Lama - Baru</option>
                            <option @selected(in_array('sanksi_selesai DESC', (array) $rekRangka->urut))
                                value="sanksi_selesai DESC">Baru - Lama</option>
                        </select>
                        <span class="t-bantu">Pilih satu</span>
                    </div>
                </div>
            </details>
            @endunless
        </div>

        <span class="merah">Merah</span> : Sanksi Berakhir.

        <div class="data ringkas">
            <table id="sanksi-sdm_tabel" class="tabel">
                <thead>
                    <tr>
                        <th></th>
                        <th>No</th>
                        <th>Identitas</th>
                        <th>Sanksi</th>
                        <th>Laporan</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tabels as $nomor => $tabel)
                    <tr @class(['merah'=> $tabel->sanksi_selesai <= $dateRangka->today() ])>
                            <th>
                                <div class="pil-aksi">
                                    <button id="{{ 'aksi_sanksi_baris_' .$tabels->firstItem() + $nomor}}"
                                        title="Pilih Tindakan">
                                        <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <use xlink:href="{{ $mixRangka('/ikon.svg') . '#menuvert' }}"
                                                xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                                        </svg>
                                    </button>
                                    <div class="aksi">
                                        <a class="isi-xhr" data-rekam="false" data-tujuan="#sanksi-sdm_sematan"
                                            href="{{ $urlRangka->route('sdm.sanksi.lihat', ['uuid' => $tabel->sanksi_uuid], false) }}"
                                            title="Lihat/Ubah Sanksi">Lihat/Ubah Sanksi</a>
                                    </div>
                                </div>
                            </th>
                            <td>{{$tabels->firstItem() + $nomor}}</td>
                            <td>
                                <div @class(['merah'=> $tabel->langgar_tsdm_tgl_berhenti])>
                                    <a class="isi-xhr taut-akun"
                                        href="{{ $urlRangka->route('sdm.akun', ['uuid' => $tabel->langgar_tsdm_uuid], false) }}">
                                        <img @class(['akun', 'svg'=> !$storageRangka->exists('sdm/foto-profil/' .
                                        $tabel->sanksi_no_absen . '.webp')]) src="{{
                                        $storageRangka->exists('sdm/foto-profil/' . $tabel->sanksi_no_absen .
                                        '.webp')
                                        ? $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' =>
                                        $tabel->sanksi_no_absen . '.webp' . '?' .
                                        filemtime($appRangka->storagePath('app/sdm/foto-profil/' .
                                        $tabel->sanksi_no_absen . '.webp'))], false) :
                                        $mixRangka('/ikon.svg') . '#akun' }}" alt="{{
                                        $tabel->langgar_tsdm_nama ?? 'foto akun' }}" title="{{
                                        $tabel->langgar_tsdm_nama
                                        ?? 'foto akun' }}" loading="lazy">
                                    </a>
                                    {{$tabel->sanksi_no_absen}} - {{$tabel->langgar_tsdm_nama}} <br />
                                    {{$tabel->langgar_tlokasi}} {{$tabel->langgar_tkontrak}} -
                                    {{$tabel->langgar_tposisi}} {{ $tabel->langgar_tsdm_tgl_berhenti ? '(NON-AKTIF)'
                                    :
                                    '' }}
                                </div>
                            </td>
                            <td>
                                <b>Sanksi</b> : {{$tabel->sanksi_jenis}}<br />
                                <b>Berlaku</b> : {{
                                strtoupper($dateRangka->make($tabel->sanksi_mulai)?->translatedFormat('d F Y'))
                                }} s.d {{
                                strtoupper($dateRangka->make($tabel->sanksi_selesai)?->translatedFormat('d F Y'))
                                }}<br />
                                <b>Tambahan</b> : {!! nl2br($tabel->sanksi_tambahan) !!}
                            </td>
                            <td>
                                <b>Nomor</b> : <u><a class="isi-xhr"
                                        href="{{ $urlRangka->route('sdm.pelanggaran.data', ['kata_kunci' => $tabel->sanksi_lap_no], false) }}"
                                        aria-label="Lap Pelanggaran SDM No {{ $tabel->sanksi_lap_no }}">{{
                                        $tabel->sanksi_lap_no }}</a></u><br />
                                <b>Tanggal</b> : {{
                                strtoupper($dateRangka->make($tabel->langgar_tanggal)?->translatedFormat('d F Y'))
                                }}<br />
                                <b>Aduan</b> : {!! nl2br($tabel->langgar_isi) !!}
                                @if ($tabel->sanksi_lap_no)
                                <div @class(['merah'=> $tabel->langgar_psdm_tgl_berhenti])>
                                    <b><i><u>Pelapor</u></i></b> :<br />
                                    <a class="isi-xhr taut-akun"
                                        href="{{ $urlRangka->route('sdm.akun', ['uuid' => $tabel->langgar_psdm_uuid], false) }}">
                                        <img @class(['akun', 'svg'=> !$storageRangka->exists('sdm/foto-profil/' .
                                        $tabel->langgar_pelapor . '.webp')]) src="{{
                                        $storageRangka->exists('sdm/foto-profil/' . $tabel->langgar_pelapor .
                                        '.webp') ?
                                        $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' =>
                                        $tabel->langgar_pelapor . '.webp' . '?' .
                                        filemtime($appRangka->storagePath('app/sdm/foto-profil/' .
                                        $tabel->langgar_pelapor . '.webp'))], false) :
                                        $mixRangka('/ikon.svg') . '#akun' }}" alt="{{
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
                                @endif
                            </td>
                            <td>
                                {!! nl2br($tabel->sanksi_keterangan) !!}
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
        <button class="sekunder tcetak" onclick="ringkasTabel(this)">Panjang/Pendekkan
            Tampilan Tabel</button>

        <script>
            (async() => {
                    while(!window.aplikasiSiap) {
                        await new Promise((resolve,reject) =>
                        setTimeout(resolve, 1000));
                    }
                    
                    pilDasar('#sanksi-sdm_tabels .pil-dasar');
                    pilSaja('#sanksi-sdm_tabels .pil-saja');
                    urutData('#sdm_sanksi_cariUrut','#sdm_sanksi_cariUrut [data-indeks]');
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
        <a href="#" onclick="event.preventDefault();window.scrollTo(0,0)" title="Kembali Ke Atas">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#panahatas' }}"
                    xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
        <a class="isi-xhr" data-rekam="false" href="{{ $urlRangka->route('sdm.sanksi.unggah', [], false) }}"
            data-tujuan="#sanksi-sdm_sematan" title="Unggah Data Sanksi SDM">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#unggah' }}" xmlns:xlink="http://www.w3.org/1999/xlink">
                </use>
            </svg>
        </a>
        <a href="#" title="Unduh Data"
            onclick="event.preventDefault();lemparXHR({tujuan : '#sanksi-sdm_sematan', tautan : window.location.search ? window.location.pathname + window.location.search + '&unduh=excel' : window.location.pathname + '?unduh=excel', strim : true})">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#unduh' }}" xmlns:xlink="http://www.w3.org/1999/xlink">
                </use>
            </svg>
        </a>
        <a class="isi-xhr" data-rekam="false" data-tujuan="#sanksi-sdm_sematan"
            href="{{ $urlRangka->route('sdm.pelanggaran.tambah', [], false) }}" title="Tambah Data">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#tambah' }}" xmlns:xlink="http://www.w3.org/1999/xlink">
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

            pilDasar('#form_sdm_sanksi_cari .pil-dasar');
            pilCari('#form_sdm_sanksi_cari .pil-cari');
            pilSaja('#form_sdm_sanksi_cari .pil-saja');
            formatIsian('#form_sdm_sanksi_cari .isian :is(textarea,input[type=text],input[type=search])');
        })();
    </script>
    @endisset
</div>
@endsection