@isset($pelanggarans)
<details class="kartu">
    <summary>
        Laporan Pelanggaran Yang Perlu Ditindaklanjuti : {{number_format($pelanggarans->count(), 0, ',','.')}}
        Laporan
    </summary>

    <b>
        <i>
            <small>
                Jumlah SDM : Organik = {{number_format($jumlahOrganik, 0, ',', '.')}} Personil | Outsource =
                {{number_format($jumlahOS, 0, ',', '.')}} Personil.
            </small>
        </i>
    </b>

    <div id="tabel_pelanggaranSDM_sematan" class="scroll-margin"></div>

    <div id="tabel_pelanggaranSDM" class="kartu">
        <span class="biru">Biru</span> : Outsource.
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
                    <tr @class([ 'biru'=> str()->contains($pelanggaran->langgar_tkontrak, 'OS-') ])>
                        <th>
                            <div class="pil-aksi">
                                <button id="{{'aksi_pelanggaran_baris_' . $loop->iteration}}" title="Pilih Tindakan">
                                    <svg viewbox="0 0 24 24">
                                        <use href="#ikonmenuvert">
                                        </use>
                                    </svg>
                                </button>

                                <div class="aksi">
                                    <a class="isi-xhr" data-rekam="false" data-tujuan="#tabel_pelanggaranSDM_sematan"
                                        href="{{ $app->url->route('sdm.pelanggaran.lihat', ['uuid' => $pelanggaran->langgar_uuid]) }}"
                                        title="Tindaklanjuti">Tindaklanjuti</a>
                                </div>
                            </div>
                        </th>

                        <td>{{ $loop->iteration }}</td>

                        <td>
                            <div @class(['merah'=> $pelanggaran->langgar_tsdm_tgl_berhenti])>
                                <b><i><u>Terlapor</u></i></b> :<br />
                                <a class="isi-xhr taut-akun"
                                    href="{{ $app->url->route('sdm.akun', ['uuid' => $pelanggaran->langgar_tsdm_uuid]) }}">
                                    <img @class(['akun', 'svg'=> !$app->filesystem->exists('sdm/foto-profil/' .
                                    $pelanggaran->langgar_no_absen . '.webp')]) src="{{
                                    $app->filesystem->exists('sdm/foto-profil/' . $pelanggaran->langgar_no_absen .
                                    '.webp')
                                    ? $app->url->route('sdm.tautan-foto-profil', ['berkas_foto_profil' =>
                                    $pelanggaran->langgar_no_absen . '.webp' . '?' .
                                    filemtime($app->storagePath('app/sdm/foto-profil/' .
                                    $pelanggaran->langgar_no_absen . '.webp'))]) :
                                    $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/blank.webp')) }}"
                                    alt="{{
                                    $pelanggaran->langgar_tsdm_nama ?? 'foto akun' }}" title="{{
                                    $pelanggaran->langgar_tsdm_nama
                                    ?? 'foto akun' }}" loading="lazy">
                                </a>

                                {{$pelanggaran->langgar_no_absen}} - {{$pelanggaran->langgar_tsdm_nama}} <br />
                                {{$pelanggaran->langgar_tlokasi}} {{$pelanggaran->langgar_tkontrak}} -
                                {{$pelanggaran->langgar_tposisi}} {{ $pelanggaran->langgar_tsdm_tgl_berhenti ?
                                '(NON-AKTIF)' : '' }}
                            </div>

                            <div @class(['merah'=> $pelanggaran->langgar_psdm_tgl_berhenti])>
                                <b><i><u>Pelapor</u></i></b> :<br />
                                <a class="isi-xhr taut-akun"
                                    href="{{ $app->url->route('sdm.akun', ['uuid' => $pelanggaran->langgar_psdm_uuid]) }}">
                                    <img @class(['akun', 'svg'=> !$app->filesystem->exists('sdm/foto-profil/' .
                                    $pelanggaran->langgar_pelapor . '.webp')]) src="{{
                                    $app->filesystem->exists('sdm/foto-profil/' . $pelanggaran->langgar_pelapor .
                                    '.webp') ?
                                    $app->url->route('sdm.tautan-foto-profil', ['berkas_foto_profil' =>
                                    $pelanggaran->langgar_pelapor . '.webp' . '?' .
                                    filemtime($app->storagePath('app/sdm/foto-profil/' .
                                    $pelanggaran->langgar_pelapor . '.webp'))]) :
                                    $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/blank.webp')) }}"
                                    alt="{{
                                    $pelanggaran->langgar_psdm_nama ?? 'foto akun' }}" title="{{
                                    $pelanggaran->langgar_psdm_nama
                                    ?? 'foto akun' }}" loading="lazy">
                                </a>

                                {{$pelanggaran->langgar_pelapor}} - {{$pelanggaran->langgar_psdm_nama}} <br />
                                {{$pelanggaran->langgar_plokasi}} {{$pelanggaran->langgar_pkontrak}} -
                                {{$pelanggaran->langgar_pposisi}} {{ $pelanggaran->langgar_psdm_tgl_berhenti ?
                                '(NON-AKTIF)' : '' }}
                            </div>
                        </td>

                        <td>
                            <b>Nomor</b> : {{$pelanggaran->langgar_lap_no}}<br />
                            <b>Tanggal</b> :
                            {{strtoupper($app->date->make($pelanggaran->langgar_tanggal)?->translatedFormat('d F Y')) }}
                            <br />
                            <b>Aduan</b> : {!! nl2br($pelanggaran->langgar_isi) !!}<br />
                            <b>Keterangan</b> : {!! nl2br($pelanggaran->langgar_keterangan) !!}
                        </td>

                        <td>
                            <b><i><u>Sanksi Aktif Sebelumnya</u></i></b> :<br />
                            <b>No Laporan</b> : {{ $pelanggaran->lap_no_sebelumnya}}<br />
                            <b>Sanksi </b> : {{ $pelanggaran->sanksi_aktif_sebelumnya}}<br />
                            <b>Berakhir pada </b> :
                            {{strtoupper($app->date->make($pelanggaran->sanksi_selesai_sebelumnya)?->translatedFormat('d
                            F Y')) }} <br />
                            <b><i><u>Sanksi Diberikan</u></i></b> :<br />
                            <b>Sanksi </b> : {{ $pelanggaran->final_sanksi_jenis}}<br />
                            <b>Tambahan </b> : {{ $pelanggaran->final_sanksi_tambahan}}<br />
                            <b>Mulai </b> :
                            {{strtoupper($app->date->make($pelanggaran->final_sanksi_mulai)?->translatedFormat('d F
                            Y'))}}<br />
                            <b>Selesai </b> :
                            {{strtoupper($app->date->make($pelanggaran->final_sanksi_selesai)?->translatedFormat('d F
                            Y'))}}<br />
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

        @if ($pelanggarans->count() > 0)
        <button class="sekunder tcetak" onclick="ringkasTabel(this)">Panjang/Pendekkan Tampilan Tabel</button>

        <a class="isi-xhr utama" href="{{ $app->url->route('sdm.pelanggaran.data') }}">SELENGKAPNYA</a>
        @endif
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