@extends('rangka')

@section('isi')
<div id="sdm_pelanggaran">
    <h4>Data Riwayat Laporan Pelanggaran SDM</h4>
    @isset($tabels)
    <details {{ $rekRangka->anyFilled(['tgl_langgar_mulai', 'langgar_status', 'langgar_proses', 'tgl_langgar_sampai', 'langgar_penempatan', 'langgar_sanksi', 'urut']) ? 'open' : '' }}>
        <summary>Cari data :</summary>  
        <div class="cari-data tcetak">
            <form id="form_sdm_pelanggaran_cari" class="form-xhr kartu" method="GET" action="{{ $urlRangka->current() }}">
                <div class="isian gspan-2">
                    <label for="sdm_pelanggaran_cariKataKunci">Kata Kunci</label>
                    <input id="sdm_pelanggaran_cariKataKunci" type="text" name="kata_kunci" value="{{ $rekRangka->kata_kunci }}">
                    <span class="t-bantu">Cari nomor, pelapor, terlapor, aduan</span>
                </div>
                <details class="gspan-4" {{ $rekRangka->anyFilled(['tgl_langgar_mulai', 'langgar_status', 'langgar_proses', 'tgl_langgar_sampai', 'langgar_penempatan', 'langgar_sanksi']) ? 'open' : '' }}>
                    <summary>Penyaringan :</summary>                    
                    <div class="kartu form">
                        <div class="isian">
                            <label for="sdm_pelanggaran_cariStatus">Saring Status Laporan</label>
                            <select id="sdm_pelanggaran_cariStatus" name="langgar_status[]" class="pil-dasar" multiple>
                                <option @selected($rekRangka->langgar_status == 'DIPROSES')>DIPROSES</option>
                                <option @selected($rekRangka->langgar_status == 'DIBATALKAN')>DIBATALKAN</option>
                            </select>
                            <span class="t-bantu">Pilih satu atau lebih</span>
                        </div>
                        <div class="isian">
                            <label for="sdm_pelanggaran_cariStatusPenanganan">Saring Status Penanganan</label>
                            <select id="sdm_pelanggaran_cariStatusPenanganan" name="langgar_proses" class="pil-dasar">
                                <option selected disabled></option>
                                <option @selected($rekRangka->langgar_proses == 'SELESAI')>SELESAI</option>
                                <option @selected($rekRangka->langgar_proses == 'BELUM SELESAI')>BELUM SELESAI</option>
                            </select>
                            <span class="t-bantu">Pilih satu atau lebih</span>
                        </div>
                        <div class="isian gspan-2">
                            <label for="sdm_pelanggaran_cariLokasi">Saring Penempatan</label>
                            <select id="sdm_pelanggaran_cariLokasi" name="langgar_penempatan[]" class="pil-cari" multiple>
                                @foreach ($lokasis as $lokasi)
                                    <option @selected(in_array($lokasi->atur_butir, (array) $rekRangka->langgar_penempatan)) @class(['merah' => $lokasi->atur_status == 'NON-AKTIF'])>{{ $lokasi->atur_butir }}</option>
                                @endforeach
                            </select>
                            <span class="t-bantu">Pilih satu atau lebih</span>
                        </div>
                        <div class="isian gspan-2">
                            <label for="sdm_pelanggaran_cariTanggalMulai">Tanggal Laporan Mulai</label>
                            <input id="sdm_pelanggaran_cariTanggalMulai" type="date" name="tgl_langgar_mulai" value="{{ $rekRangka->old('tgl_langgar_mulai', $rekRangka->tgl_langgar_mulai ?? null) }}">
                            <span class="t-bantu">Isi tanggal</span>
                        </div>
                        <div class="isian gspan-2">
                            <label for="sdm_pelanggaran_cariTanggalSampai">Tanggal Laporan Sampai</label>
                            <input id="sdm_pelanggaran_cariTanggalSampai" type="date" name="tgl_langgar_sampai" value="{{ $rekRangka->old('tgl_langgar_sampai', $rekRangka->tgl_langgar_sampai ?? null) }}">
                            <span class="t-bantu">Isi tanggal</span>
                        </div>
                    </div>
                </details>
                <details class="gspan-4" {{ $rekRangka->anyFilled('urut') ? 'open' : '' }}>
                    <summary>Pengurutan :</summary>
                    <div class="kartu form" id="sdm_pelanggaran_cariUrut">
                        <div class="isian" data-indeks="{{ $urutNomor ? $indexNomor : 'X' }}">
                            <label for="sdm_pelanggaran_tambah_cariUrutNomor">{{ $urutNomor ? $indexNomor.'. ' : '' }}Urut Nomor Laporan</label>
                            <select id="sdm_pelanggaran_tambah_cariUrutNomor" name="urut[]" class="pil-dasar" onchange="getElementById('tombol_cari_pelanggaran').click()">
                                <option selected disabled></option>
                                <option @selected(in_array('langgar_lap_no ASC', (array) $rekRangka->urut)) value="langgar_lap_no ASC">0 - 9</option>
                                <option @selected(in_array('langgar_lap_no DESC', (array) $rekRangka->urut)) value="langgar_lap_no DESC">9 - 0</option>
                            </select>
                            <span class="t-bantu">Pilih satu</span>
                        </div>
                        <div class="isian" data-indeks="{{ $urutTanggal ? $indexTanggal : 'X' }}">
                            <label for="sdm_pelanggaran_tambah_cariUrutTanggal">{{ $urutTanggal ? $indexTanggal.'. ' : '' }}Urut Tanggal Laporan</label>
                            <select id="sdm_pelanggaran_tambah_cariUrutTanggal" name="urut[]" class="pil-dasar" onchange="getElementById('tombol_cari_pelanggaran').click()">
                                <option selected disabled></option>
                                <option @selected(in_array('langgar_tanggal ASC', (array) $rekRangka->urut)) value="langgar_tanggal ASC">Lama - Baru</option>
                                <option @selected(in_array('langgar_tanggal DESC', (array) $rekRangka->urut)) value="langgar_tanggal DESC">Baru - Lama</option>
                            </select>
                            <span class="t-bantu">Pilih satu</span>
                        </div>
                    </div>
                </details>
                <div class="gspan-4"></div>
                <button id="tombol_cari_pelanggaran" class="utama pelengkap" type="submit">
                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#cari' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                    CARI
                </button>
            </form>
        </div>
    </details>
        <div id="pelanggaran-sdm_sematan" style="scroll-margin:4em 0 0 0"></div>
        
        <div class="kartu">
            <div class="trek-data tcetak">
                <span class="bph">
                    <label for="sdm_pelanggaran_cariPerHalaman">Baris per halaman : </label>
                    <select id="sdm_pelanggaran_cariPerHalaman" name="bph" class="pil-saja" form="tombol_cari_pelanggaran" onchange="getElementById('tombol_cari_pelanggaran').click()">
                        <option>25</option>
                        <option @selected($tabels->perPage() == 50)>50</option>
                        <option @selected($tabels->perPage() == 75)>75</option>
                        <option @selected($tabels->perPage() == 100)>100</option>
                    </select>
                </span>
                <span class="ket">{{number_format($tabels->firstItem(), 0, ',', '.')}} - {{number_format($tabels->lastItem(), 0, ',', '.')}} dari {{number_format($tabels->total(), 0, ',', '.')}} data</span>
                @if($tabels->hasPages())
                <span class="trek">
                    @if($tabels->currentPage() > 1)
                        <a class="isi-xhr" href="{{ $tabels->url(1) }}" title="Awal">
                            <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#awal' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                            </svg>
                        </a>
                    @endif
                    @if($tabels->previousPageUrl())
                    <a class="isi-xhr" href="{{ $tabels->previousPageUrl() }}" title="Sebelumnya">
                        <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#mundur' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                        </svg>
                    </a>
                    @endif
                    @if($tabels->nextPageUrl())
                    <a class="isi-xhr" href="{{ $tabels->nextPageUrl() }}" title="Berikutnya">
                        <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#maju' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                        </svg>
                    </a>
                    <a class="isi-xhr" href="{{ $tabels->url($tabels->lastPage()) }}" title="Akhir">
                        <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#akhir' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                        </svg>
                    </a>
                    @endif
                </span>
                @endif
            </div>

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
                        <tr>
                            <th>
                                <div class="pil-aksi">
                                    <button>
                                        <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#menuvert' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                                        </svg>
                                    </button>
                                    <div class="aksi">
                                        {{-- <a class="isi-xhr" data-rekam="false" data-tujuan="#pelanggaran-sdm_sematan" href="{{ $urlRangka->route('sdm.permintaan-tambah-sdm.lihat', ['uuid' => $tabel->tambahsdm_uuid]) }}" title="Lihat Data">
                                            <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#lihat' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                                            </svg>
                                        </a> --}}
                                        <a class="isi-xhr" data-rekam="false" data-tujuan="#pelanggaran-sdm_sematan" href="{{ $urlRangka->route('sdm.pelanggaran.ubah', ['uuid' => $tabel->langgar_uuid]) }}" title="Ubah Data">Ubah Laporan</a>
                                    </div>
                                </div>
                            </th>
                            <td>{{$tabels->firstItem() + $nomor}}</td>
                            <td>
                                <div @class(['merah' => $tabel->langgar_tsdm_tgl_berhenti])>
                                    <b><i><u>Terlapor</u></i></b> :<br/>
                                    <a class="isi-xhr taut-akun" href="{{ $urlRangka->route('sdm.akun', ['uuid' => $tabel->langgar_tsdm_uuid]) }}">
                                        <img @class(['akun', 'svg' => !$storageRangka->exists('sdm/foto-profil/' . $tabel->langgar_no_absen . '.webp')]) src="{{ $storageRangka->exists('sdm/foto-profil/' . $tabel->langgar_no_absen . '.webp') ? $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' => $tabel->langgar_no_absen . '.webp' . '?' . filemtime($appRangka->storagePath('app/sdm/foto-profil/' . $tabel->langgar_no_absen . '.webp'))]) : $urlRangka->asset($mixRangka('/ikon.svg')) . '#akun' }}" alt="{{ $tabel->langgar_tsdm_nama ?? 'foto akun' }}" title="{{ $tabel->langgar_tsdm_nama ?? 'foto akun' }}" loading="lazy">
                                    </a>
                                    {{$tabel->langgar_no_absen}} - {{$tabel->langgar_tsdm_nama}} <br/>
                                    {{$tabel->langgar_tlokasi}} {{$tabel->langgar_tkontrak}} - {{$tabel->langgar_tposisi}} {{ $tabel->langgar_tsdm_tgl_berhenti ? '(NON-AKTIF)' : '' }}
                                </div>
                                <div @class(['merah' => $tabel->langgar_psdm_tgl_berhenti])>
                                    <b><i><u>Pelapor</u></i></b> :<br/>
                                    <a class="isi-xhr taut-akun" href="{{ $urlRangka->route('sdm.akun', ['uuid' => $tabel->langgar_psdm_uuid]) }}">
                                        <img @class(['akun', 'svg' => !$storageRangka->exists('sdm/foto-profil/' . $tabel->langgar_pelapor . '.webp')]) src="{{ $storageRangka->exists('sdm/foto-profil/' . $tabel->langgar_pelapor . '.webp') ? $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' => $tabel->langgar_pelapor . '.webp' . '?' . filemtime($appRangka->storagePath('app/sdm/foto-profil/' . $tabel->langgar_pelapor . '.webp'))]) : $urlRangka->asset($mixRangka('/ikon.svg')) . '#akun' }}" alt="{{ $tabel->langgar_psdm_nama ?? 'foto akun' }}" title="{{ $tabel->langgar_psdm_nama ?? 'foto akun' }}" loading="lazy">
                                    </a>
                                    {{$tabel->langgar_pelapor}} - {{$tabel->langgar_psdm_nama}} <br/>
                                    {{$tabel->langgar_plokasi}} {{$tabel->langgar_pkontrak}} - {{$tabel->langgar_pposisi}} {{ $tabel->langgar_psdm_tgl_berhenti ? '(NON-AKTIF)' : '' }}
                                </div>
                            </td>
                            <td>
                                <form class="form-xhr" method="POST" action="{{ $urlRangka->route('sdm.pelanggaran.ubah', [ 'uuid' => $tabel->langgar_uuid]) }}" data-singkat="true">
                                    <input type="hidden" name="_token" value="{{ $rekRangka->session()->token() }}">
                                    <div class="isian">
                                        <label for="permintambahsdmPenempatan" class="tcetak">Ubah Status</label>
                                        <select id="permintambahsdmPenempatan" name="langgar_status" class="pil-saja" required onchange="getElementById('kirim-{{$tabel->langgar_uuid}}').click()">
                                            <option @selected($rekRangka->old('langgar_status', $tabel->langgar_status ?? null) == 'DIPROSES')>DIPROSES</option>
                                            <option @selected($rekRangka->old('langgar_status', $tabel->langgar_status ?? null) == 'DIBATALKAN')>DIBATALKAN</option>
                                        </select>
                                        <button type="submit" id="kirim-{{ $tabel->langgar_uuid }}" sembunyikan></button>
                                    </div>
                                </form><br/>
                                <b>Nomor</b> : {{$tabel->langgar_lap_no}}<br/>
                                <b>Tanggal</b> : {{ strtoupper($dateRangka->make($tabel->langgar_tanggal)?->translatedFormat('d F Y')) }}<br/>
                                <b>Aduan</b> : {!! nl2br($tabel->langgar_isi) !!}<br/>
                                <b>Keterangan</b> : {!! nl2br($tabel->langgar_keterangan) !!}
                            </td>
                            <td>
                                <b><i><u>Sanksi Aktif Sebelumnya</u></i></b> :<br/>
                                <b>No Laporan</b> : {{ $tabel->lap_no_sebelumnya}}<br/>
                                <b>Sanksi </b> : {{ $tabel->sanksi_aktif_sebelumnya}}<br/>
                                <b>Berakhir pada </b> : {{ strtoupper($dateRangka->make($tabel->sanksi_selesai_sebelumnya)?->translatedFormat('d F Y')) }}<br/><br/>
                                <b><i><u>Sanksi Diberikan</u></i></b> :<br/>
                                <b>Sanksi </b> : {{ $tabel->final_sanksi_jenis}}<br/>
                                <b>Tambahan </b> : {{ $tabel->final_sanksi_tambahan}}<br/>
                                <b>Mulai </b> : {{ strtoupper($dateRangka->make($tabel->final_sanksi_mulai)?->translatedFormat('d F Y'))}}<br/>
                                <b>Selesai </b> : {{ strtoupper($dateRangka->make($tabel->final_sanksi_selesai)?->translatedFormat('d F Y')) }}<br/>
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
            <button class="sekunder tcetak" onclick="ringkasTabel(this)" style="margin:0.5em 0">Panjang/Pendekkan Tampilan Tabel</button>
        </div>
    
    @else
        <p class="kartu">Tidak ada data.</p>
    @endisset
    
    <div class="pintasan tcetak">
        <a href="#" onclick="event.preventDefault();window.scrollTo(0,0)" title="Kembali Ke Atas">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#panahatas' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
        <a class="isi-xhr" href="{{ $urlRangka->current().'?'.http_build_query(array_merge($rekRangka->merge(['unduh' => 'excel'])->except(['page', 'bph']))) }}" data-rekam="false" data-laju="true" data-tujuan="#pelanggaran-sdm_sematan" title="Unduh Data">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#unduh' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
        <a class="isi-xhr" data-rekam="false" data-tujuan="#pelanggaran-sdm_sematan" href="{{ $urlRangka->route('sdm.pelanggaran.tambah') }}" title="Tambah Data">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#tambah' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
    </div>
    
    @isset($tabels)
    <script>
        pilDasar('#form_sdm_pelanggaran_cari .pil-dasar');
        pilCari('#form_sdm_pelanggaran_cari .pil-cari');
        pilSaja('#form_sdm_pelanggaran_cari .pil-saja');
        pilSaja('.trek-data .pil-saja');
        pilSaja('#pelanggaran-sdm_tabel .pil-saja');
        urutData('#sdm_pelanggaran_cariUrut','#sdm_pelanggaran_cariUrut [data-indeks]');
        formatIsian('#form_sdm_pelanggaran_cari .isian :is(textarea,input[type=text],input[type=search])');
        formatTabel('#pelanggaran-sdm_tabel thead th', '#pelanggaran-sdm_tabel tbody tr');
    </script>
    @endisset

    @includeWhen($rekRangka->session()->has('spanduk') || $rekRangka->session()->has('pesan') || $errors->any(), 'pemberitahuan')

</div>
@endsection
