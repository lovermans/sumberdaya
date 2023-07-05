@extends('rangka')

@section('isi')
<div id="sdm_nilai">
    <h4>Data Penilaian Berkala SDM</h4>
    @isset($tabels)
    <div class="cari-data tcetak">

        <form id="form_sdm_nilai_cari" class="form-xhr kartu" data-tujuan="#nilai-sdm_tabels" data-frag="true"
            method="GET" data-blank="true">
            <input type="hidden" name="fragment" value="nilai-sdm_tabels">

            <details class="gspan-4" {{ $rekRangka->anyFilled(['nilaisdm_tahun', 'nilaisdm_periode',
                'nilaisdm_kontrak']) ?
                'open' : '' }}>

                <summary class="cari">
                    <div class="isian gspan-4">
                        <input type="text" id="kata_kunci_nilai_sdm" name="kata_kunci"
                            value="{{ $rekRangka->kata_kunci }}" aria-label="Cari Kata Kunci">

                        <button id="tombol_cari_nilai" class="cari-cepat" type="submit" title="Cari Data">
                            <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#cari' }}"
                                    xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                            </svg>
                        </button>
                    </div>
                </summary>

                <div class="kartu form">
                    <div class="isian normal">
                        <label for="sdm_nilai_cariTahun">Saring Tahun</label>

                        <select id="sdm_nilai_cariTahun" name="nilaisdm_tahun[]" class="pil-cari" multiple>
                            @foreach (range(2020,date("Y")) as $tahun)
                            <option @selected(in_array($tahun, (array) $rekRangka->sanksi_jenis))>{{ $tahun }}</option>
                            @endforeach
                        </select>

                        <span class="t-bantu">Pilih satu atau lebih</span>
                    </div>

                    <div class="isian normal">
                        <label for="sdm_nilai_cariPeriode">Saring Periode</label>

                        <select id="sdm_nilai_cariPeriode" name="nilaisdm_periode[]" class="pil-cari" multiple>
                            <option @selected($rekRangka->nilaisdm_periode == 'SEMESTER-I')>SEMESTER-I</option>
                            <option @selected($rekRangka->nilaisdm_periode == 'SEMESTER-II')>SEMESTER-II</option>
                        </select>

                        <span class="t-bantu">Pilih satu atau lebih</span>
                    </div>

                    <div class="isian normal">
                        <label for="sdm_nilai_cariPenempatan">Saring Penempatan</label>

                        <select id="sdm_nilai_cariPenempatan" name="nilaisdm_penempatan[]" class="pil-cari" multiple>
                            @foreach ($lokasis as $lokasi)
                            <option @selected(in_array($lokasi->atur_butir, (array)
                                $rekRangka->nilaisdm_penempatan)) @class(['merah' => $lokasi->atur_status ==
                                'NON-AKTIF'])>{{ $lokasi->atur_butir }}</option>
                            @endforeach
                        </select>

                        <span class="t-bantu">Pilih satu atau lebih</span>
                    </div>

                    <div class="isian normal">
                        <label for="sdm_nilai_cariKontrak">Saring Status SDM</label>

                        <select id="sdm_nilai_cariKontrak" name="nilaisdm_kontrak[]" class="pil-cari" multiple>
                            @foreach ($statusSDMs as $statusSDM)
                            <option @selected(in_array($statusSDM->atur_butir, (array)
                                $rekRangka->nilaisdm_kontrak)) @class(['merah' => $statusSDM->atur_status ==
                                'NON-AKTIF'])>{{ $statusSDM->atur_butir }}</option>
                            @endforeach
                        </select>

                        <span class="t-bantu">Pilih satu atau lebih</span>
                    </div>

                </div>
            </details>
        </form>
    </div>

    <div id="nilai-sdm_tabels" class="kartu scroll-margin">
        @fragment('nilai-sdm_tabels')
        @unless ($halamanAkun ?? null)
        <b><i><small>Jumlah SDM ({{ $rekRangka->anyFilled(['nilaisdm_tahun', 'nilaisdm_periode', 'nilaisdm_kontrak']) ?
                    'sesuai data penyaringan' : 'global'
                    }}) : Organik = {{number_format($jumlahOrganik, 0, ',', '.')}} Personil | Outsource =
                    {{number_format($jumlahOS, 0, ',', '.')}} Personil.</small></i></b>
        @endunless
        <div id="nilai-sdm_sematan" class="scroll-margin"></div>

        <div class="trek-data tcetak">
            @unless ($halamanAkun ?? null)
            <span class="bph">
                <label for="sdm_nilai_cariPerHalaman">Baris per halaman : </label>

                <select id="sdm_nilai_cariPerHalaman" name="bph" class="pil-saja" form="form_sdm_nilai_cari"
                    onchange="getElementById('tombol_cari_nilai').click()">
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
                <a class="isi-xhr" data-tujuan="#nilai-sdm_tabels" data-frag="true" href="{{ $tabels->url(1) }}"
                    title="Awal">
                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#awal' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                </a>
                @endif

                @if($tabels->previousPageUrl())
                <a class="isi-xhr" data-tujuan="#nilai-sdm_tabels" data-frag="true"
                    href="{{ $tabels->previousPageUrl() }}" title="Sebelumnya">
                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#mundur' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                </a>
                @endif

                @if($tabels->nextPageUrl())
                <a class="isi-xhr" data-tujuan="#nilai-sdm_tabels" data-frag="true" href="{{ $tabels->nextPageUrl() }}"
                    title="Berikutnya">
                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#maju' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                </a>

                <a class="isi-xhr" data-tujuan="#nilai-sdm_tabels" data-frag="true"
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
                <div class="kartu form" id="sdm_nilai_cariUrut">
                    <div class="isian" data-indeks="{{ $urutTahun ? $indexTahun : 'X' }}">
                        <label for="sdm_nilai_tambah_cariUrutTahun">{{ $urutTahun ?
                            $indexTahun . '. ' : '' }}Urut Tahun Penilaian</label>
                        <select id="sdm_nilai_tambah_cariUrutTahun" name="urut[]" class="pil-dasar"
                            form="form_sdm_nilai_cari" onchange="getElementById('tombol_cari_nilai').click()">
                            <option selected disabled></option>
                            <option @selected(in_array('nilaisdm_tahun ASC', (array) $rekRangka->urut))
                                value="nilaisdm_tahun ASC">0 - 9</option>
                            <option @selected(in_array('nilaisdm_tahun DESC', (array) $rekRangka->urut))
                                value="nilaisdm_tahun DESC">9 - 0</option>
                        </select>
                        <span class="t-bantu">Pilih satu</span>
                    </div>

                    <div class="isian" data-indeks="{{ $urutPeriode ? $indexPeriode : 'X' }}">
                        <label for="sdm_nilai_tambah_cariUrutPeriode">{{ $urutPeriode ?
                            $indexPeriode.'. '
                            : '' }}Urut Periode Penilaian</label>
                        <select id="sdm_nilai_tambah_cariUrutPeriode" name="urut[]" class="pil-dasar"
                            form="form_sdm_nilai_cari" onchange="getElementById('tombol_cari_nilai').click()">
                            <option selected disabled></option>
                            <option @selected(in_array('nilaisdm_periode ASC', (array) $rekRangka->urut))
                                value="nilaisdm_periode ASC">A - Z</option>
                            <option @selected(in_array('nilaisdm_periode DESC', (array) $rekRangka->urut))
                                value="nilaisdm_periode DESC">Z - A</option>
                        </select>
                        <span class="t-bantu">Pilih satu</span>
                    </div>
                </div>
            </details>
            @endunless
        </div>

        <span class="biru">Biru</span> : Outsource.

        <div class="data ringkas">
            <table id="sanksi-sdm_tabel" class="tabel">
                <thead>
                    <tr>
                        <th></th>
                        <th>No</th>
                        @unless ($halamanAkun ?? null)
                        <th>Identitas</th>
                        @endunless
                        <th>Tahun/Periode</th>
                        <th>Penilaian</th>
                        <th>Lain-lain</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tabels as $nomor => $tabel)
                    <tr @class([ 'biru'=> $strRangka->contains($tabel->penempatan_kontrak, 'OS-')])>
                        <th>
                            <div class="pil-aksi">
                                <button id="{{ 'aksi_nilai_baris_' .$tabels->firstItem() + $nomor}}"
                                    title="Pilih Tindakan">
                                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#menuvert' }}"
                                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                                    </svg>
                                </button>
                                <div class="aksi">
                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#nilai-sdm_sematan"
                                        href="{{ $urlRangka->route('sdm.penilaian.lihat', ['uuid' => $tabel->nilaisdm_uuid], false) }}"
                                        title="Lihat/Ubah Penilaian">Lihat/Ubah Penilaian</a>
                                </div>
                            </div>
                        </th>
                        <td>{{$tabels->firstItem() + $nomor}}</td>
                        @unless ($halamanAkun ?? null)
                        <td>
                            <div @class(['merah'=> $tabel->sdm_tgl_berhenti])>
                                <a class="isi-xhr taut-akun"
                                    href="{{ $urlRangka->route('sdm.akun', ['uuid' => $tabel->sdm_uuid], false) }}">
                                    <img @class(['akun', 'svg'=> !$storageRangka->exists('sdm/foto-profil/' .
                                    $tabel->nilaisdm_no_absen . '.webp')]) src="{{
                                    $storageRangka->exists('sdm/foto-profil/' . $tabel->nilaisdm_no_absen .
                                    '.webp')
                                    ? $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' =>
                                    $tabel->nilaisdm_no_absen . '.webp' . '?' .
                                    filemtime($appRangka->storagePath('app/sdm/foto-profil/' .
                                    $tabel->nilaisdm_no_absen . '.webp'))], false) :
                                    $mixRangka('/ikon.svg') . '#akun' }}" alt="{{
                                    $tabel->sdm_nama ?? 'foto akun' }}" title="{{
                                    $tabel->sdm_nama
                                    ?? 'foto akun' }}" loading="lazy">
                                </a>
                                {{$tabel->nilaisdm_no_absen}} - {{$tabel->sdm_nama}} <br />
                                {{$tabel->penempatan_lokasi}} {{$tabel->penempatan_kontrak}} -
                                {{$tabel->penempatan_posisi}} {{ $tabel->sdm_tgl_berhenti ? '(NON-AKTIF)'
                                :
                                '' }}
                            </div>
                        </td>
                        @endunless
                        <td>
                            <b>Tahun</b> : {{ $tabel->nilaisdm_tahun }} <br>
                            <b>Periode</b> : {{ $tabel->nilaisdm_periode }}
                        </td>
                        <td>
                            <b>Bobot Kehadiran</b> : {{ $tabel->nilaisdm_bobot_hadir }} <br>
                            <b>Bobot Sikap Kerja</b> : {{ $tabel->nilaisdm_bobot_sikap }} <br>
                            <b>Bobot Target Pekerjaan</b> : {{ $tabel->nilaisdm_bobot_target }}
                        </td>
                        <td>
                            <b>Tindak Lanjut</b> : {!! nl2br($tabel->nilaisdm_tindak_lanjut) !!} <br>
                            <b>Keterangan</b> :{!! nl2br($tabel->nilaisdm_keterangan) !!}
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
        <button class="sekunder tcetak" onclick="ringkasTabel(this)">Panjang/Pendekkan
            Tampilan Tabel</button>

        <script>
            (async() => {
                    while(!window.aplikasiSiap) {
                        await new Promise((resolve,reject) =>
                        setTimeout(resolve, 1000));
                    }
                    
                    pilDasar('#nilai-sdm_tabels .pil-dasar');
                    pilSaja('#nilai-sdm_tabels .pil-saja');
                    urutData('#sdm_nilai_cariUrut','#sdm_nilai_cariUrut [data-indeks]');
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
        <a class="isi-xhr" data-rekam="false" href="{{ $urlRangka->route('sdm.penilaian.unggah', [], false) }}"
            data-tujuan="#nilai-sdm_sematan" title="Unggah Data Penilaian SDM">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#unggah' }}" xmlns:xlink="http://www.w3.org/1999/xlink">
                </use>
            </svg>
        </a>
        <a href="#" title="Unduh Data"
            onclick="event.preventDefault();lemparXHR({tujuan : '#nilai-sdm_sematan', tautan : window.location.search ? window.location.pathname + window.location.search + '&unduh=excel' : window.location.pathname + '?unduh=excel', strim : true})">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#unduh' }}" xmlns:xlink="http://www.w3.org/1999/xlink">
                </use>
            </svg>
        </a>
        <a class="isi-xhr" data-rekam="false" data-tujuan="#nilai-sdm_sematan"
            href="{{ $urlRangka->route('sdm.penilaian.tambah', [], false) }}" title="Tambah Data">
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

            pilDasar('#form_sdm_nilai_cari .pil-dasar');
            pilCari('#form_sdm_nilai_cari .pil-cari');
            pilSaja('#form_sdm_nilai_cari .pil-saja');
            formatIsian('#form_sdm_nilai_cari .isian :is(textarea,input[type=text],input[type=search])');
        })();
    </script>
    @endisset
</div>
@endsection