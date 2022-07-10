@if(session()->has('spanduk'))
<script>
    var spanduk = document.querySelector('#spanduk');
    var isiSpanduk = '<p><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><use xlink:href="{{ asset(mix('/ikon.svg')) . '#perhatian' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use></svg></p><a class="isi-xhr sekunder" href="">AMANKAN</a>';
    isiPemberitahuan(spanduk, isiSpanduk);
</script>
@endif

@if(session()->has('pesan'))
<script>
    var pesan = document.querySelector('#pesan');
    var isiPesan = '<p>{{ session('pesan') }}</p><button class="tutup-i"><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><use xlink:href="{{ asset(mix('/ikon.svg')) . '#tutup' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use></svg></button>';
    isiPemberitahuan(pesan, isiPesan);
</script>
@endif

@if($errors->any())
<script>
    var periksa = document.querySelector('#periksa');
    var isiPeriksa = '<details><summary>Periksa kesalahan sbb :</summary><ul> @foreach ($errors->all() as $error) <li>{{ $error }}.</li> @endforeach </ul></details>';
    isiPemberitahuan(periksa, isiPeriksa);
</script>
@endif