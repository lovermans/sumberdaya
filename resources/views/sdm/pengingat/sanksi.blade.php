@isset($sanksis)
<details class="kartu">
    <summary>Sanksi Aktif : {{number_format($sanksis->count(), 0, ',','.')}} Personil</summary>

    <b><i><small>Jumlah SDM : Organik = {{number_format($jumlahOrganik, 0, ',', '.')}} Personil | Outsource =
                {{number_format($jumlahOS, 0, ',', '.')}} Personil.</small></i></b>

    <div id="tabel_sanksi_sdm_sematan" class="scroll-margin"></div>

    <div id="tabel_sanksi_sdm" class="kartu">
        <span class="biru">Biru</span> : Outsource.
        <div class="data ringkas">
            <table class="tabel">
                <thead>
                    <tr>
                        <th></th>
                        <th>No</th>
                        <th>Identitas</th>
                        <th>Sanksi</th>
                        <th>Laporan</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sanksis as $no => $sanksi)
                    <tr @class([ 'biru'=> $strRangka->contains($sanksi->langgar_tkontrak, 'OS-') ])>
                        <th>
                            <div class="pil-aksi">
                                <button id="{{'aksi_sanksi_baris_' . $loop->iteration}}" title="Pilih Tindakan">
                                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#menuvert' }}"
                                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                                    </svg>
                                </button>

                                <div class="aksi">
                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#tabel_sanksi_sdm_sematan"
                                        href="{{ $urlRangka->route('sdm.sanksi.lihat', ['uuid' => $sanksi->sanksi_uuid], false) }}"
                                        title="Lihat/Ubah Sanksi">Lihat/Ubah Sanksi</a>
                                </div>
                            </div>
                        </th>

                        <td>{{ $loop->iteration }}</td>

                        <td>
                            <div @class(['merah'=> $sanksi->langgar_tsdm_tgl_berhenti])>
                                <a class="isi-xhr taut-akun"
                                    href="{{ $urlRangka->route('sdm.akun', ['uuid' => $sanksi->langgar_tsdm_uuid], false) }}">
                                    <img @class(['akun', 'svg'=> !$storageRangka->exists('sdm/foto-profil/' .
                                    $sanksi->sanksi_no_absen . '.webp')]) src="{{
                                    $storageRangka->exists('sdm/foto-profil/' . $sanksi->sanksi_no_absen .
                                    '.webp')
                                    ? $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' =>
                                    $sanksi->sanksi_no_absen . '.webp' . '?' .
                                    filemtime($appRangka->storagePath('app/sdm/foto-profil/' .
                                    $sanksi->sanksi_no_absen . '.webp'))], false) :
                                    $mixRangka('/ikon.svg') . '#akun' }}" alt="{{
                                    $sanksi->langgar_tsdm_nama ?? 'foto akun' }}" title="{{
                                    $sanksi->langgar_tsdm_nama
                                    ?? 'foto akun' }}" loading="lazy">
                                </a>
                                {{$sanksi->sanksi_no_absen}} - {{$sanksi->langgar_tsdm_nama}} <br />
                                {{$sanksi->langgar_tlokasi}} {{$sanksi->langgar_tkontrak}} -
                                {{$sanksi->langgar_tposisi}} {{ $sanksi->langgar_tsdm_tgl_berhenti ? '(NON-AKTIF)'
                                :
                                '' }}
                            </div>
                        </td>

                        <td>
                            <b>Sanksi</b> : {{$sanksi->sanksi_jenis}}<br />
                            <b>Berlaku</b> : {{
                            strtoupper($dateRangka->make($sanksi->sanksi_mulai)?->translatedFormat('d F Y'))
                            }} s.d {{
                            strtoupper($dateRangka->make($sanksi->sanksi_selesai)?->translatedFormat('d F Y'))
                            }}<br />
                            <b>Tambahan</b> : {!! nl2br($sanksi->sanksi_tambahan) !!}
                        </td>

                        <td>
                            <b>Nomor</b> : <u><a class="isi-xhr"
                                    href="{{ $urlRangka->route('sdm.pelanggaran.data', ['kata_kunci' => $sanksi->sanksi_lap_no], false) }}"
                                    aria-label="Lap Pelanggaran SDM No {{ $sanksi->sanksi_lap_no }}">{{
                                    $sanksi->sanksi_lap_no }}</a></u><br />
                            <b>Tanggal</b> : {{
                            strtoupper($dateRangka->make($sanksi->langgar_tanggal)?->translatedFormat('d F Y'))
                            }}<br />
                            <b>Aduan</b> : {!! nl2br($sanksi->langgar_isi) !!}
                            @if ($sanksi->sanksi_lap_no)
                            <div @class(['merah'=> $sanksi->langgar_psdm_tgl_berhenti])>
                                <b><i><u>Pelapor</u></i></b> :<br />
                                <a class="isi-xhr taut-akun"
                                    href="{{ $urlRangka->route('sdm.akun', ['uuid' => $sanksi->langgar_psdm_uuid], false) }}">
                                    <img @class(['akun', 'svg'=> !$storageRangka->exists('sdm/foto-profil/' .
                                    $sanksi->langgar_pelapor . '.webp')]) src="{{
                                    $storageRangka->exists('sdm/foto-profil/' . $sanksi->langgar_pelapor .
                                    '.webp') ?
                                    $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' =>
                                    $sanksi->langgar_pelapor . '.webp' . '?' .
                                    filemtime($appRangka->storagePath('app/sdm/foto-profil/' .
                                    $sanksi->langgar_pelapor . '.webp'))], false) :
                                    $mixRangka('/ikon.svg') . '#akun' }}" alt="{{
                                    $sanksi->langgar_psdm_nama ?? 'foto akun' }}" title="{{
                                    $sanksi->langgar_psdm_nama
                                    ?? 'foto akun' }}" loading="lazy">
                                </a>
                                {{$sanksi->langgar_pelapor}} - {{$sanksi->langgar_psdm_nama}} <br />
                                {{$sanksi->langgar_plokasi}} {{$sanksi->langgar_pkontrak}} -
                                {{$sanksi->langgar_pposisi}} {{ $sanksi->langgar_psdm_tgl_berhenti ? '(NON-AKTIF)'
                                :
                                '' }}
                            </div>
                            @endif
                        </td>
                        <td>
                            {!! nl2br($sanksi->sanksi_keterangan) !!}
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
        
        formatTabel('#tabel_sanksi_sdm thead th', '#tabel_sanksi_sdm tbody tr');
    })();
</script>
@endisset