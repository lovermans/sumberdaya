@if (!$app->request->user())
	<div class="mini-aside">
		<form action="{{ $app->url->route('login') }}" class="form-xhr kartu" data-tn="true" id="form-masuk" method="POST">
			<input name="_token" type="hidden" value="{{ $app->request->session()->token() }}">

			<div class="isian normal">
				<label for="idAbsen">Nomor Absen</label>

				<input id="idAbsen" inputmode="numeric" name="sdm_no_absen" pattern="^[0-9]{8}$" required type="text"
					value="{{ $app->request->old('sdm_no_absen') }}">

				<span class="t-bantu">8 digit nomor absen</span>
			</div>

			<div class="isian normal">
				<label for="password">Sandi</label>

				<input id="password" name="password" required type="password">

				<span class="t-bantu">Sandi akun</span>
			</div>

			<div class="gspan-4"></div>

			<button class="utama pelengkap" type="submit">MASUK</button>
		</form>
	</div>
@else
	<div class="pesan-internal" id="mengerti-mulai-aplikasi">
		<div class="pesan-internal-kepala">
			<div class="judul-form">
				<h4 class="form">Panduan.</h4>

				<a class="tutup-i">
					<svg viewbox="0 0 24 24">
						<use href="#ikontutup"></use>
					</svg>
				</a>
			</div>
		</div>
		<p>
			Tekan/sentuh tombol
			<svg viewBox="0 0 24 24">
				<use href="#ikonaplikasi"></use>
			</svg>
			di ujung kanan atas layar untuk memilih aplikasi.
		</p>

		<div class="pesan-internal-tindaklanjut">
			<a class="sekunder jtl" href="#">JANGAN TAMPILKAN LAGI</a>
		</div>

		<div class="bersih"></div>
	</div>

	<script>
		if (localStorage.getItem('mengerti-mulai-aplikasi') == 'true' === true) document.getElementById(
			'mengerti-mulai-aplikasi').remove();
	</script>
@endif

@include('pemberitahuan')
@include('komponen')
