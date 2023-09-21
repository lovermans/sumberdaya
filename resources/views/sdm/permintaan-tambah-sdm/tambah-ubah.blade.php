@extends('rangka')

@section('isi')
    <div id="permintambahsdm">
        <form class="form-xhr kartu" id="form_permintambahsdm" method="POST" action="{{ $app->url->current() }}">
            <input name="_token" type="hidden" value="{{ $app->request->session()->token() }}">

            <div class="judul-form gspan-4">
                <h4 class="form">{{ $app->request->routeIs('sdm.permintaan-tambah-sdm.tambah') ? 'Tambah' : 'Ubah' }} Data
                    Permintaan Tambah SDM
                </h4>

                <a class="tutup-i">
                    <svg viewbox="0 0 24 24">
                        <use href="#ikontutup"></use>
                    </svg>
                </a>
            </div>

            @if ($app->request->routeIs('sdm.permintaan-tambah-sdm.ubah'))
                <div class="isian pendek">
                    <label for="permintambahsdmNomor">No Permintaan</label>
                    <input id="permintambahsdmNomor" name="tambahsdm_no" type="text"
                        value="{{ $app->request->old('tambahsdm_no', $permin->tambahsdm_no ?? null) }}" readonly inputmode="numeric" required>
                    <span class="t-bantu">Harap tidak diubah</span>
                </div>
            @endif

            <div class="isian normal">
                <label for="permintambahsdmPemohon">Pemohon</label>

                <select class="pil-cari" id="permintambahsdmPemohon" name="tambahsdm_sdm_id" required>
                    <option selected></option>

                    @if (
                        !in_array($app->request->old('tambahsdm_sdm_id', $permin->tambahsdm_sdm_id ?? null), $sdms->pluck('sdm_no_absen')->toArray()) &&
                            !is_null($app->request->old('tambahsdm_sdm_id', $permin->tambahsdm_sdm_id ?? null)))
                        <option class="merah" value="{{ $app->request->old('tambahsdm_sdm_id', $permin->tambahsdm_sdm_id ?? null) }}" selected>
                            {{ $app->request->old('tambahsdm_sdm_id', $permin->tambahsdm_sdm_id ?? null) }}
                        </option>
                    @endif

                    @foreach ($sdms as $sdm)
                        <option value="{{ $sdm->sdm_no_absen }}" @selected($sdm->sdm_no_absen == $app->request->old('tambahsdm_sdm_id', $permin->tambahsdm_sdm_id ?? null)) @class(['merah' => $sdm->sdm_tgl_berhenti])>
                            {{ $sdm->sdm_no_absen }} - {{ $sdm->sdm_nama }}
                        </option>
                    @endforeach
                </select>

                <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
            </div>

            <div class="isian normal">
                <label for="permintambahsdmPenempatan">Penempatan Dibutuhkan</label>

                <select class="pil-cari" id="permintambahsdmPenempatan" name="tambahsdm_penempatan" required>
                    <option selected></option>

                    @if (
                        !in_array($app->request->old('tambahsdm_penempatan', $permin->tambahsdm_penempatan ?? null), (array) $penempatans->pluck('atur_butir')->toArray()) &&
                            !is_null($app->request->old('tambahsdm_penempatan', $permin->tambahsdm_penempatan ?? null)))
                        <option class="merah" value="{{ $app->request->old('tambahsdm_penempatan', $permin->tambahsdm_penempatan ?? null) }}" selected>
                            {{ $app->request->old('tambahsdm_penempatan', $permin->tambahsdm_penempatan ?? null) }}
                        </option>
                    @endif

                    @foreach ($penempatans as $penempatan)
                        <option @selected($penempatan->atur_butir == $app->request->old('tambahsdm_penempatan', $permin->tambahsdm_penempatan ?? null)) @class(['merah' => $penempatan->atur_status == 'NON-AKTIF'])>
                            {{ $penempatan->atur_butir }}
                        </option>
                    @endforeach
                </select>

                <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
            </div>

            <div class="isian panjang">
                <label for="permintambahsdmPosisi">Posisi Dibutuhkan</label>

                <select class="pil-cari" id="permintambahsdmPosisi" name="tambahsdm_posisi" required>
                    <option selected></option>

                    @if (
                        !in_array($app->request->old('tambahsdm_posisi', $permin->tambahsdm_posisi ?? null), $posisis->pluck('posisi_nama')->toArray()) &&
                            !is_null($app->request->old('tambahsdm_posisi', $permin->tambahsdm_posisi ?? null)))
                        <option class="merah" value="{{ $app->request->old('tambahsdm_posisi', $permin->tambahsdm_posisi ?? null) }}" selected>
                            {{ $app->request->old('tambahsdm_posisi', $permin->tambahsdm_posisi ?? null) }}
                        </option>
                    @endif

                    @foreach ($posisis as $posisi)
                        <option @selected($posisi->posisi_nama == $app->request->old('tambahsdm_posisi', $permin->tambahsdm_posisi ?? null)) @class(['merah' => $posisi->posisi_status == 'NON-AKTIF'])>
                            {{ $posisi->posisi_nama }}
                        </option>
                    @endforeach
                </select>

                <span class="t-bantu">Disarankan tidak memilih pilihan berwarna merah</span>
            </div>

            <div class="isian pendek">
                <label for="permintambahsdmJumlah">Jumlah Dibutuhkan</label>

                <input id="permintambahsdmJumlah" name="tambahsdm_jumlah" type="number"
                    value="{{ $app->request->old('tambahsdm_jumlah', $permin->tambahsdm_jumlah ?? null) }}" min="0" inputmode="numeric" required>

                <span class="t-bantu">Jumlah SDM yang dibutuhkan</span>
            </div>

            <div class="isian pendek">
                <label for="permintambahsdmTglUsul">Tanggal Diusulkan</label>

                <input id="permintambahsdmTglUsul" name="tambahsdm_tgl_diusulkan" type="date"
                    value="{{ $app->request->old('tambahsdm_tgl_diusulkan', $permin->tambahsdm_tgl_diusulkan ?? $app->date->today()->toDateString()) }}" required>

                <span class="t-bantu">Isi tanggal</span>
            </div>

            <div class="isian pendek">
                <label for="permintambahsdmTglButuh">Tanggal Dibutuhkan</label>

                <input id="permintambahsdmTglButuh" name="tambahsdm_tgl_dibutuhkan" type="date"
                    value="{{ $app->request->old('tambahsdm_tgl_dibutuhkan',$permin->tambahsdm_tgl_dibutuhkan ??$app->date->today()->addDays(7)->toDateString()) }}"
                    required>

                <span class="t-bantu">Isi tanggal</span>
            </div>

            @if ($app->request->routeIs('sdm.permintaan-tambah-sdm.ubah'))
                <div class="isian pendek">
                    <label for="permintambahsdmPenempatan">Status Permohonan</label>

                    <select class="pil-cari" id="permintambahsdmPenempatan" name="tambahsdm_status">
                        <option selected></option>

                        @if (
                            !in_array($app->request->old('tambahsdm_status', $permin->tambahsdm_status ?? null), (array) $statuses->pluck('atur_butir')->toArray()) &&
                                !is_null($app->request->old('tambahsdm_status', $permin->tambahsdm_status ?? null)))
                            <option class="merah" value="{{ $app->request->old('tambahsdm_status', $permin->tambahsdm_status ?? null) }}" selected>
                                {{ $app->request->old('tambahsdm_status', $permin->tambahsdm_status ?? null) }}
                            </option>
                        @endif

                        @foreach ($statuses as $status)
                            <option
                                {{ $app->request->old('tambahsdm_status', $permin->tambahsdm_status ?? null) == $status->atur_butir
                                    ? 'selected'
                                    : (!$app->request->old('tambahsdm_status', $permin->tambahsdm_status ?? null) && $status->atur_butir == 'DIUSULKAN'
                                        ? 'selected'
                                        : '') }}>
                                {{ $status->atur_butir }}
                            </option>
                        @endforeach
                    </select>

                    <span class="t-bantu">Pilih satu</span>
                </div>
            @endif

            <div class="isian gspan-4">
                <label for="permintambahsdmAlasan">Alasan</label>

                <textarea id="permintambahsdmAlasan" name="tambahsdm_alasan" rows="5" required>{{ $app->request->old('tambahsdm_alasan', $permin->tambahsdm_alasan ?? null) }}</textarea>

                <span class="t-bantu">Isi alasan permintaan</span>
            </div>

            <div class="isian gspan-4">
                <label for="permintambahsdmKeterangan">Keterangan</label>

                <textarea id="permintambahsdmKeterangan" name="tambahsdm_keterangan" rows="3">
                    {{ $app->request->old('tambahsdm_keterangan', $permin->tambahsdm_keterangan ?? null) }}
                </textarea>

                <span class="t-bantu">Isi catatan detail permintaan</span>
            </div>

            @if ($app->request->routeIs('sdm.permintaan-tambah-sdm.ubah'))
                <div class="isian gspan-4">
                    <label>Berkas Permohonan</label>

                    @if (
                        $app->filesystem->exists(
                            $berkasPerminTambahSDM = 'sdm/permintaan-tambah-sdm/berkas/' . $app->request->old('tambahsdm_no', $permin->tambahsdm_no ?? null) . '.pdf'))
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
            @endif

            <div class="isian pendek">
                <label for="permintambahsdmUnggah">Unggah Berkas</label>

                <input id="permintambahsdmUnggah" name="tambahsdm_berkas" type="file" accept=".pdf,application/pdf">

                <span class="t-bantu">
                    Format PDF
                    {{ $app->filesystem->exists('sdm/permintaan-tambah-sdm/berkas' . $app->request->old('tambahsdm_no', $permin->tambahsdm_no ?? null) . '.pdf')
                        ? '(berkas yang diunggah akan menindih berkas unggahan lama).'
                        : '' }}
                </span>
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

                pilSaja('#form_permintambahsdm .pil-saja');
                pilCari('#form_permintambahsdm .pil-cari');
                formatIsian('#form_permintambahsdm .isian :is(textarea,input[type=text],input[type=search])');
            })();
        </script>

        @include('pemberitahuan')
        @include('komponen')
    </div>
@endsection
