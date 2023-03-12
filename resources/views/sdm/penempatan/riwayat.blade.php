@extends('rangka')

@section('isi')
<div id="sdm_penempatan_status">
    <h4>Kelola Penempatan SDM</h4>
    @isset($tabels)
        <div class="cari-data tcetak">
            <form id="form_sdm_penempatan_status_cari" class="form-xhr kartu" data-tujuan="#riwa-penem-sdm_tabels" data-frag="true" method="GET" action="{{ $urlRangka->current() }}">
                <input type="hidden" name="fragment" value="riwa-penem-sdm_tabels">

                <details class="gspan-4" {{ $rekRangka->anyFilled(['lokasi', 'kontrak', 'kategori', 'pangkat', 'kelamin', 'posisi', 'agama', 'kawin', 'warganegara', 'pendidikan', 'disabilitas']) ? 'open' : '' }} style="padding:0">
                    <summary class="cari">
                        <div class="isian gspan-4">
                            <input type="text" placeholder="Isi kata kunci lalu Enter" name="kata_kunci" value="{{ $rekRangka->kata_kunci }}">
                        </div>
                    </summary>
                    <div class="kartu form">
                        <div class="isian">
                            <label for="sdm_penempatan_status_cariStatusPenempatanSDM">Saring Lokasi</label>
                            <select id="sdm_penempatan_status_cariStatusPenempatanSDM" name="lokasi[]" class="pil-cari" multiple>
                                @foreach ($penempatans as $lokasi)
                                    <option @selected(in_array($lokasi->atur_butir, (array) $rekRangka->lokasi)) @class(['merah' => $lokasi->atur_status == 'NON-AKTIF'])>{{ $lokasi->atur_butir }}</option>                            
                                @endforeach
                            </select>
                            <span class="t-bantu">Pilih satu atau lebih</span>
                        </div>
                        <div class="isian">
                            <label for="sdm_penempatan_status_cariStatusKontrakSDM">Saring Jenis Kontrak</label>
                            <select id="sdm_penempatan_status_cariStatusKontrakSDM" name="kontrak[]" class="pil-cari" multiple>
                                @foreach ($kontraks as $kontrak)
                                    <option @selected(in_array($kontrak->atur_butir, (array) $rekRangka->kontrak)) @class(['merah' => $kontrak->atur_status == 'NON-AKTIF'])>{{ $kontrak->atur_butir }}</option>                            
                                @endforeach
                            </select>
                            <span class="t-bantu">Pilih satu atau lebih</span>
                        </div>
                        <div class="isian">
                            <label for="sdm_penempatan_status_cariStatusKategoriSDM">Saring Kategori</label>
                            <select id="sdm_penempatan_status_cariStatusKategoriSDM" name="kategori[]" class="pil-cari" multiple>
                                @foreach ($kategoris as $kategori)
                                    <option @selected(in_array($kategori->atur_butir, (array) $rekRangka->kategori)) @class(['merah' => $kategori->atur_status == 'NON-AKTIF'])>{{ $kategori->atur_butir }}</option>                            
                                @endforeach
                            </select>
                            <span class="t-bantu">Pilih satu atau lebih</span>
                        </div>
                        <div class="isian">
                            <label for="sdm_penempatan_status_cariStatusPangkatSDM">Saring Pangkat</label>
                            <select id="sdm_penempatan_status_cariStatusPangkatSDM" name="pangkat[]" class="pil-cari" multiple>
                                @foreach ($pangkats as $pangkat)
                                    <option @selected(in_array($pangkat->atur_butir, (array) $rekRangka->pangkat)) @class(['merah' => $pangkat->atur_status == 'NON-AKTIF'])>{{ $pangkat->atur_butir }}</option>                            
                                @endforeach
                            </select>
                            <span class="t-bantu">Pilih satu atau lebih</span>
                        </div>
                        <div class="isian gspan-2">
                            <label for="sdm_penempatan_status_cariStatusJabatanSDM">Saring Jabatan</label>
                            <select id="sdm_penempatan_status_cariStatusJabatanSDM" name="posisi[]" class="pil-cari" multiple>
                                @foreach ($posisis as $posisi)
                                <option @selected(in_array($posisi->posisi_nama, (array) $rekRangka->posisi)) @class(['merah' => $posisi->posisi_status == 'NON-AKTIF'])>{{ $posisi->posisi_nama }}</option>                            
                                @endforeach
                            </select>
                            <span class="t-bantu">Pilih satu atau lebih</span>
                        </div>
                        <div class="isian">
                            <label for="sdm_penempatan_status_cariStatusKelaminSDM">Saring Kelamin</label>
                            <select id="sdm_penempatan_status_cariStatusKelaminSDM" name="kelamin[]" multiple class="pil-saja">
                                @foreach ($kelamins as $kelamin)
                                    <option @selected(in_array($kelamin->atur_butir, (array) $rekRangka->kelamin)) @class(['merah' => $kelamin->atur_status == 'NON-AKTIF'])>{{ $kelamin->atur_butir }}</option>                            
                                @endforeach
                            </select>
                            <span class="t-bantu">Pilih satu atau lebih</span>
                        </div>
                        <div class="isian">
                            <label for="sdm_penempatan_status_cariAgamaSDM">Saring Agama</label>
                            <select id="sdm_penempatan_status_cariAgamaSDM" name="agama[]" multiple class="pil-saja">
                                @foreach ($agamas as $agama)
                                    <option @selected(in_array($agama->atur_butir, (array) $rekRangka->agama)) @class(['merah' => $agama->atur_status == 'NON-AKTIF'])>{{ $agama->atur_butir }}</option>                            
                                @endforeach
                            </select>
                            <span class="t-bantu">Pilih satu atau lebih</span>
                        </div>
                        <div class="isian">
                            <label for="sdm_penempatan_status_cariStatusKawinSDM">Saring Status Kawin</label>
                            <select id="sdm_penempatan_status_cariStatusKawinSDM" name="kawin[]" multiple class="pil-saja">
                                @foreach ($kawins as $kawin)
                                    <option @selected(in_array($kawin->atur_butir, (array) $rekRangka->kawin)) @class(['merah' => $kawin->atur_status == 'NON-AKTIF'])>{{ $kawin->atur_butir }}</option>                            
                                @endforeach
                            </select>
                            <span class="t-bantu">Pilih satu atau lebih</span>
                        </div>
                        <div class="isian">
                            <label for="sdm_penempatan_status_cariPendidikanSDM">Saring Pendidikan</label>
                            <select id="sdm_penempatan_status_cariPendidikanSDM" name="pendidikan[]" multiple class="pil-saja">
                                @foreach ($pendidikans as $pendidikan)
                                    <option @selected(in_array($pendidikan->atur_butir, (array) $rekRangka->pendidikan)) @class(['merah' => $pendidikan->atur_status == 'NON-AKTIF'])>{{ $pendidikan->atur_butir }}</option>                            
                                @endforeach
                            </select>
                            <span class="t-bantu">Pilih satu atau lebih</span>
                        </div>
                        <div class="isian">
                            <label for="sdm_penempatan_status_cariWarganegaraSDM">Saring Warganegara</label>
                            <select id="sdm_penempatan_status_cariWarganegaraSDM" name="warganegara[]" multiple class="pil-saja">
                                @foreach ($warganegaras as $warganegara)
                                    <option @selected(in_array($warganegara->atur_butir, (array) $rekRangka->warganegara)) @class(['merah' => $warganegara->atur_status == 'NON-AKTIF'])>{{ $warganegara->atur_butir }}</option>                            
                                @endforeach
                            </select>
                            <span class="t-bantu">Pilih satu atau lebih</span>
                        </div>
                        <div class="isian">
                            <label for="sdm_penempatan_status_cariDisabilitasSDM">Saring Disabilitas</label>
                            <select id="sdm_penempatan_status_cariDisabilitasSDM" name="disabilitas[]" multiple class="pil-saja">
                                @foreach ($disabilitases as $disabilitas)
                                    <option @selected(in_array($disabilitas->atur_butir, (array) $rekRangka->disabilitas)) @class(['merah' => $disabilitas->atur_status == 'NON-AKTIF'])>{{ $disabilitas->atur_butir }}</option>                            
                                @endforeach
                            </select>
                            <span class="t-bantu">Pilih satu atau lebih</span>
                        </div>
                    </div>
                    <div class="gspan-4"></div>
                    <button id="tombol_cari_status_penempatan" class="utama pelengkap" type="submit">
                        <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="{{ $mixRangka('/ikon.svg') . '#cari' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                        </svg>
                        CARI
                    </button>
                </details>
            </form>
        </div>

        
        <div id="riwa-penem-sdm_tabels" class="kartu" style="scroll-margin:4em 0 0 0">
            @fragment('riwa-penem-sdm_tabels')
            <span class="merah">Merah</span> : Non-Aktif. <span class="oranye">Oranye</span> : Kontrak Kadaluarsa/Akan Habis. <span class="biru">Biru</span> : <i>Outsource</i>.
            <div id="sdm_penem_riwa_sematan" style="scroll-margin:4em 0 0 0"></div>
            <div class="trek-data cari-data tcetak">
                <div class="isian" style="margin:0 0 1em">
                    <select id="sdm_penempatan_status_cariStatusAktifSDM" class="pil-saja tombol" onchange="if (this.value !== '') lemparXHR(true, null, this.value)">
                        <option value="{{ $urlRangka->route('sdm.penempatan.riwayat', $rekRangka->except('unduh'), false) }}" @selected($rekRangka->routeIs('sdm.penempatan.riwayat'))>SEMUA RIWAYAT</option>
                        <option value="{{ $urlRangka->route('sdm.penempatan.data-aktif', $rekRangka->except('unduh'), false) }}" @selected($rekRangka->routeIs('sdm.penempatan.data-aktif'))>AKTIF</option>
                        <option value="{{ $urlRangka->route('sdm.penempatan.data-nonaktif', $rekRangka->except('unduh'), false) }}" @selected($rekRangka->routeIs('sdm.penempatan.data-nonaktif'))>NON-AKTIF</option>
                        <option value="{{ $urlRangka->route('sdm.penempatan.data-akanhabis', $rekRangka->except('unduh'), false) }}" @selected($rekRangka->routeIs('sdm.penempatan.data-akanhabis'))>AKAN HABIS</option>
                        <option value="{{ $urlRangka->route('sdm.penempatan.data-kadaluarsa', $rekRangka->except('unduh'), false) }}" @selected($rekRangka->routeIs('sdm.penempatan.data-kadaluarsa'))>KADALUARSA</option>
                        <option value="{{ $urlRangka->route('sdm.penempatan.data-baru', $rekRangka->except('unduh'), false) }}" @selected($rekRangka->routeIs('sdm.penempatan.data-baru'))>BELUM DITEMPATKAN</option>
                        <option value="{{ $urlRangka->route('sdm.penempatan.data-batal', $rekRangka->except('unduh'), false) }}" @selected($rekRangka->routeIs('sdm.penempatan.data-batal'))>BATAL DITEMPATKAN</option>
                        <option value="{{ $urlRangka->route('sdm.penempatan.riwayat-nyata', $rekRangka->except('unduh'), false) }}" @selected($rekRangka->routeIs('sdm.penempatan.riwayat-nyata'))>MASA KERJA NYATA</option>
                    </select>
                </div>
                <span class="bph">
                    <label for="sdm_penempatan_status_cariPerHalaman">Baris per halaman : </label>
                    <select id="sdm_penempatan_status_cariPerHalaman" name="bph" class="pil-saja" form="form_sdm_penempatan_status_cari" onchange="getElementById('tombol_cari_status_penempatan').click()">
                        <option>100</option>
                        <option @selected($tabels->perPage() == 250)>250</option>
                        <option @selected($tabels->perPage() == 500)>500</option>
                        <option @selected($tabels->perPage() == 1000)>1000</option>
                    </select>
                </span>
                <span class="ket">{{number_format($tabels->firstItem(), 0, ',', '.')}} - {{number_format($tabels->lastItem(), 0, ',', '.')}} dari {{number_format($tabels->total(), 0, ',', '.')}} data</span>
                @if($tabels->hasPages())
                <span class="trek">
                    @if($tabels->currentPage() > 1)
                        <a class="isi-xhr" data-tujuan="#riwa-penem-sdm_tabels" data-frag="true" href="{{ $tabels->url(1) }}" title="Awal">
                            <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#awal' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                            </svg>
                        </a>
                    @endif
                    @if($tabels->previousPageUrl())
                    <a class="isi-xhr" data-tujuan="#riwa-penem-sdm_tabels" data-frag="true" href="{{ $tabels->previousPageUrl() }}" title="Sebelumnya">
                        <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="{{ $mixRangka('/ikon.svg') . '#mundur' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                        </svg>
                    </a>
                    @endif
                    @if($tabels->nextPageUrl())
                    <a class="isi-xhr" data-tujuan="#riwa-penem-sdm_tabels" data-frag="true" href="{{ $tabels->nextPageUrl() }}" title="Berikutnya">
                        <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="{{ $mixRangka('/ikon.svg') . '#maju' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                        </svg>
                    </a>
                    <a class="isi-xhr" data-tujuan="#riwa-penem-sdm_tabels" data-frag="true" href="{{ $tabels->url($tabels->lastPage()) }}" title="Akhir">
                        <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="{{ $mixRangka('/ikon.svg') . '#akhir' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                        </svg>
                    </a>
                    @endif
                </span>
                @endif
                <details class="gspan-4" {{ $rekRangka->anyFilled('urut') ? 'open' : '' }} style="padding:1em 0">
                    <summary>Pengurutan :</summary>
                    <div class="kartu form" id="sdm_penempatan_status_urut">
                        <div class="isian" data-indeks="{{ $urutAbsen ? $indexAbsen : 'X' }}">
                            <label for="sdm_penempatan_status_urutAbsen">{{ $urutAbsen ? $indexAbsen.'. ' : '' }}Urut Nomor Absen</label>
                            <select id="sdm_penempatan_status_urutAbsen" name="urut[]" form="form_sdm_penempatan_status_cari" class="pil-dasar" onchange="getElementById('tombol_cari_status_penempatan').click()">
                                <option selected disabled></option>
                                <option @selected(in_array('sdm_no_absen ASC', (array) $rekRangka->urut)) value="sdm_no_absen ASC">0 - 9</option>
                                <option @selected(in_array('sdm_no_absen DESC', (array) $rekRangka->urut)) value="sdm_no_absen DESC">9 - 0</option>
                            </select>
                            <span class="t-bantu">Pilih satu</span>
                        </div>
                        <div class="isian" data-indeks="{{ $urutMasuk ? $indexMasuk : 'X' }}">
                            <label for="sdm_penempatan_status_urutMasuk">{{ $urutMasuk ? $indexMasuk.'. ' : '' }}Urut Tanggal Masuk</label>
                            <select id="sdm_penempatan_status_urutMasuk" name="urut[]" form="form_sdm_penempatan_status_cari" class="pil-dasar" onchange="getElementById('tombol_cari_status_penempatan').click()">
                                <option selected disabled></option>
                                <option @selected(in_array('sdm_tgl_gabung ASC', (array) $rekRangka->urut)) value="sdm_tgl_gabung ASC">Lama - Baru</option>
                                <option @selected(in_array('sdm_tgl_gabung DESC', (array) $rekRangka->urut)) value="sdm_tgl_gabung DESC">Baru - Lama</option>
                            </select>
                            <span class="t-bantu">Pilih satu</span>
                        </div>
                        <div class="isian" data-indeks="{{ $urutLahir ? $indexLahir : 'X' }}">
                            <label for="sdm_penempatan_status_urutLahir">{{ $urutLahir ? $indexLahir.'. ' : '' }}Urut Tanggal Lahir</label>
                            <select id="sdm_penempatan_status_urutLahir" name="urut[]" form="form_sdm_penempatan_status_cari" class="pil-dasar" onchange="getElementById('tombol_cari_status_penempatan').click()">
                                <option selected disabled></option>
                                <option @selected(in_array('sdm_tgl_lahir ASC', (array) $rekRangka->urut)) value="sdm_tgl_lahir ASC">Lama - Baru</option>
                                <option @selected(in_array('sdm_tgl_lahir DESC', (array) $rekRangka->urut)) value="sdm_tgl_lahir DESC">Baru - Lama</option>
                            </select>
                            <span class="t-bantu">Pilih satu</span>
                        </div>
                        <div class="isian" data-indeks="{{ $urutKeluar ? $indexKeluar : 'X' }}">
                            <label for="sdm_penempatan_status_urutKeluar">{{ $urutKeluar ? $indexKeluar.'. ' : '' }}Urut Tanggal Keluar</label>
                            <select id="sdm_penempatan_status_urutKeluar" name="urut[]" form="form_sdm_penempatan_status_cari" class="pil-dasar" onchange="getElementById('tombol_cari_status_penempatan').click()">
                                <option selected disabled></option>
                                <option @selected(in_array('sdm_tgl_berhenti ASC', (array) $rekRangka->urut)) value="sdm_tgl_berhenti ASC">Lama - Baru</option>
                                <option @selected(in_array('sdm_tgl_berhenti DESC', (array) $rekRangka->urut)) value="sdm_tgl_berhenti DESC">Baru - Lama</option>
                            </select>
                            <span class="t-bantu">Pilih satu</span>
                        </div>
                    </div>
                </details>
            </div>

            @include('sdm.penempatan.riwayat-data', ['tabels' => $tabels])
            
            @endfragment
        </div>
    
    @else
        <p class="kartu">Tidak ada data.</p>
    @endisset
    
    <div class="pintasan tcetak">
        <a href="#" onclick="event.preventDefault();window.scrollTo(0,0)" title="Kembali Ke Atas">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#panahatas' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
        <a class="isi-xhr" data-rekam="false" href="{{ $urlRangka->route('sdm.penempatan.unggah', [], false) }}" data-tujuan="#sdm_penem_riwa_sematan" title="Unggah Data Penempatan SDM">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#unggah' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
        <a class="isi-xhr" href="{{ $urlRangka->current().'?'.http_build_query(array_merge($rekRangka->merge(['unduh' => 'excel'])->except(['page', 'bph']))) }}" data-rekam="false" data-laju="true" data-tujuan="#sdm_penem_riwa_sematan" title="Unduh Data">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#unduh' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
        <a class="isi-xhr" href="{{ $urlRangka->route('register', [], false) }}" title="Tambah Data SDM">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#tambahorang' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
        <a class="isi-xhr" href="{{ $urlRangka->route('sdm.penempatan.statistik', [], false) }}" data-rekam="false" data-laju="true" data-tujuan="#sdm_penem_riwa_sematan" title="Unduh Statistik Penempatan SDM ">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#statistik' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
    </div>
    
    @isset($tabels)
    <script>
        pilDasar('#form_sdm_penempatan_status_cari .pil-dasar');
        pilSaja('#form_sdm_penempatan_status_cari .pil-saja');
        pilCari('#form_sdm_penempatan_status_cari .pil-cari');
        urutData('#sdm_penempatan_status_urut','#sdm_penempatan_status_urut [data-indeks]');
        formatIsian('#form_sdm_penempatan_status_cari .isian :is(textarea,input[type=text],input[type=search])');
    </script>
    @endisset

</div>
@endsection
