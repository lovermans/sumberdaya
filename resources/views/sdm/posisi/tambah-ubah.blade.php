@extends('rangka')

@section('isi')
<div id="sdm_posisi_tambahUbah">
    <form id="form_sdm_posisi_tambahUbah" class="form-xhr kartu" method="POST" action="{{ $urlRangka->current() }}">    
        <input type="hidden" name="_token" value="{{ $rekRangka->session()->token() }}">

        <div class="gspan-4">
            <a class="tutup-i"><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><use xlink:href="{{ $mixRangka('/ikon.svg') . '#tutup' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use></svg></a>
            <h4 class="form">{{$rekRangka->routeIs('sdm.posisi.tambah') ? 'Tambah' : 'Ubah'}} Data Pengaturan Jabatan</h4>
        </div>

        <div class="isian normal">
            <label for="sdm_posisi_tambahUbahNama">Nama Jabatan</label>
            <input id="sdm_posisi_tambahUbahNama" name="posisi_nama" value="{{ $rekRangka->old('posisi_nama', $pos->posisi_nama ?? null) }}" maxlength="40" type="text" required>
            <span class="t-bantu">Maks 40 karakter</span>
        </div>

        <div class="isian pendek">
            <label for="sdm_posisi_tambahUbahNama">Kode Jabatan WLKP</label>
            <input id="sdm_posisi_tambahUbahNama" name="posisi_wlkp" value="{{ $rekRangka->old('posisi_wlkp', $pos->posisi_wlkp ?? null) }}" maxlength="40" type="text">
            <span class="t-bantu">Maks 40 karakter</span>
        </div>

        <div class="isian gspan-4">
            <label for="sdm_posisi_tambahUbahKeterangan">Keterangan</label>
            <textarea id="sdm_posisi_tambahUbahKeterangan" name="posisi_keterangan" rows="3">{{ $rekRangka->old('posisi_keterangan', $pos->posisi_keterangan ?? null) }}</textarea>
            <span class="t-bantu">Isi keterangan jabatan</span>
        </div>

        <div class="isian normal">
            <label for="sdm_posisi_tambahUbahAtasan">Nama Jabatan Atasan</label>
            <select id="sdm_posisi_tambahUbahAtasan" name="posisi_atasan" class="pil-cari">
                <option selected></option>
                
                @if (!in_array($rekRangka->old('posisi_atasan', $pos->posisi_atasan ?? null), $posisis->pluck('posisi_nama')->toArray()) && !is_null($rekRangka->old('posisi_atasan', $pos->posisi_atasan ?? null)))
                <option value="{{ $rekRangka->old('posisi_atasan', $pos->posisi_atasan ?? null) }}" class="merah" selected>{{ $rekRangka->old('posisi_atasan', $pos->posisi_atasan ?? null) }}</option>
                @endif

                @foreach ($posisis as $posisi)
                <option @selected($posisi->posisi_nama == $rekRangka->old('posisi_atasan', $pos->posisi_atasan ?? null)) @class(['merah' => $posisi->posisi_status == 'NON-AKTIF'])>{{ $posisi->posisi_nama }}</option>
                @endforeach
            </select>
            <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
        </div>

        <div class="isian pendek">
            <label for="sdm_posisi_tambahUbahStatus">Status</label>
            <select id="sdm_posisi_tambahUbahStatus" name="posisi_status" class="pil-saja" required>
                <option default @selected($rekRangka->old('posisi_status', $pos->posisi_status ?? null) == 'AKTIF')>AKTIF</option>
                <option @selected($rekRangka->old('posisi_status', $pos->posisi_status ?? null) == 'NON-AKTIF')>NON-AKTIF</option>
            </select>
            <span class="t-bantu">Pilih satu</span>
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
            
            pilSaja('#form_sdm_posisi_tambahUbah .pil-saja');
            pilCari('#form_sdm_posisi_tambahUbah .pil-cari');
            formatIsian('#form_sdm_posisi_tambahUbah .isian :is(textarea,input[type=text],input[type=search])');
        })();
    </script>

    @includeWhen($rekRangka->session()->has('spanduk') || $rekRangka->session()->has('pesan') || $errors->any(), 'pemberitahuan')
    @include('komponen')
</div>
@endsection
