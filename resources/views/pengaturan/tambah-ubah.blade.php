@extends('rangka')

@section('isi')
    <div id="atur_tambahUbah">
        <form class="form-xhr kartu" id="form_atur_tambahUbah" method="POST" action="{{ $app->url->current() }}">
            <input name="_token" type="hidden" value="{{ $app->request->session()->token() }}">

            <div class="judul-form gspan-4">
                <h4 class="form">{{ $app->request->routeIs('atur.tambah') ? 'Tambah' : 'Ubah' }} Data Pengaturan Umum</h4>

                <a class="tutup-i">
                    <svg viewbox="0 0 24 24">
                        <use href="#ikontutup"></use>
                    </svg>
                </a>
            </div>

            <div class="isian">
                <label for="atur_tambahUbahJenis">Jenis Pengaturan</label>

                <input id="atur_tambahUbahJenis" name="atur_jenis" type="text"
                    value="{{ $app->request->old('atur_jenis', $atur->atur_jenis ?? null) }}" maxlength="20" required>

                <span class="t-bantu">Isi kelompok pengaturan</span>
            </div>

            <div class="isian">
                <label for="atur_tambahUbahButir">Butir Pengaturan</label>

                <input id="atur_tambahUbahButir" name="atur_butir" type="text"
                    value="{{ $app->request->old('atur_butir', $atur->atur_butir ?? null) }}" maxlength="40" required>

                <span class="t-bantu">Isi butir aturan dari kelompok pengaturan di atas</span>
            </div>

            <div class="isian">
                <label for="atur_tambahUbahStatus">Status</label>

                <select class="pil-saja" id="atur_tambahUbahStatus" name="atur_status" required>
                    <option default @selected($app->request->old('atur_butir', $atur->atur_status ?? null) == 'AKTIF')>
                        AKTIF
                    </option>

                    <option @selected($app->request->old('atur_butir', $atur->atur_status ?? null) == 'NON-AKTIF')>
                        NON-AKTIF
                    </option>
                </select>

                <span class="t-bantu">Pilih satu</span>
            </div>

            <div class="isian gspan-4">
                <label for="atur_tambahUbahKeterangan">Keterangan</label>

                <textarea id="atur_tambahUbahKeterangan" name="atur_detail" cols="3">
                {{ $app->request->old('atur_detail', $atur->atur_detail ?? null) }}
            </textarea>

                <span class="t-bantu">Isi catatan detail aturan</span>
            </div>

            <div class="gspan-4"></div>

            <button class="utama pelengkap" type="submit">SIMPAN</button>
        </form>

        <script>
            (async () => {
                while (!window.aplikasiSiap) {
                    await new Promise((resolve, reject) =>
                        setTimeout(resolve, 1000));
                }

                pilSaja('#form_atur_tambahUbah .pil-saja');
                formatIsian('#form_atur_tambahUbah .isian :is(textarea,input[type=text],input[type=search])');
            })();
        </script>

        @include('pemberitahuan')
        @include('komponen')
    </div>
@endsection
