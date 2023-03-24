@isset($statuses)
<details class="kartu">
    <summary>Perubahan Status SDM 40 Hari Terakhir : {{number_format($statuses->count(), 0, ',','.')}} Personil</summary>
    
    <div id="tabel_status_sematan"></div>
    
    <div id="tabel_status" class="kartu">
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
                    <tr @class(['biru'=> $strRangka->contains($status->penempatan_kontrak, 'OS-')])>
                        <th>
                            <div class="pil-aksi">
                                <button>
                                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#menuvert' }}"
                                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                                    </svg>
                                </button>
                                
                                <div class="aksi">
                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#tabel_status_sematan" href="{{ $urlRangka->route('sdm.penempatan.lihat', ['uuid' => $status->penempatan_uuid], false) }}" title="Lihat Data Penempatan">Lihat Penempatan</a>
                                    
                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#tabel_status_sematan" href="{{ $urlRangka->route('sdm.penempatan.ubah', ['uuid' => $status->penempatan_uuid], false) }}" title="Ubah Data Penempatan">Ubah Penempatan</a>
                                </div>
                            </div>
                        </th>
                        
                        <td>{{ $loop->iteration }}</td>
                        
                        <td>
                            <a class="isi-xhr taut-akun" href="{{ $urlRangka->route('akun', ['uuid' => $status->sdm_uuid], false) }}">
                                <img @class(['akun', 'svg'=> !$storageRangka->exists('sdm/foto-profil/' .
                                $status->sdm_no_absen . '.webp')]) src="{{ $storageRangka->exists('sdm/foto-profil/' .
                                $status->sdm_no_absen . '.webp') ? $urlRangka->route('sdm.tautan-foto-profil',
                                ['berkas_foto_profil' => $status->sdm_no_absen . '.webp' . '?' .
                                filemtime($appRangka->storagePath('app/sdm/foto-profil/' . $status->sdm_no_absen . '.webp')), false])
                                : $mixRangka('/ikon.svg') . '#akun' }}" alt="{{
                                $status->sdm_nama ?? 'foto akun' }}" title="{{ $status->sdm_nama ?? 'foto akun'
                                }}" loading="lazy">
                            </a>
                            
                            {{ $status->sdm_no_absen }}<br/>
                            
                            {{ $status->sdm_nama }}
                        </td>
                        
                        <td>
                            {{ strtoupper($dateRangka->make($status->penempatan_mulai)?->translatedFormat('d F Y')) }}
                            s.d
                            {{ strtoupper($dateRangka->make($status->penempatan_selesai)?->translatedFormat('d F Y'))
                            }} <br />
                            {{ $status->penempatan_kontrak }} Ke : {{ $status->penempatan_ke }}
                        </td>
                        
                        <td>
                            {{ $status->penempatan_lokasi }} <br />
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

        <button class="sekunder tcetak" onclick="ringkasTabel(this)">Panjang/Pendekkan Tampilan Tabel</button>
    </div>
</details>

<script>
    formatTabel('#tabel_status thead th', '#tabel_status tbody tr');
</script>
@endisset