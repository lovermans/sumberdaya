@if(session()->has('spanduk'))
<div id="spanduk" class="tcetak">
    <p><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><use xlink:href="{{ asset(mix('/ikon.svg')) . '#perhatian' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use></svg> {{ session('spanduk') }}</p><a class="isi-xhr sekunder" href="">AMANKAN</a>
</div>
@endif

@if(session()->has('pesan'))
<div id="pesan" class="tcetak">
    <p>{{ session('pesan') }}</p><button class="tutup-i"><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><use xlink:href="{{ asset(mix('/ikon.svg')) . '#tutup' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use></svg></button>
</div>
@endif

@if($errors->any())
<div id="periksa" class="tcetak">
    <details>
        <summary>Periksa kesalahan sbb :</summary>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}.</li>
            @endforeach
        </ul>
    </details>
</div>
@endif