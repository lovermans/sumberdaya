@extends('rangka')

@section('isi')
<div id="sdm_permintaan_tambah">
    <h4>Kelola Permintaan Tambah SDM</h4>
    @isset($tabels)
    <div class="cari-data tcetak">
        <form id="form_sdm_permintaan_tambah_cari" class="form-xhr kartu" data-tujuan="#tambah_sdm_tabels" data-frag="true" method="GET" action="{{ $urlRangka->current() }}">
            <input type="hidden" name="fragment" value="tambah_sdm_tabels">
            
            <details class="gspan-4" {{ $rekRangka->anyFilled(['tgl_diusulkan_mulai', 'tambahsdm_status', 'tgl_diusulkan_sampai', 'tambahsdm_penempatan', 'tambahsdm_laju', 'posisi']) ? 'open' : '' }}>
                
                <summary class="cari">
                    <div class="isian gspan-4">
                        <input type="text" id="kata_kunci_permintaan_tambah_sdm" name="kata_kunci" value="{{ $rekRangka->kata_kunci }}" aria-label="Cari Kata Kunci">

                        <button id="tombol_cari_permintaan_sdm" class="cari-cepat" type="submit" title="Cari Data">
                            <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#cari' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                            </svg>
                        </button>
                    </div>
                </summary>

                <div class="kartu form">
                    <div class="isian pendek">
                        <label for="sdm_permintaan_tambah_cariLokasi">Saring Penempatan</label>
                        
                        <select id="sdm_permintaan_tambah_cariLokasi" name="tambahsdm_penempatan[]" class="pil-cari" multiple>
                            @foreach ($lokasis as $lokasi)
                            <option @selected(in_array($lokasi->atur_butir, (array) $rekRangka->tambahsdm_penempatan)) @class(['merah' => $lokasi->atur_status == 'NON-AKTIF'])>{{ $lokasi->atur_butir }}</option>
                            @endforeach
                        </select>

                        <span class="t-bantu">Pilih satu atau lebih</span>
                    </div>

                    <div class="isian pendek">
                        <label for="sdm_permintaan_tambah_cariPemenuhan">Saring Pemenuhan</label>
                        
                        <select id="sdm_permintaan_tambah_cariPemenuhan" name="tambahsdm_laju" class="pil-cari">
                            <option selected disabled></option>
                            <option @selected($rekRangka->tambahsdm_laju == 'BELUM TERPENUHI')>BELUM TERPENUHI</option>
                            <option @selected($rekRangka->tambahsdm_laju == 'SUDAH TERPENUHI')>SUDAH TERPENUHI</option>
                            <option @selected($rekRangka->tambahsdm_laju == 'KELEBIHAN')>KELEBIHAN</option>
                        </select>

                        <span class="t-bantu">Pilih satu atau lebih</span>
                    </div>

                    <div class="isian pendek">
                        <label for="sdm_permintaan_tambah_cariTanggalUsulMulai">Tanggal Diusulkan Mulai</label>
                        
                        <input id="sdm_permintaan_tambah_cariTanggalUsulMulai" type="date" name="tgl_diusulkan_mulai" value="{{ $rekRangka->old('tgl_diusulkan_mulai', $rekRangka->tgl_diusulkan_mulai ?? null) }}">

                        <span class="t-bantu">Isi tanggal</span>
                    </div>

                    <div class="isian pendek">
                        <label for="sdm_permintaan_tambah_cariTanggalUsulSampai">Tanggal Diusulkan Sampai</label>
                        
                        <input id="sdm_permintaan_tambah_cariTanggalUsulSampai" type="date" name="tgl_diusulkan_sampai" value="{{ $rekRangka->old('tgl_diusulkan_sampai', $rekRangka->tgl_diusulkan_sampai ?? null) }}">
                        
                        <span class="t-bantu">Isi tanggal</span>
                    </div>

                    <div class="isian panjang">
                        <label for="sdm_permintaan_tambah_cariStatusJabatanSDM">Saring Jabatan</label>
                        
                        <select id="sdm_permintaan_tambah_cariStatusJabatanSDM" name="posisi[]" class="pil-cari" multiple>
                            @foreach ($posisis as $posisi)
                            <option @selected(in_array($posisi->posisi_nama, (array) $rekRangka->posisi)) @class(['merah' => $posisi->posisi_status == 'NON-AKTIF'])>{{ $posisi->posisi_nama }}</option>                            
                            @endforeach
                        </select>

                        <span class="t-bantu">Pilih satu atau lebih</span>
                    </div>

                    <div class="isian pendek">
                        <label for="sdm_permintaan_tambah_cariStatus">Saring Status</label>
                        
                        <select id="sdm_permintaan_tambah_cariStatus" name="tambahsdm_status[]" class="pil-dasar" multiple>
                            @foreach ($statuses as $status)
                            <option @selected(in_array($status->atur_butir, (array) $rekRangka->tambahsdm_status))>{{ $status->atur_butir }}</option>
                            @endforeach
                        </select>

                        <span class="t-bantu">Pilih satu atau lebih</span>
                    </div>
                </div>                
            </details>
        </form>
    </div>
    
    <div id="tambah_sdm_tabels" class="kartu scroll-margin">
        @fragment('tambah_sdm_tabels')
        <b><i>Total permintaan tambah SDM ({{ $rekRangka->anyFilled(['kata_kunci', 'tambahsdm_status', 'tgl_diusulkan_mulai', 'tgl_diusulkan_sampai', 'tambahsdm_penempatan', 'tambahsdm_laju', 'posisi']) ? 'sesuai data pencarian & penyaringan' : 'global' }}) : Kebutuhan = {{number_format($kebutuhan, 0, ',', '.')}} Personil -> Terpenuhi = {{number_format($terpenuhi, 0, ',', '.')}} Personil -> Selisih = {{number_format($selisih, 0, ',', '.')}} Personil.</i></b>
        
        <div id="permintaan-sdm_sematan" class="scroll-margin"></div>

        <div class="trek-data tcetak">
            <span class="bph">
                <label for="sdm_permintaan_tambah_cariPerHalaman" class="ket">Baris per halaman : </label>
                
                <select id="sdm_permintaan_tambah_cariPerHalaman" name="bph" class="pil-saja" form="form_sdm_permintaan_tambah_cari" onchange="getElementById('tombol_cari_permintaan_sdm').click()">
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
                <a class="isi-xhr" data-tujuan="#tambah_sdm_tabels" data-frag="true" href="{{ $tabels->url(1) }}" title="Awal">
                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#awal' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                </a>
                @endif

                @if($tabels->previousPageUrl())
                <a class="isi-xhr" data-tujuan="#tambah_sdm_tabels" data-frag="true" href="{{ $tabels->previousPageUrl() }}" title="Sebelumnya">
                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#mundur' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                </a>
                @endif

                @if($tabels->nextPageUrl())
                <a class="isi-xhr" data-tujuan="#tambah_sdm_tabels" data-frag="true" href="{{ $tabels->nextPageUrl() }}" title="Berikutnya">
                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#maju' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                </a>

                <a class="isi-xhr" data-tujuan="#tambah_sdm_tabels" data-frag="true" href="{{ $tabels->url($tabels->lastPage()) }}" title="Akhir">
                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#akhir' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                </a>
                @endif
            </span>
            @endif

            <details class="gspan-4" {{ $rekRangka->anyFilled('urut') ? 'open' : '' }}>
                <summary>Pengurutan :</summary>

                <div class="kartu form" id="sdm_permintaan_tambah_cariUrut">
                    <div class="isian" data-indeks="{{ $urutNomor ? $indexNomor : 'X' }}">
                        <label for="sdm_permintaan_tambah_cariUrutNomor">{{ $urutNomor ? $indexNomor.'. ' : '' }}Urut Nomor Permintaan</label>
                        
                        <select id="sdm_permintaan_tambah_cariUrutNomor" name="urut[]" form="form_sdm_permintaan_tambah_cari" class="pil-dasar" onchange="getElementById('tombol_cari_permintaan_sdm').click()">
                            <option selected disabled></option>
                            <option @selected(in_array('tambahsdm_no ASC', (array) $rekRangka->urut)) value="tambahsdm_no ASC">0 - 9</option>
                            <option @selected(in_array('tambahsdm_no DESC', (array) $rekRangka->urut)) value="tambahsdm_no DESC">9 - 0</option>
                        </select>

                        <span class="t-bantu">Pilih satu</span>
                    </div>

                    <div class="isian" data-indeks="{{ $urutJumlah ? $indexJumlah : 'X' }}">
                        <label for="sdm_permintaan_tambah_cariUrutJumlah">{{ $urutJumlah ? $indexJumlah.'. ' : '' }}Urut Jumlah Dibutuhkan</label>
                        
                        <select id="sdm_permintaan_tambah_cariUrutJumlah" name="urut[]" form="form_sdm_permintaan_tambah_cari" class="pil-dasar" onchange="getElementById('tombol_cari_permintaan_sdm').click()">
                            <option selected disabled></option>
                            <option @selected(in_array('tambahsdm_jumlah ASC', (array) $rekRangka->urut)) value="tambahsdm_jumlah ASC">0 - 9</option>
                            <option @selected(in_array('tambahsdm_jumlah DESC', (array) $rekRangka->urut)) value="tambahsdm_jumlah DESC">9 - 0</option>
                        </select>
                        
                        <span class="t-bantu">Pilih satu</span>
                    </div>

                    <div class="isian" data-indeks="{{ $urutPosisi ? $indexPosisi : 'X' }}">
                        <label for="sdm_permintaan_tambah_cariUrutPosisi">{{ $urutPosisi ? $indexPosisi.'. ' : '' }}Urut Posisi Dibutuhkan</label>
                        
                        <select id="sdm_permintaan_tambah_cariUrutPosisi" name="urut[]" form="form_sdm_permintaan_tambah_cari" class="pil-dasar" onchange="getElementById('tombol_cari_permintaan_sdm').click()">
                            <option selected disabled></option>
                            <option @selected(in_array('tambahsdm_posisi ASC', (array) $rekRangka->urut)) value="tambahsdm_posisi ASC">A - Z</option>
                            <option @selected(in_array('tambahsdm_posisi DESC', (array) $rekRangka->urut)) value="tambahsdm_posisi DESC">Z - A</option>
                        </select>
                        
                        <span class="t-bantu">Pilih satu</span>
                    </div>

                    <div class="isian" data-indeks="{{ $urutPenempatan ? $indexPenempatan : 'X' }}">
                        <label for="sdm_permintaan_tambah_cariUrutPenempatan">{{ $urutPenempatan ? $indexPenempatan.'. ' : '' }}Urut Penempatan SDM</label>
                        
                        <select id="sdm_permintaan_tambah_cariUrutPenempatan" name="urut[]" form="form_sdm_permintaan_tambah_cari" class="pil-dasar" onchange="getElementById('tombol_cari_permintaan_sdm').click()">
                            <option selected disabled></option>
                            <option @selected(in_array('tambahsdm_penempatan ASC', (array) $rekRangka->urut)) value="tambahsdm_penempatan ASC">A - Z</option>
                            <option @selected(in_array('tambahsdm_penempatan DESC', (array) $rekRangka->urut)) value="tambahsdm_penempatan DESC">Z - A</option>
                        </select>
                        
                        <span class="t-bantu">Pilih satu</span>
                    </div>
                </div>
            </details>
        </div>

        <span class="merah">Merah</span> : Kelebihan. <span class="biru">Biru</span> : Sudah Terpenuhi.

        <div class="data ringkas">
            <table id="permintaan-sdm_tabel" class="tabel">
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
                    <tr @class(['merah' => $tabel->tambahsdm_jumlah < $tabel->tambahsdm_terpenuhi, 'biru' => $tabel->tambahsdm_jumlah == $tabel->tambahsdm_terpenuhi])>
                        <th>
                            <div class="pil-aksi">
                                <button id="{{'aksi_tsdm_baris_' . $tabels->firstItem() + $nomor}}" title="Pilih Tindakan">
                                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#menuvert' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                                    </svg>
                                </button>

                                <div class="aksi">
                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#permintaan-sdm_sematan" href="{{ $urlRangka->route('sdm.permintaan-tambah-sdm.lihat', ['uuid' => $tabel->tambahsdm_uuid], false) }}" title="Lihat Data">Lihat Data</a>
                                    
                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#permintaan-sdm_sematan" href="{{ $urlRangka->route('sdm.permintaan-tambah-sdm.ubah', ['uuid' => $tabel->tambahsdm_uuid], false) }}" title="Ubah Data">Ubah Data</a>
                                </div>
                            </div>
                        </th>

                        <td>{{$tabels->firstItem() + $nomor}}</td>

                        <td>
                            <a class="isi-xhr taut-akun" href="{{ $urlRangka->route('akun', ['uuid' => $tabel->sdm_uuid], false) }}">
                                <img @class(['akun', 'svg' => !$storageRangka->exists('sdm/foto-profil/' . $tabel->tambahsdm_sdm_id . '.webp')]) src="{{ $storageRangka->exists('sdm/foto-profil/' . $tabel->tambahsdm_sdm_id . '.webp') ? $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' => $tabel->tambahsdm_sdm_id . '.webp' . '?' . filemtime($appRangka->storagePath('app/sdm/foto-profil/' . $tabel->tambahsdm_sdm_id . '.webp'))], false) : $mixRangka('/ikon.svg') . '#akun' }}" alt="{{ $tabel->sdm_nama ?? 'foto akun' }}" title="{{ $tabel->sdm_nama ?? 'foto akun' }}" loading="lazy">
                            </a>
                            <b>Nomor</b> : {{$tabel->tambahsdm_no}}<br/>
                            <b>Pemohon</b> : {{$tabel->tambahsdm_sdm_id}} - {{$tabel->sdm_nama}}<br/>
                            <b>Diusulkan</b> : {{ strtoupper($dateRangka->make($tabel->tambahsdm_tgl_diusulkan)?->translatedFormat('d F Y')) }}<br/>
                            <b>Dibutuhkan</b> : {{ strtoupper($dateRangka->make($tabel->tambahsdm_tgl_dibutuhkan)?->translatedFormat('d F Y')) }}
                        </td>

                        <td>
                            <b>Penempatan</b> : {{$tabel->tambahsdm_penempatan}}<br/>
                            <b>Posisi</b> : {{$tabel->tambahsdm_posisi}}<br/>
                            <b>Jml Kebutuhan</b> : {{$tabel->tambahsdm_jumlah}}<br/>
                            <b>Jml Terpenuhi</b> : <u><a class="isi-xhr" href="{{ $urlRangka->route('sdm.penempatan.riwayat', ['kata_kunci' => $tabel->tambahsdm_no], false) }}">{{ $tabel->tambahsdm_terpenuhi }}</a></u><br/>
                            <b>Pemenuhan Terbaru</b> : {{ strtoupper($dateRangka->make($tabel->pemenuhan_terkini)?->translatedFormat('d F Y')) }}
                        </td>

                        <td>
                            <b>Alasan</b> : {!! nl2br($tabel->tambahsdm_alasan) !!}<br/>
                            <b>Keterangan</b> : {!! nl2br($tabel->tambahsdm_keterangan) !!}
                        </td>

                        <td>
                            <form class="form-xhr" method="POST" action="{{ $urlRangka->route('sdm.permintaan-tambah-sdm.ubah', [ 'uuid' => $tabel->tambahsdm_uuid], false) }}" data-singkat="true">
                                <input type="hidden" name="_token" value="{{ $rekRangka->session()->token() }}">
                                <div class="isian pendek">
                                    <label for="{{'ubah_cepat_status_tsdm_' . $tabels->firstItem() + $nomor}}" class="tcetak">Ubah Status</label>
                                    
                                    <select id="{{'ubah_cepat_status_tsdm_' . $tabels->firstItem() + $nomor}}" name="tambahsdm_status" class="pil-saja" required onchange="getElementById('kirim-{{$tabel->tambahsdm_uuid}}').click()">
                                        <option selected disabled></option>
                                        @if (!in_array($rekRangka->old('tambahsdm_status', $tabel->tambahsdm_status ?? null), (array) $statuses->pluck('atur_butir')->toArray()))
                                            <option value="{{ $rekRangka->old('tambahsdm_status', $tabel->tambahsdm_status ?? null) }}" class="merah" selected>{{ $rekRangka->old('tambahsdm_status', $permin->tambahsdm_status ?? null) }}</option>
                                        @endif
                                        @foreach ($statuses as $status)
                                            <option @selected($status->atur_butir == $rekRangka->old('tambahsdm_status', $tabel->tambahsdm_status ?? null))>{{ $status->atur_butir }}</option>
                                        @endforeach
                                    </select>

                                    <button type="submit" id="kirim-{{ $tabel->tambahsdm_uuid }}" sembunyikan></button>
                                </div>
                            </form>

                            <p class="tcetak">
                                <a class="isi-xhr utama" data-rekam="false" data-laju="true" data-tujuan="#permintaan-sdm_sematan" href="{{ $urlRangka->route('sdm.permintaan-tambah-sdm.formulir', ['uuid' => $tabel->tambahsdm_uuid], false) }}" title="Cetak Formulir Permintaan Tambah SDM">
                                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#cetak' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                                    </svg>
                                    FORMULIR
                                </a>
                            </p>

                            @if ($storageRangka->exists('sdm/permintaan-tambah-sdm/'.$tabel->tambahsdm_no.'.pdf'))
                            <p class="tcetak">
                                <a class="sekunder tcetak" target="_blank" title="Unduh Berkas Terunggah" href="{{ $urlRangka->route('sdm.permintaan-tambah-sdm.berkas', ['berkas' => $tabel->tambahsdm_no . '.pdf' . '?' . filemtime($appRangka->storagePath('app/sdm/permintaan-tambah-sdm/' . $tabel->tambahsdm_no . '.pdf'))], false) }}">
                                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#unduh' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
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

        <button class="sekunder tcetak" onclick="ringkasTabel(this)">Panjang/Pendekkan Tampilan Tabel</button>
        
        <script>
            (async() => {
                while(!window.aplikasiSiap) {
                    await new Promise((resolve,reject) =>
                    setTimeout(resolve, 1000));
                }
                
                pilDasar('#tambah_sdm_tabels .pil-dasar');
                pilSaja('#tambah_sdm_tabels .pil-saja');
                urutData('#sdm_permintaan_tambah_cariUrut','#sdm_permintaan_tambah_cariUrut [data-indeks]');
                formatTabel('#permintaan-sdm_tabel thead th', '#permintaan-sdm_tabel tbody tr');
            })();
        </script>

        @includeWhen($rekRangka->session()->has('spanduk') || $rekRangka->session()->has('pesan') || $errors->any(), 'pemberitahuan')
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

        <a href="#" title="Unduh Data" onclick="event.preventDefault();lemparXHR({tujuan : '#permintaan-sdm_sematan', tautan : window.location.search ? window.location.pathname + window.location.search + '&unduh=excel' : window.location.pathname + '?unduh=excel', strim : true})">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#unduh' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>
        
        <a class="isi-xhr" data-rekam="false" data-tujuan="#permintaan-sdm_sematan" href="{{ $urlRangka->route('sdm.permintaan-tambah-sdm.tambah', [], false) }}" title="Tambah Data">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#tambah' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
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
            
            pilDasar('#form_sdm_permintaan_tambah_cari .pil-dasar');
            pilCari('#form_sdm_permintaan_tambah_cari .pil-cari');
            pilSaja('#form_sdm_permintaan_tambah_cari .pil-saja');
            formatIsian('#form_sdm_permintaan_tambah_cari .isian :is(textarea,input[type=text],input[type=search])');
        })();
    </script>
    @endisset

</div>
@endsection
