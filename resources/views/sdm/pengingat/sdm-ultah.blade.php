@isset($ulangTahuns)
<details class="kartu">
    <summary>Hari Lahir SDM Dalam Waktu Dekat : {{number_format($ulangTahuns->count(), 0, ',','.')}} Personil</summary>

    <div id="tabel_ultah_sematan" class="scroll-margin"></div>

    <div id="tabel_ultah" class="kartu">
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
                    <tr>
                        <td>{{ $loop->iteration }}</td>

                        <td>
                            <a class="isi-xhr taut-akun" href="{{ $urlRangka->route('akun', ['uuid' => $ultah->sdm_uuid], false) }}">
                                <img @class(['akun', 'svg'=> !$storageRangka->exists('sdm/foto-profil/' . $ultah->sdm_no_absen .
                                '.webp')]) src="{{ $storageRangka->exists('sdm/foto-profil/' . $ultah->sdm_no_absen . '.webp') ?
                                $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' => $ultah->sdm_no_absen . '.webp' .
                                '?' . filemtime($appRangka->storagePath('app/sdm/foto-profil/' . $ultah->sdm_no_absen . '.webp')), false]) :
                                $mixRangka('/ikon.svg') . '#akun' }}" alt="{{
                                $ultah->sdm_nama ?? 'foto akun' }}" title="{{ $ultah->sdm_nama ?? 'foto akun' }}"
                                loading="lazy">
                            </a>

                            {{ $ultah->sdm_no_absen }}<br/>

                            {{ $ultah->sdm_nama }}
                        </td>

                        <td>{{ strtoupper($dateRangka->make($ultah->sdm_tgl_lahir)?->translatedFormat('d F')) }}</td>

                        <td>
                            {{ $ultah->penempatan_lokasi }} <br />
                            {{ $ultah->penempatan_kontrak }} <br />
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

        <button class="sekunder tcetak" onclick="ringkasTabel(this)">Panjang/Pendekkan Tampilan Tabel</button>
    </div>
</details>

<script>
    (async() => {
        while(!window.aplikasiSiap) {
            await new Promise((resolve,reject) =>
            setTimeout(resolve, 1000));
        }
        
        formatTabel('#tabel_ultah thead th', '#tabel_ultah tbody tr');
    })();
</script>
@endisset