@extends('rangka')

@section('isi')
<div id="permintambahsdm">
    <form id="form_permintambahsdm" class="form-xhr kartu" method="POST" action="{{ $urlRangka->current() }}">
        <input type="hidden" name="_token" value="{{ $rekRangka->session()->token() }}">

        <div class="gspan-4">
            <a class="tutup-i"><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><use xlink:href="{{ $mixRangka('/ikon.svg') . '#tutup' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use></svg></a>
            <h4 class="form">{{$rekRangka->routeIs('sdm.permintaan-tambah-sdm.tambah') ? 'Tambah' : 'Ubah'}} Data Permintaan Tambah SDM</h4>
        </div>

        @if ($rekRangka->routeIs('sdm.permintaan-tambah-sdm.ubah'))
        <div class="isian pendek">
            <label for="permintambahsdmNomor">No Permintaan</label>
            <input id="permintambahsdmNomor" type="text" name="tambahsdm_no" value="{{ $rekRangka->old('tambahsdm_no', $permin->tambahsdm_no ?? null) }}" readonly inputmode="numeric" required>
            <span class="t-bantu">Harap tidak diubah</span>
        </div>
        @endif

        <div class="isian normal">
            <label for="permintambahsdmPemohon">Pemohon</label>

            <select id="permintambahsdmPemohon" name="tambahsdm_sdm_id" class="pil-cari" required>
                <option selected></option>
                
                @if (!in_array($rekRangka->old('tambahsdm_sdm_id', $permin->tambahsdm_sdm_id ?? null), $sdms->pluck('sdm_no_absen')->toArray()) && !is_null($rekRangka->old('tambahsdm_sdm_id', $permin->tambahsdm_sdm_id ?? null)))
                <option value="{{ $rekRangka->old('tambahsdm_sdm_id', $permin->tambahsdm_sdm_id ?? null) }}" class="merah" selected>{{ $rekRangka->old('tambahsdm_sdm_id', $permin->tambahsdm_sdm_id ?? null) }}</option>
                @endif

                @foreach ($sdms as $sdm)
                <option @selected($sdm->sdm_no_absen == $rekRangka->old('tambahsdm_sdm_id', $permin->tambahsdm_sdm_id ?? null)) value="{{ $sdm->sdm_no_absen }}" @class(['merah' => $sdm->sdm_tgl_berhenti])>{{ $sdm->sdm_no_absen }} - {{ $sdm->sdm_nama }}</option>
                @endforeach
            </select>

            <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
        </div>

        <div class="isian pendek">
            <label for="permintambahsdmPenempatan">Penempatan Dibutuhkan</label>

            <select id="permintambahsdmPenempatan" name="tambahsdm_penempatan" class="pil-cari" required>
                <option selected></option>
                
                @if (!in_array($rekRangka->old('tambahsdm_penempatan', $permin->tambahsdm_penempatan ?? null), (array) $penempatans->pluck('atur_butir')->toArray()) && !is_null($rekRangka->old('tambahsdm_penempatan', $permin->tambahsdm_penempatan ?? null)))
                <option value="{{ $rekRangka->old('tambahsdm_penempatan', $permin->tambahsdm_penempatan ?? null) }}" class="merah" selected>{{ $rekRangka->old('tambahsdm_penempatan', $permin->tambahsdm_penempatan ?? null) }}</option>
                @endif
                
                @foreach ($penempatans as $penempatan)
                <option @selected($penempatan->atur_butir == $rekRangka->old('tambahsdm_penempatan', $permin->tambahsdm_penempatan ?? null)) @class(['merah' => $penempatan->atur_status == 'NON-AKTIF'])>{{ $penempatan->atur_butir }}</option>
                @endforeach
            </select>

            <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
        </div>

        <div class="isian panjang">
            <label for="permintambahsdmPosisi">Posisi Dibutuhkan</label>

            <select id="permintambahsdmPosisi" name="tambahsdm_posisi" class="pil-cari" required>
                <option selected></option>
                
                @if (!in_array($rekRangka->old('tambahsdm_posisi', $permin->tambahsdm_posisi ?? null), $posisis->pluck('posisi_nama')->toArray()) && !is_null($rekRangka->old('tambahsdm_posisi', $permin->tambahsdm_posisi ?? null)))
                <option value="{{ $rekRangka->old('tambahsdm_posisi', $permin->tambahsdm_posisi ?? null) }}" class="merah" selected>{{ $rekRangka->old('tambahsdm_posisi', $permin->tambahsdm_posisi ?? null) }}</option>
                @endif

                @foreach ($posisis as $posisi)
                <option @selected($posisi->posisi_nama == $rekRangka->old('tambahsdm_posisi', $permin->tambahsdm_posisi ?? null)) @class(['merah' => $posisi->posisi_status == 'NON-AKTIF'])>{{ $posisi->posisi_nama }}</option>
                @endforeach
            </select>

            <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
        </div>

        <div class="isian pendek">
            <label for="permintambahsdmJumlah">Jumlah Dibutuhkan</label>
            
            <input id="permintambahsdmJumlah" type="number" name="tambahsdm_jumlah" value="{{ $rekRangka->old('tambahsdm_jumlah', $permin->tambahsdm_jumlah ?? null) }}" min="0" inputmode="numeric" required>

            <span class="t-bantu">Jumlah SDM yang dibutuhkan</span>
        </div>

        <div class="isian pendek">
            <label for="permintambahsdmTglUsul">Tanggal Diusulkan</label>
            
            <input id="permintambahsdmTglUsul" type="date" name="tambahsdm_tgl_diusulkan" value="{{ $rekRangka->old('tambahsdm_tgl_diusulkan', $permin->tambahsdm_tgl_diusulkan ?? $dateRangka->today()->toDateString()) }}" required>
            
            <span class="t-bantu">Isi tanggal</span>
        </div>

        <div class="isian pendek">
            <label for="permintambahsdmTglButuh">Tanggal Dibutuhkan</label>

            <input id="permintambahsdmTglButuh" type="date" name="tambahsdm_tgl_dibutuhkan" value="{{ $rekRangka->old('tambahsdm_tgl_dibutuhkan', $permin->tambahsdm_tgl_dibutuhkan ?? $dateRangka->today()->addDays(7)->toDateString()) }}" required>

            <span class="t-bantu">Isi tanggal</span>
        </div>

        @if ($rekRangka->routeIs('sdm.permintaan-tambah-sdm.ubah'))
        <div class="isian pendek">
            <label for="permintambahsdmPenempatan">Status Permohonan</label>

            <select id="permintambahsdmPenempatan" name="tambahsdm_status" class="pil-cari">
                <option selected></option>
                
                @if (!in_array($rekRangka->old('tambahsdm_status', $permin->tambahsdm_status ?? null), (array) $statuses->pluck('atur_butir')->toArray()) && !is_null($rekRangka->old('tambahsdm_status', $permin->tambahsdm_status ?? null)))
                <option value="{{ $rekRangka->old('tambahsdm_status', $permin->tambahsdm_status ?? null) }}" class="merah" selected>{{ $rekRangka->old('tambahsdm_status', $permin->tambahsdm_status ?? null) }}</option>
                @endif

                @foreach ($statuses as $status)
                <option {{ ($rekRangka->old('tambahsdm_status', $permin->tambahsdm_status ?? null) == $status->atur_butir) ? 'selected' : (!$rekRangka->old('tambahsdm_status', $permin->tambahsdm_status ?? null) && $status->atur_butir == 'DIUSULKAN' ? 'selected' : '') }}>{{ $status->atur_butir }}</option>
                @endforeach
            </select>

            <span class="t-bantu">Pilih satu</span>
        </div>
        @endif

        <div class="isian gspan-4">
            <label for="permintambahsdmAlasan">Alasan</label>
            
            <textarea id="permintambahsdmAlasan" name="tambahsdm_alasan" rows="5" required>{{ $rekRangka->old('tambahsdm_alasan', $permin->tambahsdm_alasan ?? null) }}</textarea>

            <span class="t-bantu">Isi alasan permintaan</span>
        </div>

        <div class="isian gspan-4">
            <label for="permintambahsdmKeterangan">Keterangan</label>
            
            <textarea id="permintambahsdmKeterangan" name="tambahsdm_keterangan" rows="3">{{ $rekRangka->old('tambahsdm_keterangan', $permin->tambahsdm_keterangan ?? null) }}</textarea>

            <span class="t-bantu">Isi catatan detail permintaan</span>
        </div>
        
        @if ($rekRangka->routeIs('sdm.permintaan-tambah-sdm.ubah'))
        <div class="isian gspan-4">
            <label>Berkas Permohonan</label>

            @if ($storageRangka->exists('sdm/permintaan-tambah-sdm/'.$rekRangka->old('tambahsdm_no', $permin->tambahsdm_no ?? null).'.pdf'))
            <iframe class="berkas tcetak" src="{{ $urlRangka->route('sdm.permintaan-tambah-sdm.berkas', ['berkas' => $rekRangka->old('tambahsdm_no', $permin->tambahsdm_no ?? null) . '.pdf' . '?' . filemtime($appRangka->storagePath('app/sdm/permintaan-tambah-sdm/' . $rekRangka->old('tambahsdm_no', $permin->tambahsdm_no ?? null) . '.pdf'))], false) }}" title="Berkas Permintaan SDM" loading="lazy"></iframe>
            
            <a class="sekunder tcetak" target="_blank" title="Unduh Berkas Terunggah" href="{{ $urlRangka->route('sdm.permintaan-tambah-sdm.berkas', ['berkas' => $rekRangka->old('tambahsdm_no', $permin->tambahsdm_no ?? null) . '.pdf' . '?' . filemtime($appRangka->storagePath('app/sdm/permintaan-tambah-sdm/' . $rekRangka->old('tambahsdm_no', $permin->tambahsdm_no ?? null) . '.pdf'))], false) }}">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ $mixRangka('/ikon.svg') . '#unduh' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg>
                BERKAS
            </a>

            @else
            <p class="merah">Tidak ada berkas terunggah.</p>
            @endif
        </div>
        @endif

        <div class="isian pendek">
            <label for="permintambahsdmUnggah">Unggah Berkas</label>
            
            <input id="permintambahsdmUnggah" type="file" name="tambahsdm_berkas" accept=".pdf,application/pdf">
            
            <span class="t-bantu">Format PDF {{ $storageRangka->exists('sdm/permintaan-tambah-sdm/'.$rekRangka->old('tambahsdm_no', $permin->tambahsdm_no ?? null).'.pdf') ? '(berkas yang diunggah akan menindih berkas unggahan lama).' : '' }}</span>
        </div>

        <div class="gspan-4"></div>

        <button class="utama pelengkap" type="submit">SIMPAN</button>
    </form>

    <script>
        (async() => {
            while(!window.aplikasiSiap) {
                await new Promise((resolve,reject) =>
                setTimeout(resolve, 1000));
            }
            
            pilSaja('#form_permintambahsdm .pil-saja');
            pilCari('#form_permintambahsdm .pil-cari');
            formatIsian('#form_permintambahsdm .isian :is(textarea,input[type=text],input[type=search])');
        })();
    </script>

    @includeWhen($rekRangka->session()->has('spanduk') || $rekRangka->session()->has('pesan') || $errors->any(), 'pemberitahuan')
    @include('komponen')
</div>
@endsection
