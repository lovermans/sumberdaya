@extends('sdm.rangka')
@section('isi')
    @include('pemberitahuan')
    @can('sdm-pengurus')
        <div class="pintasan tcetak">
            <a class="isi-xhr" href="{{route('register')}}"></a>
        </div>
    @endcan
@endsection
