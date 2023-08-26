@if (!$app->request->user())
<div class="mini-aside">
    <form id="form-masuk" class="form-xhr kartu" method="POST" data-tn="true" action="{{ $app->url->route('login') }}">
        <input type="hidden" name="_token" value="{{ $app->request->session()->token() }}">

        <div class="isian normal">
            <label for="idAbsen">Nomor Absen</label>

            <input id="idAbsen" type="text" name="sdm_no_absen" value="{{ $app->request->old('sdm_no_absen') }}"
                pattern="^[0-9]{8}$" inputmode="numeric" required>

            <span class="t-bantu">8 digit nomor absen</span>
        </div>

        <div class="isian normal">
            <label for="password">Sandi</label>

            <input id="password" type="password" name="password" required>

            <span class="t-bantu">Sandi akun</span>
        </div>

        <div class="gspan-4"></div>

        <button class="utama pelengkap" type="submit">MASUK</button>
    </form>
</div>

@else
<div id="mengerti-mulai-aplikasi" class="pesan-internal">
    <div class="judul-form">
        <h4 class="form">Panduan.</h4>

        <a class="tutup-i">
            <svg viewbox="0 0 24 24">
                <use href="#ikontutup"></use>
            </svg>
        </a>
    </div>

    <p>
        Tekan/sentuh tombol
        <svg viewBox="0 0 24 24">
            <use href="#ikonaplikasi"></use>
        </svg>
        di ujung kanan atas layar untuk memilih aplikasi.
    </p>

    <div class="pesan-internal-tindaklanjut">
        <a class="sekunder" href="#" onclick="event.preventDefault();
            localStorage.setItem('mengerti-mulai-aplikasi', true);
            this.parentElement.parentElement.remove()">
            JANGAN TAMPILKAN LAGI
        </a>
    </div>

    <div class="bersih"></div>
</div>

<script>
    if(localStorage.getItem('mengerti-mulai-aplikasi') == 'true' === true) document.getElementById('mengerti-mulai-aplikasi').remove();
</script>
@endif

@include('pemberitahuan')
@include('komponen')