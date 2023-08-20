@extends('rangka')

@section('isi')
<div id="nilai_sdm_lihat">
    <div class="kartu form">
        @isset($nilai)
        <div class="judul-form gspan-4">
            <h4 class="form">Data Penilaian SDM</h4>

            <a class="tutup-i">
                <svg viewbox="0 0 24 24">
                    <use href="#ikontutup"></use>
                </svg>
            </a>
        </div>

        <div class="isian">
            <h3>Identitas</h3>
            <p>{{ $nilai->nilaisdm_no_absen }} - {{ $nilai->sdm_nama }} - {{ $nilai->penempatan_lokasi }} - {{
                $nilai->penempatan_kontrak }} - {{ $nilai->penempatan_posisi }}</p>
        </div>

        <div class="isian">
            <h3>Tahun Penilaian</h3>
            <p>{{ $nilai->nilaisdm_tahun }}</p>
        </div>

        <div class="isian">
            <h3>Periode Penilaian</h3>
            <p>{{ $nilai->nilaisdm_periode }}</p>
        </div>

        <div class="isian">
            <h3>Nilai Bobot Kehadiran</h3>
            <p>{{ $nilai->nilaisdm_bobot_hadir }}</p>
        </div>

        <div class="isian">
            <h3>Nilai Bobot Sikap Kerja</h3>
            <p>{{ $nilai->nilaisdm_bobot_sikap }}</p>
        </div>

        <div class="isian">
            <h3>Nilai Bobot Target Pekerjaan</h3>
            <p>{{ $nilai->nilaisdm_bobot_target }}</p>
        </div>

        <div class="isian">
            <h3>Total Nilai</h3>
            <p>{{ $nilai->nilaisdm_total }}</p>
        </div>

        <div class="isian gspan-4">
            <h3>Tindak Lanjut Penilaian</h3>
            <p>{!! nl2br($nilai->nilaisdm_tindak_lanjut) !!}</p>
        </div>

        <div class="isian gspan-4">
            <h3>Keterangan Penilaian</h3>
            <p>{!! nl2br($nilai->nilaisdm_keterangan) !!}</p>
        </div>

        <div class="isian gspan-4">
            <h3>Berkas Penilaian</h3>

            @if ($app->filesystem->exists('sdm/penilaian/berkas/' . $nilai->nilaisdm_no_absen . ' - ' .
            $nilai->nilaisdm_tahun . ' - ' . $nilai->nilaisdm_periode . '.pdf'))
            <iframe class="berkas tcetak" src="{{ $app->url->route('sdm.penilaian.berkas', ['berkas' => $nilai->nilaisdm_no_absen . ' - ' .
                $nilai->nilaisdm_tahun . ' - ' . $nilai->nilaisdm_periode . '.pdf' . '?' . filemtime(storage_path('app/sdm/penilaian/berkas/' . $nilai->nilaisdm_no_absen . ' - ' .
            $nilai->nilaisdm_tahun . ' - ' . $nilai->nilaisdm_periode . '.pdf'))]) }}"
                title="Bukti Pendukung Laporan Penilaian SDM" loading="lazy"
                onload="if (this.contentDocument.body.id == 'badan-dokumen') this.remove()"></iframe>

            <a class="sekunder tcetak" target="_blank" title="Unduh Berkas Terunggah" href="{{ $app->url->route('sdm.penilaian.berkas', ['berkas' => $nilai->nilaisdm_no_absen . ' - ' .
                $nilai->nilaisdm_tahun . ' - ' . $nilai->nilaisdm_periode . '.pdf' . '?' . filemtime(storage_path('app/sdm/penilaian/berkas/' . $nilai->nilaisdm_no_absen . ' - ' .
            $nilai->nilaisdm_tahun . ' - ' . $nilai->nilaisdm_periode . '.pdf'))]) }}">
                <svg viewBox="0 0 24 24">
                    <use href="#ikonunduh"></use>
                </svg>
                BERKAS
            </a>

            @else
            <p class="merah">Tidak ada bukti pendukung terunggah.</p>

            @endif
        </div>

        <a class="utama isi-xhr" data-rekam="false" data-tujuan="#nilai_sdm_lihat_sematan"
            href="{{ $app->url->route('sdm.penilaian.ubah', ['uuid' => $nilai->nilaisdm_uuid]) }}">UBAH
            PENILAIAN</a>

        @else
        <div class="isian gspan-4">
            <p>Periksa kembali data yang diminta.</p>
        </div>

        @endisset
    </div>

    <div id="nilai_sdm_lihat_sematan" class="scroll-margin"></div>

    @include('pemberitahuan')
    @include('komponen')
</div>
@endsection