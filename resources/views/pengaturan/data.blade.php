@extends('rangka')

@section('isi')
<div id="atur_data">
    <p class="tcetak kartu">
        <svg fill="var(--taut-umum)" viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#informasi' }}"
                xmlns:xlink="http://www.w3.org/1999/xlink">
            </use>
        </svg>
        Data pengaturan ini digunakan hanya sebagai alat bantu validasi pengisian data lain dalam aplikasi. Segala
        bentuk perubahan dalam data pengaturan ini tidak akan berdampak pada data lainnya, Anda harus mengubah data
        lainnya secara manual.
    </p>

    @isset($tabels)
    <div class="cari-data tcetak">
        <form id="form_atur_data_cari" class="form-xhr kartu" data-tujuan="#atur_tabels" data-frag="true" method="GET"
            action="{{ $urlRangka->current() }}">

            <input type="hidden" name="fragment" value="atur_tabels">

            <details class="gspan-4" {{ $rekRangka->anyFilled(['atur_jenis', 'atur_butir', 'atur_status']) ? 'open' : ''
                }}>

                <summary class="cari">
                    <div class="isian gspan-4">
                        <input type="text" id="kata_kunci_pengaturan" name="kata_kunci"
                            value="{{ $rekRangka->kata_kunci }}" aria-label="Cari Kata Kunci">

                        <button id="tombol_cari_atur" class="cari-cepat" type="submit" title="Cari Data">
                            <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#cari' }}"
                                    xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                            </svg>
                        </button>
                    </div>
                </summary>

                <div class="kartu form gspan-4">

                    <div class="isian">
                        <label for="atur_data_cariStatus">Saring Status</label>

                        <select id="atur_data_cariStatus" name="atur_status[]" class="pil-dasar" multiple
                            onchange="getElementById('tombol_cari_atur').click()">
                            @foreach ($statuses as $status)
                            <option @selected(in_array($status, (array) $rekRangka->atur_status))>{{ $status }}</option>
                            @endforeach
                        </select>

                        <span class="t-bantu">Pilih satu atau lebih</span>
                    </div>

                    <div class="isian">
                        <label for="atur_data_cariJenis">1 Saring Jenis</label>

                        <select id="atur_data_cariJenis" name="atur_jenis[]" class="pil-cari"
                            @disabled($jenises->count() < 1) multiple
                                onchange="getElementById('tombol_cari_atur').click()">
                                @foreach ($jenises as $jenis)
                                <option @selected(in_array($jenis, (array) $rekRangka->atur_jenis))>{{ $jenis }}
                                </option>
                                @endforeach
                        </select>

                        <span class="t-bantu">Pilih satu atau lebih</span>
                    </div>

                    <div class="isian">
                        <label for="atur_data_cariButir">2 Saring Butir</label>

                        <select id="atur_data_cariButir" name="atur_butir[]" class="pil-cari" @disabled($butirs->count()
                            < 1) multiple onchange="getElementById('tombol_cari_atur').click()">
                                @foreach ($butirs as $butir)
                                <option @selected(in_array($butir, (array) $rekRangka->atur_butir))>{{ $butir }}
                                </option>
                                @endforeach
                        </select>

                        <span class="t-bantu">Pilih satu atau lebih</span>
                    </div>
                </div>
            </details>
        </form>
    </div>

    <div id="atur_tabels" class="kartu scroll-margin">
        @fragment('atur_tabels')
        <div id="atur_sematan" class="scroll-margin"></div>

        <div class="trek-data tcetak">
            <span class="bph">
                <label for="atur_data_cariPerHalaman">Baris per halaman : </label>

                <select id="atur_data_cariPerHalaman" name="bph" class="pil-saja" form="form_atur_data_cari"
                    onchange="getElementById('tombol_cari_atur').click()">
                    <option>25</option>
                    <option @selected($tabels->perPage() == 50)>50</option>
                    <option @selected($tabels->perPage() == 75)>75</option>
                    <option @selected($tabels->perPage() == 100)>100</option>
                </select>
            </span>

            <span class="ket">{{number_format($tabels->firstItem(), 0, ',', '.')}} -
                {{number_format($tabels->lastItem(), 0, ',', '.')}} dari {{number_format($tabels->total(), 0, ',',
                '.')}} data</span>

            @if($tabels->hasPages())
            <span class="trek">
                @if($tabels->currentPage() > 1)
                <a class="isi-xhr" href="{{ $tabels->url(1) }}" data-tujuan="#atur_tabels" data-frag="true"
                    title="Awal">
                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#awal' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                </a>
                @endif

                @if($tabels->previousPageUrl())
                <a class="isi-xhr" href="{{ $tabels->previousPageUrl() }}" data-tujuan="#atur_tabels" data-frag="true"
                    title="Sebelumnya">
                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#mundur' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                </a>
                @endif

                @if($tabels->nextPageUrl())
                <a class="isi-xhr" href="{{ $tabels->nextPageUrl() }}" data-tujuan="#atur_tabels" data-frag="true"
                    title="Berikutnya">
                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#maju' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                </a>

                <a class="isi-xhr" href="{{ $tabels->url($tabels->lastPage()) }}" data-tujuan="#atur_tabels"
                    data-frag="true" title="Akhir">
                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#akhir' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                </a>
                @endif
            </span>
            @endif

            <details class="gspan-4" {{ $rekRangka->anyFilled('urut') ? 'open' : '' }}>
                <summary>Pengurutan :</summary>

                <div class="kartu form" id="atur_data_cariUrut">
                    <div class="isian" data-indeks="{{ $urutJenis ? $indexJenis : 'X' }}">
                        <label for="atur_data_cariUrutJenis">{{ $urutJenis ? $indexJenis.'. ' : '' }}Urut Jenis</label>

                        <select id="atur_data_cariUrutJenis" name="urut[]" class="pil-dasar" form="form_atur_data_cari"
                            onchange="getElementById('tombol_cari_atur').click()">
                            <option selected disabled></option>
                            <option @selected(in_array('atur_jenis ASC', (array) $rekRangka->urut))
                                value="atur_jenis ASC">A - Z</option>
                            <option @selected(in_array('atur_jenis DESC', (array) $rekRangka->urut))
                                value="atur_jenis DESC">Z - A</option>
                        </select>

                        <span class="t-bantu">Pilih satu</span>
                    </div>

                    <div class="isian" data-indeks="{{ $urutButir ? $indexButir : 'X' }}">
                        <label for="atur_data_cariUrutButir">{{ $urutButir ? $indexButir.'. ' : '' }}Urut Butir</label>

                        <select id="atur_data_cariUrutButir" name="urut[]" class="pil-dasar" form="form_atur_data_cari"
                            onchange="getElementById('tombol_cari_atur').click()">
                            <option selected disabled></option>
                            <option @selected(in_array('atur_butir ASC', (array) $rekRangka->urut))
                                value="atur_butir ASC">A - Z</option>
                            <option @selected(in_array('atur_butir DESC', (array) $rekRangka->urut))
                                value="atur_butir DESC">Z - A</option>
                        </select>

                        <span class="t-bantu">Pilih satu</span>
                    </div>

                    <div class="isian" data-indeks="{{ $urutStatus ? $indexStatus : 'X' }}">
                        <label for="atur_data_cariUrutStatus">{{ $urutStatus ? $indexStatus.'. ' : '' }}Urut
                            Status</label>

                        <select id="atur_data_cariUrutStatus" name="urut[]" class="pil-dasar" form="form_atur_data_cari"
                            onchange="getElementById('tombol_cari_atur').click()">
                            <option selected disabled></option>
                            <option @selected(in_array('atur_status ASC', (array) $rekRangka->urut))
                                value="atur_status ASC">A - Z</option>
                            <option @selected(in_array('atur_status DESC', (array) $rekRangka->urut))
                                value="atur_status DESC">Z - A</option>
                        </select>

                        <span class="t-bantu">Pilih satu</span>
                    </div>
                </div>
            </details>
        </div>

        <div class="data ringkas">
            <table id="atur_tabel" class="tabel">
                <thead>
                    <tr>
                        <th></th>
                        <th>No</th>
                        <th>Jenis</th>
                        <th>Butir</th>
                        <th>Keterangan</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($tabels as $nomor => $tabel)
                    <tr @class(['merah'=> $tabel->atur_status == 'NON-AKTIF'])>
                        <th>
                            <div class="pil-aksi">
                                <button id="{{'aksi_penempatan_baris_' . $tabels->firstItem() + $nomor }}"
                                    title="Pilih Tindakan">
                                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#menuvert' }}"
                                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                                    </svg>
                                </button>

                                <div class="aksi">
                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#atur_sematan"
                                        href="{{ $urlRangka->route('atur.lihat', ['uuid' => $tabel->atur_uuid]) }}"
                                        title="Lihat Data">
                                        Lihat Data
                                    </a>

                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#atur_sematan"
                                        href="{{ $urlRangka->route('atur.ubah', ['uuid' => $tabel->atur_uuid]) }}"
                                        title="Ubah Data">
                                        Ubah Data
                                    </a>

                                    {{-- <a class="isi-xhr" data-rekam="false" data-metode="POST" data-enkode="true"
                                        data-kirim="{{ '_token='.csrf_token() }}"
                                        href="{{ $urlRangka->route('atur.nonaktifkan', ['uuid' => $atur->atur_uuid]) }}">Non-aktifkan</a>
                                    --}}
                                </div>
                            </div>
                        </th>
                        <td>{{$tabels->firstItem() + $nomor}}</td>
                        <td>{{$tabel->atur_jenis}}</td>
                        <td>{{$tabel->atur_butir}}</td>
                        <td>{!! nl2br($tabel->atur_detail) !!}</td>
                        <td>{{$tabel->atur_status}}</td>
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
            (async () => {
                while (!window.aplikasiSiap) {
                    await new Promise((resolve, reject) =>
                        setTimeout(resolve, 1000));
                }

                pilDasar('.trek-data .pil-dasar');
                pilSaja('.trek-data .pil-saja');
                urutData('#atur_data_cariUrut', '#atur_data_cariUrut [data-indeks]');
                formatTabel('#atur_tabel thead th', '#atur_tabel tbody tr');
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

        <a class="isi-xhr" data-rekam="false" data-tujuan="#atur_sematan" href="{{ $urlRangka->route('atur.unggah') }}"
            title="Unggah Data">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#unggah' }}"
                    xmlns:xlink="http://www.w3.org/1999/xlink">
                </use>
            </svg>
        </a>

        <a href="#" title="Unduh Data"
            onclick="event.preventDefault();lemparXHR({tujuan : '#atur_sematan', tautan : window.location.search ? window.location.pathname + window.location.search + '&unduh=excel' : window.location.pathname + '?unduh=excel', strim : true})">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#unduh' }}"
                    xmlns:xlink="http://www.w3.org/1999/xlink">
                </use>
            </svg>
        </a>

        <a class="isi-xhr" data-rekam="false" data-tujuan="#atur_sematan" href="{{ $urlRangka->route('atur.tambah') }}"
            title="Tambah Data">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#tambah' }}"
                    xmlns:xlink="http://www.w3.org/1999/xlink">
                </use>
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

            pilDasar('#form_atur_data_cari .pil-dasar');
            pilCari('#form_atur_data_cari .pil-cari');
            pilSaja('#form_atur_data_cari .pil-saja');
            formatIsian('#form_atur_data_cari .isian :is(textarea,input[type=text],input[type=search])');
        })();
    </script>
    @endisset
</div>
@endsection