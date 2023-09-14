@isset($statuses)
    <details class="kartu">
        <summary>
            Perubahan Status SDM 40 Hari Terakhir : {{ number_format($statuses->count(), 0, ',', '.') }} Personil
        </summary>

        <b>
            <i>
                <small>
                    Jumlah SDM : Organik = {{ number_format($jumlahOrganik, 0, ',', '.') }} Personil | Outsource =
                    {{ number_format($jumlahOS, 0, ',', '.') }} Personil.
                </small>
            </i>
        </b>

        <div class="scroll-margin" id="tabel_status_sematan"></div>

        <div class="kartu" id="tabel_status">
            <span class="biru">Biru</span> : Outsource.
            <div class="data ringkas">
                <table class="tabel">
                    <thead>
                        <tr>
                            <th></th>
                            <th>No</th>
                            <th>Identitas</th>
                            <th>PKWT</th>
                            <th>Lainnya</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($statuses as $no => $status)
                            <tr @class([
                                'biru' => str()->contains($status->penempatan_kontrak, 'OS-'),
                            ])>
                                <th>
                                    <div class="pil-aksi">
                                        <button id="{{ 'aksi_pstatus_baris_' . $loop->iteration }}" title="Pilih Tindakan">
                                            <svg viewbox="0 0 24 24">
                                                <use href="#ikonmenuvert">
                                                </use>
                                            </svg>
                                        </button>

                                        <div class="aksi">
                                            <a class="isi-xhr" data-rekam="false" data-tujuan="#tabel_status_sematan"
                                                href="{{ $app->url->route('sdm.penempatan.lihat', ['uuid' => $status->penempatan_uuid]) }}"
                                                title="Lihat Data Penempatan">Buka Data</a>

                                            <a class="isi-xhr" data-rekam="false" data-tujuan="#tabel_status_sematan"
                                                href="{{ $app->url->route('sdm.penempatan.ubah', ['uuid' => $status->penempatan_uuid]) }}"
                                                title="Ubah Data Penempatan">Ubah Penempatan</a>
                                        </div>
                                    </div>
                                </th>

                                <td>{{ $loop->iteration }}</td>

                                <td class="profil">
                                    <a class="isi-xhr taut-akun" href="{{ $app->url->route('sdm.akun', ['uuid' => $status->sdm_uuid]) }}">
                                        <img src="{{ $app->filesystem->exists('sdm/foto-profil/' . $status->sdm_no_absen . '.webp')
                                            ? $app->url->route('sdm.tautan-foto-profil', [
                                                'berkas_foto_profil' =>
                                                    $status->sdm_no_absen . '.webp' . '?id=' . filemtime($app->storagePath('app/sdm/foto-profil/' . $status->sdm_no_absen . '.webp')),
                                                false,
                                            ])
                                            : $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/blank.webp')) }}"
                                            title="{{ $status->sdm_nama ?? 'foto akun' }}" alt="{{ $status->sdm_nama ?? 'foto akun' }}"
                                            @class([
                                                'akun',
                                                'svg' => !$app->filesystem->exists(
                                                    'sdm/foto-profil/' . $status->sdm_no_absen . '.webp'),
                                            ]) loading="lazy">

                                        <small>{{ $status->sdm_no_absen }} : {{ $status->sdm_nama }}</small>
                                    </a>
                                </td>

                                <td>
                                    {{ strtoupper($app->date->make($status->penempatan_mulai)?->translatedFormat('d F Y')) }}
                                    s.d
                                    {{ strtoupper($app->date->make($status->penempatan_selesai)?->translatedFormat('d F Y')) }} <br>
                                    {{ $status->penempatan_kontrak }} Ke : {{ $status->penempatan_ke }}
                                </td>

                                <td>
                                    {{ $status->penempatan_lokasi }} <br>
                                    {{ $status->penempatan_posisi }}
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <th></th>
                                <td colspan="4">Tidak Ada Data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($statuses->count() > 0)
                <button class="sekunder tcetak" onclick="ringkasTabel(this)">Panjang/Pendekkan Tampilan Tabel</button>
            @endif
        </div>
    </details>

    <script>
        (async () => {
            while (!window.aplikasiSiap) {
                await new Promise((resolve, reject) =>
                    setTimeout(resolve, 1000));
            }

            formatTabel('#tabel_status thead th', '#tabel_status tbody tr');
        })();
    </script>
@endisset
