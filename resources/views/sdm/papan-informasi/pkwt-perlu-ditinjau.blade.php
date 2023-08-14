@isset($kontraks)
<details class="kartu">
    <summary>Perjanjian Kerja Perlu Ditindaklanjuti : {{number_format($kontraks->count(), 0, ',','.')}} Personil
    </summary>

    <div id="tabel_kontrak_sematan" class="scroll-margin"></div>

    <div id="tabel_kontrak" class="kartu">
        <span class="biru">Biru</span> : Akan Habis = {{number_format($jmlAkanHabis, 0, ',','.')}} Personil. <span
            class="oranye">Oranye</span> : Kadaluarsa = {{number_format($jmlKadaluarsa, 0, ',','.')}} Personil.

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
                    @forelse ($kontraks as $no => $kontrak)
                    <tr @class(['oranye'=> $kontrak->penempatan_selesai <= $app->date->today(), 'biru'=>
                            ($kontrak->penempatan_selesai <= $app->date->today()->addDay(40))])>
                                <th>
                                    <div class="pil-aksi">
                                        <button id="{{'aksi_pkwt_baris_' . $loop->iteration}}" title="Pilih Tindakan">
                                            <svg viewbox="0 0 24 24">
                                                <use href="#ikonmenuvert"></use>
                                            </svg>
                                        </button>

                                        <div class="aksi">
                                            <a class="isi-xhr" data-rekam="false" data-tujuan="#tabel_kontrak_sematan"
                                                href="{{ $app->url->route('sdm.penempatan.lihat', ['uuid' => $kontrak->penempatan_uuid]) }}"
                                                title="Lihat Data Penempatan">Lihat Penempatan</a>

                                            <a class="isi-xhr" data-rekam="false" data-tujuan="#tabel_kontrak_sematan"
                                                href="{{ $app->url->route('sdm.penempatan.ubah', ['uuid' => $kontrak->penempatan_uuid]) }}"
                                                title="Ubah Data Penempatan">Ubah Penempatan</a>

                                            <a class="isi-xhr" data-rekam="false" data-tujuan="#tabel_kontrak_sematan"
                                                href="{{ $app->url->route('sdm.penempatan.tambah', ['uuid' => $kontrak->sdm_uuid]) }}"
                                                title="Tambah Data Penempatan">Tambah Penempatan</a>
                                        </div>
                                    </div>
                                </th>

                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <a class="isi-xhr taut-akun"
                                        href="{{ $app->url->route('sdm.akun', ['uuid' => $kontrak->sdm_uuid]) }}">
                                        <img @class(['akun', 'svg'=> !$app->filesystem->exists('sdm/foto-profil/'
                                        .
                                        $kontrak->sdm_no_absen . '.webp')]) src="{{
                                        $app->filesystem->exists('sdm/foto-profil/' .
                                        $kontrak->sdm_no_absen . '.webp') ?
                                        $app->url->route('sdm.tautan-foto-profil',
                                        ['berkas_foto_profil' => $kontrak->sdm_no_absen . '.webp' . '?' .
                                        filemtime($app->storagePath('app/sdm/foto-profil/' .
                                        $kontrak->sdm_no_absen . '.webp')), false])
                                        :
                                        $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/blank.webp'))
                                        }}" alt="{{
                                        $kontrak->sdm_nama ?? 'foto akun' }}" title="{{ $kontrak->sdm_nama ??
                                        'foto
                                        akun'
                                        }}" loading="lazy">
                                    </a>

                                    {{ $kontrak->sdm_no_absen }}<br />

                                    {{ $kontrak->sdm_nama }}
                                </td>

                                <td>
                                    {{
                                    strtoupper($app->date->make($kontrak->penempatan_mulai)?->translatedFormat('d
                                    F
                                    Y')) }}
                                    s.d
                                    {{
                                    strtoupper($app->date->make($kontrak->penempatan_selesai)?->translatedFormat('d
                                    F Y'))
                                    }} <br />
                                    {{ $kontrak->penempatan_kontrak }} Ke : {{ $kontrak->penempatan_ke }}
                                </td>

                                <td>
                                    {{ $kontrak->penempatan_lokasi }} <br />
                                    {{ $kontrak->penempatan_posisi }}
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

        @if ($kontraks->count() > 0)
        <button class="sekunder tcetak" onclick="ringkasTabel(this)">Panjang/Pendekkan Tampilan Tabel</button>

        @if ($jmlAkanHabis > 0)
        <a class="isi-xhr utama" href="{{ $app->url->route('sdm.penempatan.data-akanhabis') }}">AKAN
            HABIS</a>&nbsp;
        @endif

        @if ($jmlKadaluarsa > 0)
        <a class="isi-xhr utama" href="{{ $app->url->route('sdm.penempatan.data-kadaluarsa') }}">KADALUARSA</a>
        @endif
        @endif
    </div>
</details>

<script>
    (async() => {
        while(!window.aplikasiSiap) {
            await new Promise((resolve,reject) =>
            setTimeout(resolve, 1000));
        }
        
        formatTabel('#tabel_kontrak thead th', '#tabel_kontrak tbody tr');
    })();
</script>
@endisset