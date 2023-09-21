<script nonce="{{ $app->request->session()->get('sesiNonce') }}">
    (async () => {
        while (!window.aplikasiSiap) {
            await new Promise((resolve, reject) =>
                setTimeout(resolve, 1000));
        }

        document.getElementById('pemberitahuan-umum')?.replaceChildren();

        @if ($app->request->session()->has('spanduk'))
            ! function() {
                var isiSpanduk =
                    '<div class="spanduk tcetak"><p><svg viewbox="0 0 24 24" ><use href="#ikonperhatian"></use></svg>{!! $app->request->session()->get('spanduk') !!}</p><a class="isi-xhr sekunder" href="{{ $app->url->route('sdm.ubah-sandi') }}">AMANKAN</a></div>';
                isiPemberitahuan('pemberitahuan-umum', isiSpanduk);
            }();
        @endif

        @if ($app->request->session()->has('pesan'))
            ! function() {
                var isiPesan =
                    '<div class="pesan tcetak"><p>{!! $app->request->session()->get('pesan') !!}</p><button class="tutup-i" id="pemberitahuan-tutup-pesan"><svg viewbox="0 0 24 24" ><use href="#ikontutup"></use></svg></button></div>';
                isiPemberitahuan('pemberitahuan-umum', isiPesan);
            }();
        @endif

        @if ($errors->any())
            ! function() {
                var isiPeriksa =
                    '<div class="periksa tcetak"><details><summary>Tampilkan kesalahan :</summary><ul> @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach </ul></details><button class="tutup-i" id="pemberitahuan-tutup-kesalahan"><svg viewbox="0 0 24 24" ><use href="#ikontutup"></use></svg></button></div>';
                isiPemberitahuan('pemberitahuan-umum', isiPeriksa);
            }();
        @endif

        @if ($app->request->session()->has('sesiJS'))
            {!! $app->request->session()->get('sesiJS') !!}
        @endif
    })();
</script>
