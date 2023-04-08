@isset($berhentis)
<details class="kartu">
    <summary>SDM Keluar Selama 40 Hari Terakhir : {{number_format($berhentis->count(), 0, ',','.')}} Personil</summary>

    <div id="tabel_berhenti_sematan" class="scroll-margin"></div>

    <div id="tabel_berhenti" class="kartu">
        <div class="data ringkas">
            <table class="tabel">
                <thead>
                    <tr>
                        <th></th>
                        <th>No</th>
                        <th>Identitas</th>
                        <th>Berhenti</th>
                        <th>Lainnya</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($berhentis as $no => $henti)
                    <tr>
                        <th>
                            <div class="pil-aksi">
                                <button id="{{'aksi_sdm_keluar_baris_' . $loop->iteration}}" title="Pilih Tindakan">
                                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#menuvert' }}"
                                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                                    </svg>
                                </button>
                                
                                <div class="aksi">
                                    @if($henti->penempatan_uuid)
                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#tabel_berhenti_sematan"
                                        href="{{ $urlRangka->route('sdm.penempatan.lihat', ['uuid' => $henti->penempatan_uuid], false) }}"
                                        title="Lihat Data Penempatan">Lihat Penempatan</a>

                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#tabel_berhenti_sematan"
                                        href="{{ $urlRangka->route('sdm.penempatan.ubah', ['uuid' => $henti->penempatan_uuid], false) }}"
                                        title="Ubah Data Penempatan">Ubah Penempatan</a>
                                    @endif

                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#tabel_berhenti_sematan"
                                        href="{{ $urlRangka->route('sdm.penempatan.tambah', ['uuid' => $henti->sdm_uuid], false) }}"
                                        title="Tambah Data Penempatan">Tambah Penempatan</a>
                                </div>
                            </div>
                        </th>

                        <td>{{ $loop->iteration }}</td>

                        <td>
                            <a class="isi-xhr taut-akun" href="{{ $urlRangka->route('sdm.akun', ['uuid' => $henti->sdm_uuid], false) }}">
                                <img @class(['akun', 'svg'=> !$storageRangka->exists('sdm/foto-profil/' . $henti->sdm_no_absen .
                                '.webp')]) src="{{ $storageRangka->exists('sdm/foto-profil/' . $henti->sdm_no_absen . '.webp') ?
                                $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' => $henti->sdm_no_absen . '.webp' .
                                '?' . filemtime($appRangka->storagePath('app/sdm/foto-profil/' . $henti->sdm_no_absen . '.webp')), false]) :
                                $mixRangka('/ikon.svg') . '#akun' }}" alt="{{
                                $henti->sdm_nama ?? 'foto akun' }}" title="{{ $henti->sdm_nama ?? 'foto akun' }}"
                                loading="lazy">
                            </a>

                            {{ $henti->sdm_no_absen }}<br/>

                            {{ $henti->sdm_nama }}
                        </td>
                        
                        <td>
                            {{ strtoupper($dateRangka->make($henti->sdm_tgl_berhenti)?->translatedFormat('d F Y')) }} <br />
                            {{ $henti->sdm_jenis_berhenti }}<br />
                            {{ $henti->sdm_ket_berhenti }}
                        </td>

                        <td>
                            {{ $henti->penempatan_lokasi }} <br />
                            {{ $henti->penempatan_posisi }} <br />
                            {{ $henti->penempatan_kontrak }}
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
        
        formatTabel('#tabel_berhenti thead th', '#tabel_berhenti tbody tr');
    })();
</script>
@endisset