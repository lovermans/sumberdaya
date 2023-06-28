@isset($pelanggarans)
<details class="kartu">
    <summary>Laporan Pelanggaran Yang Perlu Diproses : {{number_format($pelanggarans->count(), 0, ',','.')}} Laporan
    </summary>

    <div id="tabel_pelanggaranSDM_sematan" class="scroll-margin"></div>

    <div id="tabel_pelanggaranSDM" class="kartu">
        <div class="data ringkas">
            <table class="tabel">
                <thead>
                    <tr>
                        <th></th>
                        <th>No</th>
                        <th>Laporan</th>
                        <th>Pelanggaran</th>
                        <th>Sanksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($pelanggarans as $no => $pelanggaran)
                    <tr>
                        <th>
                            <div class="pil-aksi">
                                <button id="{{'aksi_pelanggaran_baris_' . $loop->iteration}}" title="Pilih Tindakan">
                                    <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#menuvert' }}"
                                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                                    </svg>
                                </button>

                                <div class="aksi">
                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#tabel_pelanggaranSDM_sematan"
                                        href="{{ $urlRangka->route('sdm.pelanggaran.lihat', ['uuid' => $pelanggaran->langgar_uuid], false) }}"
                                        title="Tindaklanjuti">Tindaklanjuti</a>
                                </div>
                            </div>
                        </th>

                        <td>{{ $loop->iteration }}</td>

                        <td>
                            <div @class(['merah'=> $pelanggaran->langgar_tsdm_tgl_berhenti])>
                                <b><i><u>Terlapor</u></i></b> :<br />
                                <a class="isi-xhr taut-akun"
                                    href="{{ $urlRangka->route('sdm.akun', ['uuid' => $pelanggaran->langgar_tsdm_uuid], false) }}">
                                    <img @class(['akun', 'svg'=> !$storageRangka->exists('sdm/foto-profil/' .
                                    $pelanggaran->langgar_no_absen . '.webp')]) src="{{
                                    $storageRangka->exists('sdm/foto-profil/' . $pelanggaran->langgar_no_absen .
                                    '.webp')
                                    ? $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' =>
                                    $pelanggaran->langgar_no_absen . '.webp' . '?' .
                                    filemtime($appRangka->storagePath('app/sdm/foto-profil/' .
                                    $pelanggaran->langgar_no_absen . '.webp'))], false) :
                                    $mixRangka('/ikon.svg') . '#akun' }}" alt="{{
                                    $pelanggaran->langgar_tsdm_nama ?? 'foto akun' }}" title="{{
                                    $pelanggaran->langgar_tsdm_nama
                                    ?? 'foto akun' }}" loading="lazy">
                                </a>
                                {{$pelanggaran->langgar_no_absen}} - {{$pelanggaran->langgar_tsdm_nama}} <br />
                                {{$pelanggaran->langgar_tlokasi}} {{$pelanggaran->langgar_tkontrak}} -
                                {{$pelanggaran->langgar_tposisi}} {{ $pelanggaran->langgar_tsdm_tgl_berhenti ?
                                '(NON-AKTIF)'
                                :
                                '' }}
                            </div>
                            <div @class(['merah'=> $pelanggaran->langgar_psdm_tgl_berhenti])>
                                <b><i><u>Pelapor</u></i></b> :<br />
                                <a class="isi-xhr taut-akun"
                                    href="{{ $urlRangka->route('sdm.akun', ['uuid' => $pelanggaran->langgar_psdm_uuid], false) }}">
                                    <img @class(['akun', 'svg'=> !$storageRangka->exists('sdm/foto-profil/' .
                                    $pelanggaran->langgar_pelapor . '.webp')]) src="{{
                                    $storageRangka->exists('sdm/foto-profil/' . $pelanggaran->langgar_pelapor .
                                    '.webp') ?
                                    $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' =>
                                    $pelanggaran->langgar_pelapor . '.webp' . '?' .
                                    filemtime($appRangka->storagePath('app/sdm/foto-profil/' .
                                    $pelanggaran->langgar_pelapor . '.webp'))], false) :
                                    $mixRangka('/ikon.svg') . '#akun' }}" alt="{{
                                    $pelanggaran->langgar_psdm_nama ?? 'foto akun' }}" title="{{
                                    $pelanggaran->langgar_psdm_nama
                                    ?? 'foto akun' }}" loading="lazy">
                                </a>
                                {{$pelanggaran->langgar_pelapor}} - {{$pelanggaran->langgar_psdm_nama}} <br />
                                {{$pelanggaran->langgar_plokasi}} {{$pelanggaran->langgar_pkontrak}} -
                                {{$pelanggaran->langgar_pposisi}} {{ $pelanggaran->langgar_psdm_tgl_berhenti ?
                                '(NON-AKTIF)'
                                :
                                '' }}
                            </div>
                        </td>

                        <td>
                            <b>Nomor</b> : {{$pelanggaran->langgar_lap_no}}<br />
                            <b>Tanggal</b> : {{
                            strtoupper($dateRangka->make($pelanggaran->langgar_tanggal)?->translatedFormat('d F Y'))
                            }}<br />
                            <b>Aduan</b> : {!! nl2br($pelanggaran->langgar_isi) !!}<br />
                            <b>Keterangan</b> : {!! nl2br($pelanggaran->langgar_keterangan) !!}
                        </td>

                        <td>
                            <b><i><u>Sanksi Aktif Sebelumnya</u></i></b> :<br />
                            <b>No Laporan</b> : {{ $pelanggaran->lap_no_sebelumnya}}<br />
                            <b>Sanksi </b> : {{ $pelanggaran->sanksi_aktif_sebelumnya}}<br />
                            <b>Berakhir pada </b> : {{
                            strtoupper($dateRangka->make($pelanggaran->sanksi_selesai_sebelumnya)?->translatedFormat('d
                            F
                            Y')) }}<br /><br />
                            <b><i><u>Sanksi Diberikan</u></i></b> :<br />
                            <b>Sanksi </b> : {{ $pelanggaran->final_sanksi_jenis}}<br />
                            <b>Tambahan </b> : {{ $pelanggaran->final_sanksi_tambahan}}<br />
                            <b>Mulai </b> : {{
                            strtoupper($dateRangka->make($pelanggaran->final_sanksi_mulai)?->translatedFormat('d F
                            Y'))}}<br />
                            <b>Selesai </b> : {{
                            strtoupper($dateRangka->make($pelanggaran->final_sanksi_selesai)?->translatedFormat('d F
                            Y'))
                            }}<br />
                            <b>Keterangan </b> : {!! nl2br($pelanggaran->final_sanksi_keterangan) !!}
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
        
        formatTabel('#tabel_pelanggaranSDM thead th', '#tabel_pelanggaranSDM tbody tr');
    })();
</script>
@endisset