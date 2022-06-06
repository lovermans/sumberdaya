@extends(request()->segment(2) ? request()->segment(1).'.'.'rangka' : 'rangka')
@section('isi')
<p> Periksa : {{ $kesalahan ?? 'Kesalahan tidak diketahui.'}}.</p>
<p>Kembali ke <a href="{{url('/')}}">halaman utama</a>.</p>
@endsection