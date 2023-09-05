@extends('rangka')

@section('isi')
    <div id="penempatan_sdm_tambahUbah">
        <form class="form-xhr kartu" id="form_penempatanSDMTambahUbah" method="POST" action="{{ $app->url->current() }}">
            <input name="_token" type="hidden" value="{{ $app->request->session()->token() }}">

            <div class="judul-form gspan-4">
                <h4 class="form">
                    {{ $app->request->routeIs('sdm.penempatan.tambah') ? 'Tambah' : 'Ubah' }} Data Penempatan SDM
                </h4>

                <a class="tutup-i">
                    <svg viewbox="0 0 24 24">
                        <use href="#ikontutup"></use>
                    </svg>
                </a>
            </div>

            <div class="isian pendek">
                <label for="penempatan_no_absen">No Absen</label>

                <input id="penempatan_no_absen" name="penempatan_no_absen" type="text"
                    value="{{ $app->request->old('penempatan_no_absen', $penem->sdm_no_absen ?? null) }}" readonly inputmode="numeric" required>

                <span class="t-bantu">Harap tidak diubah</span>
            </div>

            <div class="isian panjang">
                <label for="penempatan_nama_sdm">Nama</label>

                <input id="penempatan_nama_sdm" name="penempatan_nama_sdm" type="text" value="{{ $penem->sdm_nama }}" disabled>

                <span class="t-bantu">Harap tidak diubah</span>
            </div>

            <div class="isian pendek">
                <label for="penempatan_lokasi">Penempatan</label>

                <select class="pil-cari" id="penempatan_lokasi" name="penempatan_lokasi" required>
                    <option selected></option>

                    @if (
                        !in_array($app->request->old('penempatan_lokasi', $penem->penempatan_lokasi ?? null), (array) $penempatans->pluck('atur_butir')->toArray()) &&
                            !is_null($app->request->old('penempatan_lokasi', $penem->penempatan_lokasi ?? null)))
                        <option class="merah" value="{{ $app->request->old('penempatan_lokasi', $penem->penempatan_lokasi ?? null) }}" selected>
                            {{ $app->request->old('penempatan_lokasi', $penem->penempatan_lokasi ?? null) }}
                        </option>
                    @endif

                    @foreach ($penempatans as $penempatan)
                        <option @selected($penempatan->atur_butir == $app->request->old('penempatan_lokasi', $penem->penempatan_lokasi ?? null)) @class(['merah' => $penempatan->atur_status == 'NON-AKTIF'])>
                            {{ $penempatan->atur_butir }}
                        </option>
                    @endforeach
                </select>

                <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
            </div>

            <div class="isian panjang">
                <label for="penempatan_posisi">Posisi</label>
                <select class="pil-cari" id="penempatan_posisi" name="penempatan_posisi" required>
                    <option selected></option>

                    @if (
                        !in_array($app->request->old('penempatan_posisi', $penem->penempatan_posisi ?? null), (array) $posisis->pluck('posisi_nama')->toArray()) &&
                            !is_null($app->request->old('penempatan_posisi', $penem->penempatan_posisi ?? null)))
                        <option class="merah" value="{{ $app->request->old('penempatan_posisi', $penem->penempatan_posisi ?? null) }}" selected>
                            {{ $app->request->old('penempatan_posisi', $penem->penempatan_posisi ?? null) }}
                        </option>
                    @endif

                    @foreach ($posisis as $posisi)
                        <option @selected($posisi->posisi_nama == $app->request->old('penempatan_posisi', $penem->penempatan_posisi ?? null)) @class(['merah' => $posisi->posisi_status == 'NON-AKTIF'])>
                            {{ $posisi->posisi_nama }}
                        </option>
                    @endforeach
                </select>

                <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
            </div>

            <div class="isian normal">
                <label for="penempatan_kontrak">Status Kontrak</label>

                <select class="pil-cari" id="penempatan_kontrak" name="penempatan_kontrak" required>
                    <option selected></option>

                    @if (
                        !in_array($app->request->old('penempatan_kontrak', $penem->penempatan_kontrak ?? null), (array) $kontraks->pluck('atur_butir')->toArray()) &&
                            !is_null($app->request->old('penempatan_kontrak', $penem->penempatan_kontrak ?? null)))
                        <option class="merah" value="{{ $app->request->old('penempatan_kontrak', $penem->penempatan_kontrak ?? null) }}" selected>
                            {{ $app->request->old('penempatan_kontrak', $penem->penempatan_kontrak ?? null) }}</option>
                    @endif

                    @foreach ($kontraks as $kontrak)
                        <option @selected($kontrak->atur_butir == $app->request->old('penempatan_kontrak', $penem->penempatan_kontrak ?? null)) @class(['merah' => $kontrak->atur_status == 'NON-AKTIF'])>
                            {{ $kontrak->atur_butir }}
                        </option>
                    @endforeach
                </select>

                <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
            </div>

            <div class="isian pendek">
                <label for="penempatan_mulai">Tanggal Mulai</label>

                <input id="penempatan_mulai" name="penempatan_mulai" type="date"
                    value="{{ $app->request->old('penempatan_mulai', $penem->penempatan_mulai ?? today()->toDateString()) }}" required>

                <span class="t-bantu">Isi tanggal</span>
            </div>

            <div class="isian pendek">
                <label for="penempatan_selesai">Tanggal Selesai</label>

                <input id="penempatan_selesai" name="penempatan_selesai" type="date"
                    value="{{ $app->request->old('penempatan_selesai', $penem->penempatan_selesai ?? null) }}" @required(!str()->contains($app->request->old('penempatan_kontrak', $penem->penempatan_kontrak ?? null), ['PKWTT', 'OS-']) && $app->request->old('penempatan_kontrak', $penem->penempatan_kontrak ?? null))>
                <span class="t-bantu">Kosongi jika PKWTT atau OS</span>
            </div>

            <div class="isian kecil">
                <label for="penempatan_ke">Kontrak Ke</label>

                <input id="penempatan_ke" name="penempatan_ke" type="number" value="{{ $app->request->old('penempatan_ke', $penem->penempatan_ke ?? null) }}"
                    min="0" @required(str()->contains($app->request->old('penempatan_kontrak', $penem->penempatan_kontrak ?? null), ['PKWT', 'PERCOBAAN']))>

                <span class="t-bantu">Angka</span>
            </div>

            <div class="isian normal">
                <label for="penempatan_kategori">Kategori</label>

                <select class="pil-cari" id="penempatan_kategori" name="penempatan_kategori" required>
                    <option selected></option>

                    @if (
                        !in_array($app->request->old('penempatan_kategori', $penem->penempatan_kategori ?? null), (array) $kategoris->pluck('atur_butir')->toArray()) &&
                            !is_null($app->request->old('penempatan_kategori', $penem->penempatan_kategori ?? null)))
                        <option class="merah" value="{{ $app->request->old('penempatan_kategori', $penem->penempatan_kategori ?? null) }}" selected>
                            {{ $app->request->old('penempatan_kategori', $penem->penempatan_kategori ?? null) }}
                        </option>
                    @endif

                    @foreach ($kategoris as $kategori)
                        <option @selected($kategori->atur_butir == $app->request->old('penempatan_kategori', $penem->penempatan_kategori ?? null)) @class(['merah' => $kategori->atur_status == 'NON-AKTIF'])>
                            {{ $kategori->atur_butir }}
                        </option>
                    @endforeach
                </select>

                <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
            </div>

            <div class="isian normal">
                <label for="penempatan_pangkat">Pangkat</label>

                <select class="pil-cari" id="penempatan_pangkat" name="penempatan_pangkat" required>
                    <option selected></option>

                    @if (
                        !in_array($app->request->old('penempatan_pangkat', $penem->penempatan_pangkat ?? null), (array) $pangkats->pluck('atur_butir')->toArray()) &&
                            !is_null($app->request->old('penempatan_pangkat', $penem->penempatan_pangkat ?? null)))
                        <option class="merah" value="{{ $app->request->old('penempatan_pangkat', $penem->penempatan_pangkat ?? null) }}" selected>
                            {{ $app->request->old('penempatan_pangkat', $penem->penempatan_pangkat ?? null) }}</option>
                    @endif

                    @foreach ($pangkats as $pangkat)
                        <option @selected($pangkat->atur_butir == $app->request->old('penempatan_pangkat', $penem->penempatan_pangkat ?? null)) @class(['merah' => $pangkat->atur_status == 'NON-AKTIF'])>
                            {{ $pangkat->atur_butir }}
                        </option>
                    @endforeach
                </select>

                <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
            </div>

            <div class="isian pendek">
                <label for="penempatan_golongan">Golongan</label>

                <select class="pil-cari" id="penempatan_golongan" name="penempatan_golongan" required>
                    <option selected></option>

                    @if (
                        !in_array($app->request->old('penempatan_golongan', $penem->penempatan_golongan ?? null), (array) $golongans->pluck('atur_butir')->toArray()) &&
                            !is_null($app->request->old('penempatan_golongan', $penem->penempatan_golongan ?? null)))
                        <option class="merah" value="{{ $app->request->old('penempatan_golongan', $penem->penempatan_golongan ?? null) }}" selected>
                            {{ $app->request->old('penempatan_golongan', $penem->penempatan_golongan ?? null) }}</option>
                    @endif

                    @foreach ($golongans as $golongan)
                        <option @selected($golongan->atur_butir == $app->request->old('penempatan_golongan', $penem->penempatan_golongan ?? null)) @class(['merah' => $golongan->atur_status == 'NON-AKTIF'])>
                            {{ $golongan->atur_butir }}
                        </option>
                    @endforeach
                </select>

                <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
            </div>

            <div class="isian normal">
                <label for="penempatan_grup">Grup</label>

                <input id="penempatan_grup" name="penempatan_grup" type="text"
                    value="{{ $app->request->old('penempatan_grup', $penem->penempatan_grup ?? null) }}">

                <span class="t-bantu">Regu/Unit Kerja</span>
            </div>

            <div class="isian pendek">
                <label for="penempatan_berkas">Unggah Berkas</label>

                <input id="penempatan_berkas" name="penempatan_berkas" type="file" accept=".pdf,application/pdf">

                <span class="t-bantu">
                    Scan PDF perjanjian kerja dan perubahan status yang mengikuti
                    {{ $app->filesystem->exists(
                        'sdm/penempatan/berkas/' .
                            $app->request->old('sdm_no_absen', $penem->sdm_no_absen ?? null) .
                            ' - ' .
                            $app->request->old('penempatan_mulai', $penem->penempatan_mulai ?? null) .
                            '.pdf',
                    )
                        ? '(berkas yang diunggah akan menindih berkas unggahan lama).'
                        : '' }}
                </span>
            </div>

            @if ($app->request->routeIs('sdm.penempatan.ubah'))
                <div class="isian gspan-4">
                    <label>Berkas Penempatan</label>

                    @if (
                        $app->filesystem->exists(
                            $berkas_penempatan =
                                'sdm/penempatan/berkas/' .
                                $app->request->old('sdm_no_absen', $penem->sdm_no_absen ?? null) .
                                ' - ' .
                                $app->request->old('penempatan_mulai', $penem->penempatan_mulai ?? null) .
                                '.pdf'))
                        <iframe class="berkas tcetak"
                            src="{{ $app->url->route('sdm.berkas', ['berkas' => $berkas_penempatan . '?id=' . filemtime($app->storagePath('app/' . $berkas_penempatan))]) }}"
                            title="Berkas Penempatan SDM" loading="lazy" onload="if (this.contentDocument.body.id == 'badan-dokumen') this.remove()"></iframe>

                        <a class="sekunder tcetak"
                            href="{{ $app->url->route('sdm.berkas', ['berkas' => $berkas_penempatan . '?id=' . filemtime($app->storagePath('app/' . $berkas_penempatan))]) }}"
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
            @endif

            <div class="isian gspan-4">
                <label for="penempatan_keterangan">Keterangan</label>

                <textarea id="penempatan_keterangan" name="penempatan_keterangan" rows="3">{{ $app->request->old('penempatan_keterangan', $penem->penempatan_keterangan ?? null) }}</textarea>

                <span class="t-bantu">Isi catatan rincian penempatan</span>
            </div>

            <div class="gspan-4"></div>

            <button class="utama pelengkap" type="submit">SIMPAN</button>
        </form>

        <script>
            (async () => {
                while (!window.aplikasiSiap) {
                    await new Promise((resolve, reject) =>
                        setTimeout(resolve, 1000));
                }

                pilSaja('#form_penempatanSDMTambahUbah .pil-saja');
                pilCari('#form_penempatanSDMTambahUbah .pil-cari');
                formatIsian('#form_penempatanSDMTambahUbah .isian :is(textarea,input[type=text],input[type=search])');

                ! function() {
                    var kontrak = document.getElementById('penempatan_kontrak');
                    kontrak.onchange = function() {
                        var sampai = document.getElementById('penempatan_selesai'),
                            kontrakke = document.getElementById('penempatan_ke');

                        if (this.selectedOptions[0].text.match(/^(PKWT|PERCOBAAN)$/)) {
                            sampai.required = true;
                            kontrakke.required = true;
                        } else {
                            sampai.required = false;
                            kontrakke.required = false;
                        };
                    };
                }();
            })();
        </script>

        @include('pemberitahuan')
        @include('komponen')
    </div>
@endsection
