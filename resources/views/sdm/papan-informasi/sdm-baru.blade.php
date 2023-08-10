@isset($barus)
<details class="kartu">
    <summary>SDM Baru Selama 40 Hari Terakhir : {{number_format($barus->count(), 0, ',','.')}} Personil</summary>

    <b><i><small>Jumlah SDM : Organik = {{number_format($jumlahOrganik, 0, ',', '.')}} Personil | Outsource =
                {{number_format($jumlahOS, 0, ',', '.')}} Personil | Belum Ditempatkan =
                {{number_format($belumDitempatkan, 0, ',', '.')}} Personil.</small></i></b>

    <div id="tabel_sdm_baru_sematan" class="scroll-margin"></div>

    <div id="tabel_sdm_baru" class="kartu">
        <span class="biru">Biru</span> : Outsource. <span class="oranye">Oranye</span> : Belum Ditempatkan.
        <div class="data ringkas">
            <table class="tabel">
                <thead>
                    <tr>
                        <th></th>
                        <th>No</th>
                        <th>Identitas</th>
                        <th>Baru</th>
                        <th>Lainnya</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($barus as $no => $baru)
                    <tr @class([ 'biru'=> $strRangka->contains($baru->penempatan_kontrak, 'OS-'), 'oranye' =>
                        !$baru->penempatan_kontrak ])>
                        <th>
                            <div class="pil-aksi">
                                <button id="{{'aksi_sdm_baru_baris_' . $loop->iteration}}" title="Pilih Tindakan">
                                    <svg viewbox="0 0 24 24">
                                        <use href="#ikonmenuvert">
                                        </use>
                                    </svg>
                                </button>

                                <div class="aksi">
                                    @if($baru->penempatan_uuid)
                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#tabel_sdm_baru_sematan"
                                        href="{{ $urlRangka->route('sdm.penempatan.lihat', ['uuid' => $baru->penempatan_uuid]) }}"
                                        title="Lihat Data Penempatan">Lihat Penempatan</a>

                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#tabel_sdm_baru_sematan"
                                        href="{{ $urlRangka->route('sdm.penempatan.ubah', ['uuid' => $baru->penempatan_uuid]) }}"
                                        title="Ubah Data Penempatan">Ubah Penempatan</a>

                                    @else
                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#tabel_sdm_baru_sematan"
                                        href="{{ $urlRangka->route('sdm.penempatan.tambah', ['uuid' => $baru->sdm_uuid]) }}"
                                        title="Tambah Data Penempatan">Tambah Penempatan</a>
                                    @endif
                                </div>
                            </div>
                        </th>

                        <td>{{ $loop->iteration }}</td>

                        <td>
                            <a class="isi-xhr taut-akun"
                                href="{{ $urlRangka->route('sdm.akun', ['uuid' => $baru->sdm_uuid]) }}">
                                <img @class(['akun', 'svg'=> !$storageRangka->exists('sdm/foto-profil/' .
                                $baru->sdm_no_absen .
                                '.webp')]) src="{{ $storageRangka->exists('sdm/foto-profil/' . $baru->sdm_no_absen .
                                '.webp') ?
                                $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' => $baru->sdm_no_absen
                                . '.webp' . '?'
                                . filemtime($appRangka->storagePath('app/sdm/foto-profil/' . $baru->sdm_no_absen .
                                '.webp')), false]) :
                                $urlRangka->asset($mixRangka('/images/blank.webp')) }}" alt="{{
                                $baru->sdm_nama ?? 'foto akun' }}" title="{{ $baru->sdm_nama ?? 'foto akun' }}"
                                loading="lazy">
                            </a>

                            {{ $baru->sdm_no_absen }}<br />

                            {{ $baru->sdm_nama }}
                        </td>

                        <td>
                            {{ strtoupper($dateRangka->make($baru->sdm_tgl_gabung)?->translatedFormat('d F Y')) }}
                            <br />
                            <u>
                                <a class="isi-xhr"
                                    href="{{ $urlRangka->route('sdm.penempatan.riwayat', ['kata_kunci' => $baru->sdm_no_ktp]) }}">{{
                                    $baru->sdm_no_ktp }}</a></u><br />
                            No Permintaan : <u><a class="isi-xhr"
                                    href="{{ $urlRangka->route('sdm.permintaan-tambah-sdm.data', ['kata_kunci' => $baru->sdm_no_permintaan]) }}"
                                    title="No Permintaan SDM">{{
                                    $baru->sdm_no_permintaan }}</a>
                            </u>
                        </td>

                        <td>
                            {{ $baru->penempatan_lokasi }} <br />
                            {{ $baru->penempatan_posisi }} <br />
                            {{ $baru->penempatan_kontrak }}
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

        @if ($barus->count() > 0)
        <button class="sekunder tcetak" onclick="ringkasTabel(this)">Panjang/Pendekkan Tampilan Tabel</button>

        @if ($belumDitempatkan > 0)
        <a class="isi-xhr utama" href="{{ $urlRangka->route('sdm.penempatan.data-baru') }}">BELUM
            DITEMPATKAN</a>
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
        
        formatTabel('#tabel_sdm_baru thead th', '#tabel_sdm_baru tbody tr');
    })();
</script>
@endisset