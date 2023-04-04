@isset($barus)
<details class="kartu">
    <summary>SDM Baru Selama 40 Hari Terakhir : {{number_format($barus->count(), 0, ',','.')}} Personil</summary>
    
    <div id="tabel_sdm_baru_sematan" class="scroll-margin"></div>
    
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
                                <button id="{{'aksi_sdm_baru_baris_' . $loop->iteration}}" title="Pilih Tindakan">
                                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#menuvert' }}"
                                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                                    </svg>
                                </button>
                                
                                <div class="aksi">
                                    @if($baru->penempatan_uuid)
                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#tabel_sdm_baru_sematan"
                                        href="{{ $urlRangka->route('sdm.penempatan.lihat', ['uuid' => $baru->penempatan_uuid], false) }}"
                                        title="Lihat Data Penempatan">Lihat Penempatan</a>

                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#tabel_sdm_baru_sematan"
                                        href="{{ $urlRangka->route('sdm.penempatan.ubah', ['uuid' => $baru->penempatan_uuid], false) }}"
                                        title="Ubah Data Penempatan">Ubah Penempatan</a>
                                    
                                    @else
                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#tabel_sdm_baru_sematan"
                                        href="{{ $urlRangka->route('sdm.penempatan.tambah', ['uuid' => $baru->sdm_uuid], false) }}"
                                        title="Tambah Data Penempatan">Tambah Penempatan</a>
                                    @endif
                                </div>
                            </div>
                        </th>

                        <td>{{ $loop->iteration }}</td>

                        <td>
                            <a class="isi-xhr taut-akun" href="{{ $urlRangka->route('akun', ['uuid' => $baru->sdm_uuid], false) }}">
                                <img @class(['akun', 'svg'=> !$storageRangka->exists('sdm/foto-profil/' . $baru->sdm_no_absen .
                                '.webp')]) src="{{ $storageRangka->exists('sdm/foto-profil/' . $baru->sdm_no_absen . '.webp') ?
                                $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' => $baru->sdm_no_absen . '.webp' . '?'
                                . filemtime($appRangka->storagePath('app/sdm/foto-profil/' . $baru->sdm_no_absen . '.webp')), false]) :
                                $mixRangka('/ikon.svg') . '#akun' }}" alt="{{
                                $baru->sdm_nama ?? 'foto akun' }}" title="{{ $baru->sdm_nama ?? 'foto akun' }}"
                                loading="lazy">
                            </a>

                            {{ $baru->sdm_no_absen }}<br/>

                            {{ $baru->sdm_nama }}
                        </td>

                        <td>
                            {{ strtoupper($dateRangka->make($baru->sdm_tgl_gabung)?->translatedFormat('d F Y')) }} <br />
                            <u>
                                <a class="isi-xhr" href="{{ $urlRangka->route('sdm.penempatan.riwayat', ['kata_kunci' => $baru->sdm_no_ktp], false) }}">{{ $baru->sdm_no_ktp }}</a></u><br />
                            No Permintaan : <u><a class="isi-xhr"
                                    href="{{ $urlRangka->route('sdm.permintaan-tambah-sdm.data', ['kata_kunci' => $baru->sdm_no_permintaan], false) }}" title="No Permintaan SDM">{{
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
        
        <button class="sekunder tcetak" onclick="ringkasTabel(this)">Panjang/Pendekkan Tampilan Tabel</button>
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