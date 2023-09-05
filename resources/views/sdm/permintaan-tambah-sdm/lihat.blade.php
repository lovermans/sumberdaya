@extends('rangka')

@section('isi')
    <div class="scroll-margin" id="permintambahsdm_lihat">
        <div class="kartu form">
            @isset($permin)
                <div class="judul-form gspan-4">
                    <h4 class="form">Data Permintaan Tambah SDM</h4>

                    <a class="tutup-i">
                        <svg viewbox="0 0 24 24">
                            <use href="#ikontutup"></use>
                        </svg>
                    </a>
                </div>

                <div class="isian">
                    <h3>No Permintaan</h3>
                    <p>{{ $permin->tambahsdm_no }}</p>
                </div>

                <div class="isian">
                    <h3>Pemohon</h3>
                    <p>{{ $permin->tambahsdm_sdm_id }} - {{ $permin->sdm_nama }}</p>
                </div>

                <div class="isian">
                    <h3>Penempatan Dibutuhkan</h3>
                    <p>{{ $permin->tambahsdm_penempatan }}</p>
                </div>

                <div class="isian">
                    <h3>Posisi Dibutuhkan</h3>
                    <p>{{ $permin->tambahsdm_posisi }}</p>
                </div>

                <div class="isian">
                    <h3>Jumlah Dibutuhkan</h3>
                    <p>{{ $permin->tambahsdm_jumlah }}</p>
                </div>

                <div class="isian">
                    <h3>Jumlah Terpenuhi</h3>
                    <p>{{ $permin->tambahsdm_terpenuhi }}</p>
                </div>

                <div class="isian">
                    <h3>Tanggal Diusulkan</h3>
                    <p>{{ strtoupper($app->date->make($permin->tambahsdm_tgl_diusulkan)?->translatedFormat('d F Y')) }}</p>
                </div>

                <div class="isian">
                    <h3>Tanggal Dibutuhkan</h3>
                    <p>{{ strtoupper($app->date->make($permin->tambahsdm_tgl_dibutuhkan)?->translatedFormat('d F Y')) }}</p>
                </div>

                <div class="isian">
                    <h3>Status Permohonan</h3>
                    <p>{{ $permin->tambahsdm_status }}</p>
                </div>

                <div class="isian">
                    <h3>Alasan</h3>
                    <p>{!! nl2br($permin->tambahsdm_alasan) !!}</p>
                </div>
                <div class="isian">
                    <h3>Keterangan</h3>
                    <p>{!! nl2br($permin->tambahsdm_keterangan) !!}</p>
                </div>

                <div class="isian gspan-4">
                    <h3>Berkas Permohonan</h3>

                    @if ($app->filesystem->exists($berkasPerminTambahSDM = 'sdm/permintaan-tambah-sdm/berkas/' . $permin->tambahsdm_no . '.pdf'))
                        <iframe class="berkas tcetak"
                            src="{{ $app->url->route('sdm.berkas', ['berkas' => $berkasPerminTambahSDM . '?id=' . filemtime($app->storagePath('app/' . $berkasPerminTambahSDM))]) }}"
                            title="Berkas Permintaan SDM" loading="lazy" onload="if (this.contentDocument.body.id == 'badan-dokumen') this.remove()"></iframe>

                        <a class="sekunder tcetak"
                            href="{{ $app->url->route('sdm.berkas', ['berkas' => $berkasPerminTambahSDM . '?id=' . filemtime($app->storagePath('app/' . $berkasPerminTambahSDM))]) }}"
                            title="Unduh Berkas Terunggah" target="_blank">
                            <svg viewBox="0 0 24 24">
                                <use href="#ikonunduh"></use>
                            </svg>
                            BERKAS
                        </a>
                    @else
                        <p class="merah">Tidak ada berkas terunggah.</p>
                    @endif
                </div>

                <a class="isi-xhr utama tcetak" data-rekam="false" data-laju="true" data-tujuan="#permintaan-sdm_sematan_lihat"
                    href="{{ $app->url->route('sdm.permintaan-tambah-sdm.formulir', ['uuid' => $permin->tambahsdm_uuid]) }}">
                    <svg viewbox="0 0 24 24">
                        <use href="#ikoncetak"></use>
                    </svg>
                    FORMULIR
                </a>

                <div class="isian gspan-4 scroll-margin" id="permintaan-sdm_sematan_lihat"></div>

                <div class="gspan-4"></div>

                <a class="utama isi-xhr tcetak" data-rekam="false" data-tujuan="#permintambahsdm_lihat"
                    href="{{ $app->url->route('sdm.permintaan-tambah-sdm.ubah', ['uuid' => $permin->tambahsdm_uuid]) }}">UBAH</a>

                <a class="sekunder isi-xhr" data-rekam="false" data-tujuan="#permintambahsdm_lihat"
                    href="{{ $app->url->route('sdm.permintaan-tambah-sdm.hapus', ['uuid' => $permin->tambahsdm_uuid]) }}">HAPUS</a>
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
