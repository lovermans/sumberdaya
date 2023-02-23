@extends('rangka')

@section('isi')
<div id="penempatan_sdm_lihat">
    <h4>Data Penempatan SDM</h4>
    <div class="kartu form">
        @isset($penem)
            <div class="isian gspan-2">
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
                @if ($storageRangka->exists('sdm/penempatan/'.$rekRangka->old('sdm_no_absen', $penem->sdm_no_absen ?? null).' - '.$rekRangka->old('penempatan_mulai', $penem->penempatan_mulai ?? null).'.pdf'))
                    <iframe src="{{ $urlRangka->route('sdm.penempatan.berkas', ['berkas' => $rekRangka->old('sdm_no_absen', $penem->sdm_no_absen ?? null).' - '.$rekRangka->old('penempatan_mulai', $penem->penempatan_mulai ?? null).'.pdf' . '?' . filemtime(storage_path('app/sdm/penempatan/' . $rekRangka->old('sdm_no_absen', $penem->sdm_no_absen ?? null).' - '.$rekRangka->old('penempatan_mulai', $penem->penempatan_mulai ?? null).'.pdf'))]) }}" title="Berkas Penempatan SDM" loading="lazy" style="width:100%;height:auto;aspect-ratio:4/3"></iframe>
                @else
                    <p class="merah">Tidak ada berkas terunggah.</p>
                @endif
            </div>
            @if($strRangka->contains($userRangka->sdm_hak_akses, 'SDM-PENGURUS'))
            <div class="isian gspan-2" id="penempatan-cetakFormulir">
                <select class="pil-saja tombol" onchange="if (this.value !== '') lemparXHR(false, '#penempatan-cetakFormulirStatus', this.value, 'GET', null, null, 'true')">
                    <option value="">CETAK FORMULIR</option>
                    <option value="{{$urlRangka->route('sdm.penempatan.pkwt-sdm', ['uuid' => $penem->penempatan_uuid])}}">PKWT</option>
                    <option value="{{$urlRangka->route('sdm.penempatan.formulir-penilaian-sdm', ['uuid' => $penem->penempatan_uuid])}}">PENILAIAN KINERJA</option>
                    <option value="{{$urlRangka->route('sdm.penempatan.formulir-perubahan-status-sdm', ['uuid' => $penem->penempatan_uuid])}}">PERUBAHAN STATUS</option>
                </select>
            </div>
            <div class="isian gspan-4" id="penempatan-cetakFormulirStatus" style="scroll-margin:4em 0 0 0"></div>
            <script>
                pilSaja('#penempatan-cetakFormulir .pil-saja');
            </script>
            @endif
            <div class="isian gspan-4"></div>
            <a class="utama isi-xhr" data-rekam="false" data-tujuan="#penempatan_sdm_lihat" href="{{ $urlRangka->route('sdm.penempatan.ubah', ['uuid' => $penem->penempatan_uuid]) }}">UBAH</a>
            <a class="sekunder isi-xhr" data-rekam="false" data-tujuan="#penempatan_sdm_lihat" href="{{ $urlRangka->route('sdm.penempatan.hapus', ['uuid' => $penem->penempatan_uuid]) }}">HAPUS</a>
            @if ($rekRangka->pjax())
                <a class="sekunder" href="#" onclick="event.preventDefault();this.parentElement.parentElement.remove()">TUTUP</a>
            @else
                <a class="isi-xhr sekunder" href="{{$urlRangka->to($rekRangka->session()->get('tautan-perujuk') ?? '/')}}">TUTUP</a>
            @endif
        @else
            <div class="isian gspan-4">
                <p>Periksa kembali data yang diminta.</p>
            </div>
        @endisset
    </div>
    
    @includeWhen($rekRangka->session()->has('spanduk') || $rekRangka->session()->has('pesan') || $errors->any(), 'pemberitahuan')
</div>
@endsection
