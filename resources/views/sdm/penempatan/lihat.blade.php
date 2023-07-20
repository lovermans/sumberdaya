@extends('rangka')

@section('isi')
<div id="penempatan_sdm_lihat">
    <div class="kartu form">
        @isset($penem)
        <div class="gspan-4">
            <a class="tutup-i"><svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#tutup' }}"
                        xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg></a>

            <h4 class="form">Data Penempatan SDM</h4>
        </div>

        <div class="isian">
            <h3>Identitas</h3>

            <p>{{ $penem->sdm_no_absen }} - {{ $penem->sdm_nama }}</p>
        </div>

        <div class="isian">
            <h3>Tanggal Masuk</h3>

            <p>{{ strtoupper($dateRangka->make($penem->sdm_tgl_gabung)?->translatedFormat('d F Y')) }}</p>
        </div>

        <div class="isian">
            <h3>Tanggal Keluar</h3>

            <p>{{ strtoupper($dateRangka->make($penem->sdm_tgl_berhenti)?->translatedFormat('d F Y')) }}</p>
        </div>

        <div class="isian">
            <h3>Lokasi</h3>

            <p>{{ $penem->penempatan_lokasi }}</p>
        </div>

        <div class="isian gspan-2">
            <h3>Jabatan</h3>

            <p>{{ $penem->penempatan_posisi }}</p>
        </div>

        <div class="isian">
            <h3>Kontrak</h3>

            <p>{{ $penem->penempatan_kontrak }}</p>
        </div>

        <div class="isian">
            <h3>Kontrak Mulai</h3>

            <p>{{ strtoupper($dateRangka->make($penem->penempatan_mulai)?->translatedFormat('d F Y')) }}</p>
        </div>

        <div class="isian">
            <h3>Kontrak Selesai</h3>

            <p>{{ strtoupper($dateRangka->make($penem->penempatan_selesai)?->translatedFormat('d F Y')) }}</p>
        </div>

        <div class="isian">
            <h3>Kontrak Ke</h3>

            <p>{{$penem->penempatan_ke}}</p>
        </div>

        <div class="isian">
            <h3>Kategori</h3>

            <p>{{$penem->penempatan_kategori}}</p>
        </div>

        <div class="isian">
            <h3>Pangkat</h3>

            <p>{{$penem->penempatan_pangkat}}</p>
        </div>

        <div class="isian">
            <h3>Golongan</h3>

            <p>{{$penem->penempatan_golongan}}</p>
        </div>

        <div class="isian">
            <h3>Grup</h3>

            <p>{{$penem->penempatan_grup}}</p>
        </div>

        <div class="isian gspan-4">
            <h3>Keterangan</h3>

            <p>{!! nl2br($penem->penempatan_keterangan) !!}</p>
        </div>

        <div class="isian gspan-4">
            <h3>Berkas Permohonan</h3>

            @if ($storageRangka->exists('sdm/penempatan/berkas/' . $penem->sdm_no_absen . ' - ' .
            $penem->penempatan_mulai . '.pdf'))
            <iframe class="berkas tcetak"
                src="{{ $urlRangka->route('sdm.penempatan.berkas', ['berkas' => $penem->sdm_no_absen . ' - ' . $penem->penempatan_mulai . '.pdf' . '?' . filemtime(storage_path('app/sdm/penempatan/berkas/' . $penem->sdm_no_absen . ' - ' . $penem->penempatan_mulai . '.pdf'))]) }}"
                title="Berkas Penempatan SDM" loading="lazy"
                onload="if (this.contentDocument.body.id == 'badan-dokumen') this.remove()"></iframe>

            <a class="sekunder tcetak" target="_blank" title="Unduh Berkas Terunggah"
                href="{{ $urlRangka->route('sdm.penempatan.berkas', ['berkas' => $penem->sdm_no_absen . ' - ' . $penem->penempatan_mulai . '.pdf' . '?' . filemtime(storage_path('app/sdm/penempatan/berkas/' . $penem->sdm_no_absen . ' - ' . $penem->penempatan_mulai . '.pdf'))]) }}">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#unduh' }}"
                        xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                </svg>
                BERKAS
            </a>

            @else
            <p class="merah">Tidak ada berkas terunggah.</p>

            @endif
        </div>

        @if($strRangka->contains($rekRangka->user()->sdm_hak_akses, 'SDM-PENGURUS'))
        <div class="isian" id="penempatan-cetakFormulir">
            <select class="pil-saja tombol"
                onchange="if (this.value !== '') lemparXHR({tujuan : '#penempatan-cetakFormulirStatus', tautan : this.value, strim : true})">
                <option value="">CETAK FORMULIR</option>

                <option value="{{$urlRangka->route('sdm.penempatan.pkwt-sdm', ['uuid' => $penem->penempatan_uuid])}}">
                    PKWT</option>

                <option
                    value="{{$urlRangka->route('sdm.penempatan.formulir-penilaian-sdm', ['uuid' => $penem->penempatan_uuid])}}">
                    PENILAIAN KINERJA</option>

                <option
                    value="{{$urlRangka->route('sdm.penempatan.formulir-perubahan-status-sdm', ['uuid' => $penem->penempatan_uuid])}}">
                    PERUBAHAN STATUS</option>
            </select>
        </div>

        <div class="isian gspan-4 scroll-margin" id="penempatan-cetakFormulirStatus"></div>

        <script>
            (async() => {
                while(!window.aplikasiSiap) {
                    await new Promise((resolve,reject) =>
                    setTimeout(resolve, 1000));
                }
                
                pilSaja('#penempatan-cetakFormulir .pil-saja');
            })();
        </script>
        @endif

        <div class="isian gspan-4"></div>

        <a class="utama isi-xhr" data-rekam="false" data-tujuan="#penempatan_sdm_lihat"
            href="{{ $urlRangka->route('sdm.penempatan.ubah', ['uuid' => $penem->penempatan_uuid]) }}">UBAH</a>

        <a class="sekunder isi-xhr" data-rekam="false" data-tujuan="#penempatan_sdm_lihat"
            href="{{ $urlRangka->route('sdm.penempatan.hapus', ['uuid' => $penem->penempatan_uuid]) }}">HAPUS</a>

        @else
        <div class="isian gspan-4">
            <p>Periksa kembali data yang diminta.</p>
        </div>

        @endisset
    </div>

    @include('pemberitahuan')
    @include('komponen')
</div>
@endsection