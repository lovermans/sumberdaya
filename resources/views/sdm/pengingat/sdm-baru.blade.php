@isset($barus)
<details class="kartu">
    <summary>SDM Baru Selama 40 Hari Terakhir : {{number_format($barus->count(), 0, ',','.')}} Personil</summary>
    <div id="tabel_sdm_baru_sematan"></div>
    <div id="tabel_sdm_baru" class="kartu">
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
                    <tr>
                        <th>
                            <div class="pil-aksi">
                                <button>
                                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="{{ $urlRangka->asset($mixRangka('/ikon.svg')) . '#menuvert' }}"
                                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
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
                            <a class="isi-xhr taut-akun" href="{{ $urlRangka->route('akun', ['uuid' => $baru->sdm_uuid]) }}">
                                <img @class(['akun', 'svg'=> !$storageRangka->exists('sdm/foto-profil/' . $baru->sdm_no_absen .
                                '.webp')]) src="{{ $storageRangka->exists('sdm/foto-profil/' . $baru->sdm_no_absen . '.webp') ?
                                $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' => $baru->sdm_no_absen . '.webp' . '?'
                                . filemtime($appRangka->storagePath('app/sdm/foto-profil/' . $baru->sdm_no_absen . '.webp'))]) :
                                $urlRangka->asset($mixRangka('/ikon.svg')) . '#akun' }}" alt="{{
                                $baru->sdm_nama ?? 'foto akun' }}" title="{{ $baru->sdm_nama ?? 'foto akun' }}"
                                loading="lazy">
                            </a>
                            {{ $baru->sdm_no_absen }}<br/>
                            {{ $baru->sdm_nama }}
                        </td>
                        <td>
                            {{ strtoupper($dateRangka->make($baru->sdm_tgl_gabung)?->translatedFormat('d F Y')) }} <br />
                            <u><a class="isi-xhr" href="{{ $urlRangka->route('sdm.penempatan.riwayat', ['kata_kunci' => $baru->sdm_no_ktp]) }}">{{ $baru->sdm_no_ktp }}</a></u><br />
                            No Permintaan : <u><a class="isi-xhr"
                                    href="{{ $urlRangka->route('sdm.permintaan-tambah-sdm.data', ['kata_kunci' => $baru->sdm_no_permintaan]) }}">{{
                                    $baru->sdm_no_permintaan }}</a></u>
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
        <button class="sekunder tcetak" onclick="ringkasTabel(this)" style="margin:0.5em 0">Panjang/Pendekkan Tampilan
            Tabel</button>
    </div>
</details>
<script>
    formatTabel('#tabel_sdm_baru thead th', '#tabel_sdm_baru tbody tr');
</script>
@endisset