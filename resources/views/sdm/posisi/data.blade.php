@extends('rangka')

@section('isi')
<div id="sdm_posisi">
    <h4>Data Pengaturan Jabatan</h4>
    @isset($tabels)
        <div class="cari-data tcetak">
            <form id="form_sdm_posisi_cari" class="form-xhr kartu" method="GET" action="{{ $urlRangka->current() }}">
                <div class="isian gspan-2">
                    <label for="sdm_posisi_cariKataKunci">Kata Kunci</label>
                    <input id="sdm_posisi_cariKataKunci" type="text" name="kata_kunci" value="{{ $rekRangka->kata_kunci }}">
                    <span class="t-bantu">Cari jabatan, kode WLKP, keterangan dan atasan</span>
                </div>
                <details class="gspan-4" {{ $rekRangka->anyFilled(['lokasi', 'kontrak', 'posisi_status']) ? 'open' : '' }} style="padding:0">
                    <summary>Penyaringan :</summary>                    
                    <div class="kartu form">
                        <div class="isian">
                            <label for="sdm_posisi_cariStatus">Saring Status</label>
                            <select id="sdm_posisi_cariStatus" name="posisi_status" class="pil-dasar">
                                <option selected disabled></option>
                                <option @selected($rekRangka->posisi_status == 'AKTIF')>AKTIF</option>
                                <option @selected($rekRangka->posisi_status == 'NON-AKTIF')>NON-AKTIF</option>
                            </select>
                            <span class="t-bantu">Pilih satu</span>
                        </div>
                        <div class="isian gspan-2">
                            <label for="sdm_posisi_cariStatusPenempatanSDM">Saring Lokasi</label>
                            <select id="sdm_posisi_cariStatusPenempatanSDM" name="lokasi[]" class="pil-cari" multiple>
                                @foreach ($lokasis as $lokasi)
                                    <option @selected(in_array($lokasi->atur_butir, (array) $rekRangka->lokasi)) @class(['merah' => $lokasi->atur_status == 'NON-AKTIF'])>{{ $lokasi->atur_butir }}</option>                            
                                @endforeach
                            </select>
                            <span class="t-bantu">Pilih satu atau lebih</span>
                        </div>
                        <div class="isian gspan-2">
                            <label for="sdm_posisi_StatusKontrakSDM">Saring Jenis Kontrak</label>
                            <select id="sdm_posisi_StatusKontrakSDM" name="kontrak[]" class="pil-cari" multiple>
                                @foreach ($kontraks as $kontrak)
                                    <option @selected(in_array($kontrak->atur_butir, (array) $rekRangka->kontrak)) @class(['merah' => $kontrak->atur_status == 'NON-AKTIF'])>{{ $kontrak->atur_butir }}</option>                            
                                @endforeach
                            </select>
                            <span class="t-bantu">Pilih satu atau lebih</span>
                        </div>
                    </div>
                </details>
                <details class="gspan-4" {{ $rekRangka->anyFilled('urut') ? 'open' : '' }} style="padding:0">
                    <summary>Pengurutan :</summary>
                    <div class="kartu form" id="sdm_posisi_cariUrut">
                        <div class="isian" data-indeks="{{ $urutPergantian ? $indexPergantian : 'X' }}">
                            <label for="sdm_posisi_cariUrutPergantian">{{ $urutPergantian ? $indexPergantian.'. ' : '' }}Urut % Pergantian</label>
                            <select id="sdm_posisi_cariUrutPergantian" name="urut[]" class="pil-dasar" onchange="getElementById('tombol_cari_posisi_sdm').click()">
                                <option selected disabled></option>
                                <option @selected(in_array('pergantian ASC', (array) $rekRangka->urut)) value="pergantian ASC">0 - 9</option>
                                <option @selected(in_array('pergantian DESC', (array) $rekRangka->urut)) value="pergantian DESC">9 - 0</option>
                            </select>
                            <span class="t-bantu">Pilih satu</span>
                        </div>
                        <div class="isian" data-indeks="{{ $urutPosisi ? $indexPosisi : 'X' }}">
                            <label for="sdm_posisi_cariUrutPosisi">{{ $urutPosisi ? $indexPosisi.'. ' : '' }}Urut Jabatan</label>
                            <select id="sdm_posisi_cariUrutPosisi" name="urut[]" class="pil-dasar" onchange="getElementById('tombol_cari_posisi_sdm').click()">
                                <option selected disabled></option>
                                <option @selected(in_array('posisi_nama ASC', (array) $rekRangka->urut)) value="posisi_nama ASC">A - Z</option>
                                <option @selected(in_array('posisi_nama DESC', (array) $rekRangka->urut)) value="posisi_nama DESC">Z - A</option>
                            </select>
                            <span class="t-bantu">Pilih satu</span>
                        </div>
                        <div class="isian" data-indeks="{{ $urutAktif ? $indexAktif : 'X' }}">
                            <label for="sdm_posisi_cariUrutAktif">{{ $urutAktif ? $indexAktif.'. ' : '' }}Urut Jml Aktif</label>
                            <select id="sdm_posisi_cariUrutAktif" name="urut[]" class="pil-dasar" onchange="getElementById('tombol_cari_posisi_sdm').click()">
                                <option selected disabled></option>
                                <option @selected(in_array('jml_aktif ASC', (array) $rekRangka->urut)) value="jml_aktif ASC">0 - 9</option>
                                <option @selected(in_array('jml_aktif DESC', (array) $rekRangka->urut)) value="jml_aktif DESC">9 - 0</option>
                            </select>
                            <span class="t-bantu">Pilih satu</span>
                        </div>
                        <div class="isian" data-indeks="{{ $urutNonAktif ? $indexNonAktif : 'X' }}">
                            <label for="sdm_posisi_cariUrutNonAktif">{{ $urutNonAktif ? $indexNonAktif.'. ' : '' }}Urut Jml Non-Aktif</label>
                            <select id="sdm_posisi_cariUrutNonAktif" name="urut[]" class="pil-dasar" onchange="getElementById('tombol_cari_posisi_sdm').click()">
                                <option selected disabled></option>
                                <option @selected(in_array('jml_nonaktif ASC', (array) $rekRangka->urut)) value="jml_nonaktif ASC">0 - 9</option>
                                <option @selected(in_array('jml_nonaktif DESC', (array) $rekRangka->urut)) value="jml_nonaktif DESC">9 - 0</option>
                            </select>
                            <span class="t-bantu">Pilih satu</span>
                        </div>
                    </div>
                </details>
                <div class="gspan-4"></div>
                <button id="tombol_cari_posisi_sdm" class="utama pelengkap" type="submit">
                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#cari' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                    CARI
                </button>
            </form>            
        </div>

        <div id="posisi-sdm_sematan" style="scroll-margin:4em 0 0 0"></div>
        
        <div class="kartu">
            <b><i>Jumlah SDM ({{ $rekRangka->anyFilled(['lokasi', 'kontrak']) ? 'sesuai data penyaringan' : 'global' }}) : Aktif = {{number_format($aktif, 0, ',', '.')}} Personil -> Non-Aktif = {{number_format($nonAktif, 0, ',', '.')}} Personil -> Total = {{number_format($total, 0, ',', '.')}} Personil.</i></b>
            <div class="trek-data tcetak">
                <span class="bph">
                    <label for="sdm_posisi_cariPerHalaman">Baris per halaman : </label>
                    <select id="sdm_posisi_cariPerHalaman" name="bph" class="pil-saja" form="form_sdm_posisi_cari" onchange="getElementById('tombol_cari_posisi_sdm').click()">
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
                <table id="posisi-sdm_tabel" class="tabel">
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
                                    <button>
                                        <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#menuvert' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                                        </svg>
                                    </button>
                                    <div class="aksi">
                                        <a class="isi-xhr" data-rekam="false" data-tujuan="#posisi-sdm_sematan" href="{{ $urlRangka->route('sdm.posisi.lihat', ['uuid' => $tabel->posisi_uuid]) }}" title="Lihat Data">Lihat Data</a>
                                        <a class="isi-xhr" data-rekam="false" data-tujuan="#posisi-sdm_sematan" href="{{ $urlRangka->route('sdm.posisi.ubah', ['uuid' => $tabel->posisi_uuid]) }}" title="Ubah Data">Ubah Data</a>
                                    </div>
                                </div>
                            </th>
                            <td>{{$tabels->firstItem() + $nomor}}</td>
                            <td>{{$tabel->posisi_nama}}</td>
                            <td><u><a class="isi-xhr" href="{{ $urlRangka->route('sdm.penempatan.data-aktif', ['posisi' => $tabel->posisi_nama, ...$rekRangka->only(['lokasi', 'kontrak'])]) }}">{{number_format($tabel->jml_aktif, 0, ',', '.')}}</a></u></td>
                            <td><u><a class="isi-xhr" href="{{ $urlRangka->route('sdm.penempatan.data-nonaktif', ['posisi' => $tabel->posisi_nama, ...$rekRangka->only(['lokasi', 'kontrak'])]) }}">{{number_format($tabel->jml_nonaktif, 0, ',', '.')}}</a></u></td>
                            <td>{{ number_format($tabel->pergantian, 2, ',', '.') }} %</td>
                            <td>{{$tabel->posisi_atasan}}</td>
                            <td>{{$tabel->posisi_wlkp}}</td>
                            <td>{{$tabel->posisi_status}}</td>
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
        <a class="isi-xhr" data-rekam="false" data-tujuan="#posisi-sdm_sematan" href="{{ $urlRangka->route('sdm.posisi.unggah') }}" title="Unggah Data">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#unggah' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
        <a class="isi-xhr" href="{{ $urlRangka->current().'?'.http_build_query(array_merge($rekRangka->merge(['unduh' => 'excel'])->except(['page', 'bph']))) }}" data-rekam="false" data-laju="true" data-tujuan="#posisi-sdm_sematan" title="Unduh Data">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#unduh' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
        <a class="isi-xhr" data-rekam="false" data-tujuan="#posisi-sdm_sematan" href="{{ $urlRangka->route('sdm.posisi.tambah') }}" title="Tambah Data">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#tambah' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
    </div>
    
    @isset($tabels)
    <script>
        pilDasar('#form_sdm_posisi_cari .pil-dasar');
        pilCari('#form_sdm_posisi_cari .pil-cari');
        pilSaja('#posisi-sdm_tabel .pil-saja');
        pilSaja('.trek-data .pil-saja');
        pilSaja('#form_sdm_posisi_cari .pil-saja');
        urutData('#sdm_posisi_cariUrut','#sdm_posisi_cariUrut [data-indeks]');
        formatIsian('#form_sdm_posisi_cari .isian :is(textarea,input[type=text],input[type=search])');
        formatTabel('#posisi-sdm_tabel thead th', '#posisi-sdm_tabel tbody tr');
    </script>
    @endisset

    @includeWhen($rekRangka->session()->has('spanduk') || $rekRangka->session()->has('pesan') || $errors->any(), 'pemberitahuan')

</div>
@endsection
