@extends('rangka')

@section('isi')
<script>
    !function(){
    var isiPeriksa = '<div class="periksa tcetak"><details><summary>Tampilkan kesalahan :</summary><ul> @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach </ul></details><button class="tutup-i"><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><use xlink:href="{{ $mixRangka('/ikon.svg') . '#tutup' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use></svg></button></div>';
    isiPemberitahuan('pemberitahuan', isiPeriksa);}();
</script>
@endsection