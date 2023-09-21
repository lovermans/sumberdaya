@isset($ulangTahuns)
    <details class="kartu">
        <summary>
            Hari Lahir SDM Dalam Waktu Dekat : {{ number_format($ulangTahuns->count(), 0, ',', '.') }} Personil
        </summary>

        <b>
            <i>
                <small>
                    Jumlah SDM : Organik = {{ number_format($jumlahOrganik, 0, ',', '.') }} Personil | Outsource =
                    {{ number_format($jumlahOS, 0, ',', '.') }} Personil.
                </small>
            </i>
        </b>

        <div class="scroll-margin" id="tabel_ultah_sematan"></div>

        <div class="kartu" id="tabel_ultah">
            <span class="biru">Biru</span> : Outsource.
            <div class="data ringkas">
                <table class="tabel">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Identitas</th>
                            <th>Lahir</th>
                            <th>Penempatan</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($ulangTahuns as $no => $ultah)
                            <tr @class(['biru' => str()->contains($ultah->penempatan_kontrak, 'OS-')])>
                                <td>{{ $loop->iteration }}</td>

                                <td class="profil">
                                    <a class="isi-xhr taut-akun" href="{{ $app->url->route('sdm.akun', ['uuid' => $ultah->sdm_uuid]) }}">
                                        <img src="{{ $app->filesystem->exists('sdm/foto-profil/' . $ultah->sdm_no_absen . '.webp')
                                            ? $app->url->route('sdm.tautan-foto-profil', [
                                                'berkas_foto_profil' => $ultah->sdm_no_absen . '.webp' . '?id=' . filemtime($app->storagePath('app/sdm/foto-profil/' . $ultah->sdm_no_absen . '.webp')),
                                                false,
                                            ])
                                            : $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/blank.webp')) }}"
                                            title="{{ $ultah->sdm_nama ?? 'foto akun' }}" alt="{{ $ultah->sdm_nama ?? 'foto akun' }}" @class([
                                                'akun',
                                                'svg' => !$app->filesystem->exists(
                                                    'sdm/foto-profil/' . $ultah->sdm_no_absen . '.webp'),
                                            ])
                                            loading="lazy">

                                        <small>{{ $ultah->sdm_no_absen }} : {{ $ultah->sdm_nama }}</small>
                                    </a>
                                </td>

                                <td>{{ strtoupper($app->date->make($ultah->sdm_tgl_lahir)?->translatedFormat('d F')) }}</td>

                                <td>
                                    {{ $ultah->penempatan_lokasi }} <br>
                                    {{ $ultah->penempatan_kontrak }} <br>
                                    {{ $ultah->penempatan_posisi }}
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <th></th>
                                <td colspan="5">Tidak Ada Data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <button class="sekunder tcetak ringkas-tabel">Panjang/Pendekkan Tampilan Tabel</button>
        </div>
    </details>

    <script nonce="{{ $app->request->session()->get('sesiNonce') }}">
        (async () => {
            while (!window.aplikasiSiap) {
                await new Promise((resolve, reject) =>
                    setTimeout(resolve, 1000));
            }

            formatTabel('#tabel_ultah thead th', '#tabel_ultah tbody tr');
        })();
    </script>
@endisset
