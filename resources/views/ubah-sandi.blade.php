@extends('rangka')

@section('isi')
    <div id="ubahSandi">
        <form class="form-xhr kartu tcetak" method="POST" action="{{ $app->url->route('sdm.ubah-sandi') }}">
            <input name="_token" type="hidden" value="{{ $app->request->session()->token() }}">

            <div class="isian gspan-4">
                <h4 class="form">Ubah Sandi Keamanan</h4>
            </div>

            <div class="isian normal">
                <label for="password_lama">Sandi Saat Ini</label>

                <input id="password_lama" name="password_lama" type="password" required>

                <span class="t-bantu">Isi kata sandi saat ini</span>
            </div>

            <div class="isian normal">
                <label for="password">Sandi Baru</label>

                <input id="password" name="password" type="password"
                    title="Minimal 8 karakter terdiri dari 1 huruf besar, 1 huruf kecil, 1 angka dan 1 simbol" required
                    pattern="^(?=[^A-Z\s]*[A-Z])(?=[^a-z\s]*[a-z])(?=[^\d\s]*\d)(?=\w*[\W_])\S{8,}$">

                <span class="t-bantu">
                    Minimal 8 karakter terdiri dari 1 huruf besar, 1 huruf kecil, 1 angka dan 1 simbol
                </span>
            </div>

            <div class="isian normal">
                <label for="password_confirmation">Konfirmasi Sandi Baru</label>

                <input id="password_confirmation" name="password_confirmation" type="password"
                    title="Minimal 8 karakter terdiri dari 1 huruf besar, 1 huruf kecil, 1 angka dan 1 simbol" required
                    pattern="^(?=[^A-Z\s]*[A-Z])(?=[^a-z\s]*[a-z])(?=[^\d\s]*\d)(?=\w*[\W_])\S{8,}$">

                <span class="t-bantu">Wajib sama dengan sandi baru</span>
            </div>

            <div class="gspan-4"></div>

            <button class="utama pelengkap" type="submit">SIMPAN</button>
        </form>

        @include('pemberitahuan')
        @include('komponen')
    </div>
@endsection
