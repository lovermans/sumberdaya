@extends('rangka')

@section('isi')
<div id="atur_tambahUbah">
    <form id="form_atur_tambahUbah" class="form-xhr kartu" data-tujuan="#atur_sematan" method="POST" action="{{ $urlRangka->current() }}">
        <input type="hidden" name="_token" value="{{ $rekRangka->session()->token() }}">
        <div class="gspan-4">
            <a class="tutup-i"><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#tutup' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use></svg></a>
            <h4 class="form">{{$rekRangka->routeIs('atur.tambah') ? 'Tambah' : 'Ubah'}} Data Pengaturan Umum</h4>
        </div>
        <div class="isian">
            <label for="atur_tambahUbahJenis">Jenis Pengaturan</label>
            <input id="atur_tambahUbahJenis" type="text" name="atur_jenis" value="{{ $rekRangka->old('atur_jenis', $atur->atur_jenis ?? null) }}" maxlength="20" required>
            <span class="t-bantu">Isi kelompok pengaturan</span>
        </div>
        <div class="isian">
            <label for="atur_tambahUbahButir">Butir Pengaturan</label>
            <input id="atur_tambahUbahButir" type="text" name="atur_butir" value="{{ $rekRangka->old('atur_butir', $atur->atur_butir ?? null) }}" maxlength="40" required>
            <span class="t-bantu">Isi butir aturan dari kelompok pengaturan di atas</span>
        </div>
        <div class="isian">
            <label for="atur_tambahUbahStatus">Status</label>
            <select id="atur_tambahUbahStatus" name="atur_status" class="pil-saja" required>
                <option default @selected($rekRangka->old('atur_butir', $atur->atur_status ?? null) == 'AKTIF')>AKTIF</option>
                <option @selected($rekRangka->old('atur_butir', $atur->atur_status ?? null) == 'NON-AKTIF')>NON-AKTIF</option>
            </select>
            <span class="t-bantu">Pilih satu</span>
        </div>
        <div class="isian gspan-4">
            <label for="atur_tambahUbahKeterangan">Keterangan</label>
            <textarea id="atur_tambahUbahKeterangan" name="atur_detail" cols="3">{{ $rekRangka->old('atur_detail', $atur->atur_detail ?? null) }}</textarea>
            <span class="t-bantu">Isi catatan detail aturan</span>
        </div>
        <div class="gspan-4"></div>
        <button class="utama pelengkap" type="submit">SIMPAN</button>
    </form>

    <script>
        pilSaja('#form_atur_tambahUbah .pil-saja');
        formatIsian('#form_atur_tambahUbah .isian :is(textarea,input[type=text],input[type=search])');
    </script>

    @includeWhen($rekRangka->session()->has('spanduk') || $rekRangka->session()->has('pesan') || $errors->any(), 'pemberitahuan')

</div>
@endsection
