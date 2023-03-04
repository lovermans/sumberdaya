@if($sesiRangka->has('spanduk'))
<script>
    !function(){
    var isiSpanduk = '<div class="spanduk tcetak"><p><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><use xlink:href="{{ $mixRangka('/ikon.svg') . '#perhatian' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use></svg> {!! $sesiRangka->get('spanduk') !!}</p><a class="isi-xhr sekunder" href="{{$urlRangka->route('ubah-sandi', [], false)}}">AMANKAN</a></div>';
    isiPemberitahuan('pemberitahuan', isiSpanduk);}();
</script>
@endif

@if($sesiRangka->has('pesan'))
<script>
    !function(){
    var isiPesan = '<div class="pesan tcetak"><p>{!! $sesiRangka->get('pesan') !!}</p><button class="tutup-i"><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><use xlink:href="{{ $mixRangka('/ikon.svg') . '#tutup' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use></svg></button></div>';
    isiPemberitahuan('pemberitahuan', isiPesan);}();
</script>
@endif

@if($errors->any())
<script>
    !function(){
    var isiPeriksa = '<div class="periksa tcetak"><details><summary>Tampilkan kesalahan :</summary><ul> @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach </ul></details><button class="tutup-i"><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><use xlink:href="{{ $mixRangka('/ikon.svg') . '#tutup' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use></svg></button></div>';
    isiPemberitahuan('pemberitahuan', isiPeriksa);}();
</script>
@endif