@extends('rangka')

@section('isi')
    <form class="form-xhr kartu tcetak" method="POST" action="{{ route('ubah-sandi') }}">
        @csrf
        <div class="isian">
            <label for="password_lama">Sandi Saat Ini</label>
            <input id="password_lama" type="password" name="password_lama" required>
            <span class="t-bantu">Isi kata sandi saat ini</span>
        </div>
        <div class="isian">
            <label for="password">Sandi Baru</label>
            <input id="password" type="password" name="password" required
                pattern="^(?=[^A-Z\s]*[A-Z])(?=[^a-z\s]*[a-z])(?=[^\d\s]*\d)(?=\w*[\W_])\S{8,}$"
                title="Minimal 8 karakter terdiri dari 1 huruf besar, 1 huruf kecil, 1 angka dan 1 simbol">
            <span class="t-bantu">Minimal 8 karakter terdiri dari 1 huruf besar, 1 huruf kecil, 1 angka dan 1
                simbol</span>
        </div>
        <div class="isian">
            <label for="password_confirmation">Konfirmasi Sandi Baru</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                pattern="^(?=[^A-Z\s]*[A-Z])(?=[^a-z\s]*[a-z])(?=[^\d\s]*\d)(?=\w*[\W_])\S{8,}$"
                title="Minimal 8 karakter terdiri dari 1 huruf besar, 1 huruf kecil, 1 angka dan 1 simbol">
            <span class="t-bantu">Wajib sama dengan sandi baru</span>
        </div>
        <button class="utama gspan-4" type="submit">SIMPAN</button>
    </form>
    @includeWhen(session()->has('spanduk') || session()->has('pesan') || $errors->any(), 'pemberitahuan')
@endsection
