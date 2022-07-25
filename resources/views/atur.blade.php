@extends('rangka')

@section('isi')
    Tes
    @includeWhen(session()->has('spanduk') || session()->has('pesan') || $errors->any(), 'pemberitahuan')
@endsection
