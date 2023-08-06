@extends('rangka')

@section('isi')
<script>
    (async() => {
        while(!window.aplikasiSiap) {
            await new Promise((resolve,reject) =>
            setTimeout(resolve, 1000));
        }
        
        isiPemberitahuan('pemberitahuan', '');
        
        @if($rekRangka->session()->has('spanduk'))
            !function(){
            var isiSpanduk = '<div class="spanduk tcetak"><p><svg viewbox="0 0 24 24" ><use href="#ikonperhatian"></use></svg> {!! $rekRangka->session()->get('spanduk') !!}</p><a class="isi-xhr sekunder" href="{{$urlRangka->route('ubah-sandi')}}">AMANKAN</a></div>';
            isiPemberitahuan('pemberitahuan', isiSpanduk);}();
        @endif

        @if($rekRangka->session()->has('pesan'))
            !function(){
            var isiPesan = '<div class="pesan tcetak"><p>{!! $rekRangka->session()->get('pesan') !!}</p><button class="tutup-i" id="http422-tutup-pesan"><svg viewbox="0 0 24 24" ><use href="#ikontutup"></use></svg></button></div>';
            isiPemberitahuan('pemberitahuan', isiPesan);}();
        @endif
        
        @if($errors->any())
            !function(){
            var isiPeriksa = '<div class="periksa tcetak"><details><summary>Tampilkan kesalahan :</summary><ul> @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach </ul></details><button class="tutup-i" id="http422-tutup-kesalahan"><svg viewbox="0 0 24 24" ><use href="#ikontutup"></use></svg></button></div>';
            isiPemberitahuan('pemberitahuan', isiPeriksa);}();
        @endif
    })();
</script>
@endsection