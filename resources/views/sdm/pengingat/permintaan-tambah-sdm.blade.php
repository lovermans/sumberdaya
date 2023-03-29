@isset($perminSDMS)
<details class="kartu">
    <summary>Permintaan Tambah SDM Belum Terpenuhi : {{number_format($perminSDMS->count(), 0, ',', '.')}} Permintaan, Total :
        {{number_format($perminSDMS->sum('tambahsdm_jumlah') - $perminSDMS->sum('tambahsdm_terpenuhi'), 0, ',', '.')}}
        Personil</summary>

    <div id="tabel_perminSDM_sematan" class="scroll-margin"></div>
    
    <div id="tabel_perminSDM" class="kartu">
        <div class="data ringkas">
            <table class="tabel">
                <thead>
                    <tr>
                        <th></th>
                        <th>No</th>
                        <th>Permintaan</th>
                        <th>Rincian</th>
                        <th>Lainnya</th>
                    </tr>
                </thead>
                
                <tbody>
                    @forelse ($perminSDMS as $no => $perminSDM)
                    <tr>
                        <th>
                            <div class="pil-aksi">
                                <button>
                                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#menuvert' }}"
                                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                                    </svg>
                                </button>
                                
                                <div class="aksi">
                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#tabel_perminSDM_sematan"
                                        href="{{ $urlRangka->route('sdm.permintaan-tambah-sdm.lihat', ['uuid' => $perminSDM->tambahsdm_uuid], false) }}"
                                        title="Lihat Permintaan SDM">Lihat Data</a>
                                    
                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#tabel_perminSDM_sematan"
                                        href="{{ $urlRangka->route('sdm.permintaan-tambah-sdm.ubah', ['uuid' => $perminSDM->tambahsdm_uuid], false) }}"
                                        title="Ubah Permintaan SDM">Ubah Data</a>
                                </div>
                            </div>
                        </th>
                        
                        <td>{{ $loop->iteration }}</td>
                        
                        <td>
                            <a class="isi-xhr taut-akun" href="{{ $urlRangka->route('akun', ['uuid' => $perminSDM->sdm_uuid], false) }}">
                                <img @class(['akun', 'svg'=> !$storageRangka->exists('sdm/foto-profil/' .
                                $perminSDM->tambahsdm_sdm_id . '.webp')]) src="{{ $storageRangka->exists('sdm/foto-profil/' .
                                $perminSDM->tambahsdm_sdm_id . '.webp') ? $urlRangka->route('sdm.tautan-foto-profil',
                                ['berkas_foto_profil' => $perminSDM->tambahsdm_sdm_id . '.webp' . '?' .
                                filemtime($appRangka->storagePath('app/sdm/foto-profil/' . $perminSDM->tambahsdm_sdm_id . '.webp')), false]) :
                                $mixRangka('/ikon.svg') . '#akun' }}" alt="{{
                                $perminSDM->sdm_nama ?? 'foto akun' }}" title="{{ $perminSDM->sdm_nama ?? 'foto akun' }}"
                                loading="lazy">
                            </a>
                            <b>Nomor</b> : {{$perminSDM->tambahsdm_no}}<br />
                            <b>Pemohon</b> : {{$perminSDM->tambahsdm_sdm_id}} - {{$perminSDM->sdm_nama}}<br />
                            <b>Diusulkan</b> : {{
                            strtoupper($dateRangka->make($perminSDM->tambahsdm_tgl_diusulkan)?->translatedFormat('d F Y'))
                            }}<br />
                            <b>Dibutuhkan</b> : {{
                            strtoupper($dateRangka->make($perminSDM->tambahsdm_tgl_dibutuhkan)?->translatedFormat('d F Y'))
                            }}
                        </td>
                        
                        <td>
                            <b>Penempatan</b> : {{$perminSDM->tambahsdm_penempatan}}<br />
                            <b>Posisi</b> : {{$perminSDM->tambahsdm_posisi}}<br />
                            <b>Jml Kebutuhan</b> : {{$perminSDM->tambahsdm_jumlah}}<br />
                            <b>Jml Terpenuhi</b> : <u><a class="isi-xhr"
                                    href="{{ $urlRangka->route('sdm.penempatan.riwayat', ['kata_kunci' => $perminSDM->tambahsdm_no], false) }}">{{
                                    $perminSDM->tambahsdm_terpenuhi }}</a></u><br />
                            <b>Pemenuhan Terbaru</b> : {{ strtoupper($dateRangka->make($perminSDM->pemenuhan_terkini)?->translatedFormat('d F Y')) }}
                        </td>
                        
                        <td>
                            <b>Alasan</b> : {!! nl2br($perminSDM->tambahsdm_alasan) !!}<br />
                            <b>Keterangan</b> : {!! nl2br($perminSDM->tambahsdm_keterangan) !!}
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

        <a class="isi-xhr utama" href="{{ $urlRangka->route('sdm.permintaan-tambah-sdm.data', [], false) }}">SELENGKAPNYA</a>
    </div>
</details>

<script>
    formatTabel('#tabel_perminSDM thead th', '#tabel_perminSDM tbody tr');
</script>
@endisset