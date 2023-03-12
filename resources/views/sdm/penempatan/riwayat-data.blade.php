<div class="data ringkas">
    <table id="riwa-penem-sdm_tabel" class="tabel">
        <thead>
            <tr>
                <th></th>
                <th>No</th>
                @if (!$rekRangka->routeIs('akun'))
                <th>Identitas</th>
                @endif
                <th>Posisi</th>
                <th>Rincian</th>
                @if (!$rekRangka->routeIs('akun'))
                <th>Informasi Lain</th>
                <th>Tambahan</th>
                @endif
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tabels as $nomor => $tabel)
            <tr @class(['merah' => $tabel->sdm_tgl_berhenti, 'oranye' => ($tabel->penempatan_selesai && $tabel->penempatan_selesai <= $dateRangka->today()->addDay(40)),'biru' => $strRangka->startsWith($tabel->penempatan_kontrak, 'OS-')])>
                <th>
                    <div class="pil-aksi">
                        <button>
                            <svg viewbox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#menuvert' }}" xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                            </svg>
                        </button>
                        <div class="aksi">
                            @isset ($tabel->penempatan_uuid)
                            <a class="isi-xhr" data-rekam="false" data-tujuan="#sdm_penem_riwa_sematan" href="{{ $urlRangka->route('sdm.penempatan.lihat', ['uuid' => $tabel->penempatan_uuid], false) }}" title="Lihat Data">Lihat Penempatan</a>
                            <a class="isi-xhr" data-rekam="false" data-tujuan="#sdm_penem_riwa_sematan" href="{{ $urlRangka->route('sdm.penempatan.ubah', ['uuid' => $tabel->penempatan_uuid], false) }}" title="Ubah Data">Ubah Penempatan</a>
                            @endisset
                            @unless ($rekRangka->routeIs('akun'))
                                <a class="isi-xhr" data-rekam="false" data-tujuan="#sdm_penem_riwa_sematan" href="{{ $urlRangka->route('sdm.penempatan.tambah', ['uuid' => $tabel->sdm_uuid], false) }}" title="Tambah Data">Tambah Penempatan</a>
                            @endunless
                        </div>
                    </div>
                </th>
                <td>{{ rescue(function () use ($tabels, $nomor) { return ($tabels->firstItem() + $nomor);}, $loop->iteration, false) }}</td>
                    
                @if (!$rekRangka->routeIs('akun'))
                <td>
                    <a class="isi-xhr taut-akun" href="{{ $urlRangka->route('akun', ['uuid' => $tabel->sdm_uuid], false) }}">
                        <img @class(['akun', 'svg' => !$storageRangka->exists('sdm/foto-profil/' . $tabel->sdm_no_absen . '.webp')]) src="{{ $storageRangka->exists('sdm/foto-profil/' . $tabel->sdm_no_absen . '.webp') ? $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' => $tabel->sdm_no_absen . '.webp' . '?' . filemtime($appRangka->storagePath('app/sdm/foto-profil/' . $tabel->sdm_no_absen . '.webp'))], false) : $mixRangka('/ikon.svg') . '#akun' }}" alt="{{ $tabel->sdm_nama ?? 'foto akun' }}" title="{{ $tabel->sdm_nama ?? 'foto akun' }}" loading="lazy">
                    </a>
                    <b>No Absen</b> : {{$tabel->sdm_no_absen}}<br/>
                    <b>Nama</b> : {{$tabel->sdm_nama}}<br/>
                    <b>No Permintaan</b> : <u><a class="isi-xhr" href="{{ $urlRangka->route('sdm.permintaan-tambah-sdm.data', ['kata_kunci' => $tabel->sdm_no_permintaan], false) }}">{{ $tabel->sdm_no_permintaan }}</a></u><br/>
                    <b>Tgl Masuk</b> : {{ strtoupper($dateRangka->make($tabel->sdm_tgl_gabung)?->translatedFormat('d F Y')) }}<br/>
                    <b>Tgl Keluar</b> : {{ strtoupper($dateRangka->make($tabel->sdm_tgl_berhenti)?->translatedFormat('d F Y')) }}<br/>
                    <b>PHK</b> : {{$tabel->sdm_jenis_berhenti}}<br/>
                    <b>Ket PHK</b> : {{$tabel->sdm_ket_berhenti}}
                </td>
                @endif
                    
                <td>
                    <b>Lokasi</b> : {{$tabel->penempatan_lokasi}}<br/>
                    <b>Posisi</b> : {{$tabel->penempatan_posisi}}<br/>
                    <b>Kode WLKP</b> : {{$tabel->posisi_wlkp}}<br/>
                    <b>Status</b> : {{$tabel->penempatan_kontrak}}<br/>
                    <b>Mulai</b> : {{ strtoupper($dateRangka->make($tabel->penempatan_mulai)?->translatedFormat('d F Y')) }}<br/>
                    <b>Selesai</b> : {{ strtoupper($dateRangka->make($tabel->penempatan_selesai)?->translatedFormat('d F Y')) }}<br/>
                    <b>Ke</b> : {{$tabel->penempatan_ke}}
                </td>
                <td>
                    <b>Kategori</b> : {{$tabel->penempatan_kategori}}<br/>
                    <b>Pangkat</b> : {{$tabel->penempatan_pangkat}}<br/>
                    <b>Golongan</b> : {{$tabel->penempatan_golongan}}<br/>
                    <b>Grup</b> : {{$tabel->penempatan_grup}}<br/>
                    <b>Usia</b> : {{ $tabel->usia }} Tahun<br/>
                    <b>Masa Kerja</b> : {{ $tabel->masa_kerja }} Tahun<br/>
                    <b>Masa Aktif</b> : {{ $tabel->masa_aktif }} Tahun
                </td>

                @if (!$rekRangka->routeIs('akun'))
                <td>
                    <b>E-KTP/Passport</b> : <u><a class="isi-xhr" href="{{ $urlRangka->route('sdm.penempatan.riwayat', ['kata_kunci' => $tabel->sdm_no_ktp], false) }}">{{ $tabel->sdm_no_ktp }}</a></u><br/>
                    <b>Warganegara</b> : {{$tabel->sdm_warganegara}}<br/>
                    <b>Lahir</b> : {{$tabel->sdm_tempat_lahir}}, {{ strtoupper($dateRangka->make($tabel->sdm_tgl_lahir)?->translatedFormat('d F Y')) }}<br/>
                    <b>Kelamin</b> : {{$tabel->sdm_kelamin}}<br/>
                    <b>Agama</b> : {{$tabel->sdm_agama}}<br/>
                    <b>Status Kawin</b> : {{$tabel->sdm_status_kawin}}<br/>
                    <b>Pendidikan</b> : {{$tabel->sdm_pendidikan}}<br/>
                    <b>Jurusan</b> : {{$tabel->sdm_jurusan}}
                </td>
                <td>
                    <b>Telepon</b> : {{$tabel->sdm_telepon}}<br/>
                    <b>Email</b> : {{$tabel->email}}<br/>
                    <b>No Jamsostek</b> : {{$tabel->sdm_no_jamsostek}}<br/>
                    <b>No BPJS</b> : {{$tabel->sdm_no_bpjs}}<br/>
                    <b>Jml Anak</b> : {{$tabel->sdm_jml_anak}}<br/>
                    <b>Uk Seragam</b> : {{$tabel->sdm_uk_seragam}}<br/>
                    <b>Uk Sepatu</b> : {{$tabel->sdm_uk_sepatu}}<br/>
                    <b>Disabilitas</b> : {{$tabel->sdm_disabilitas}}<br/>
                    <b>ID Atasan</b> : {{$tabel->sdm_id_atasan}}
                </td>
                @endif
                
                <td>{!! nl2br($tabel->penempatan_keterangan) !!}</td>
            </tr>
            @empty
            <tr>
                <th></th>
                <td colspan="{{ !$rekRangka->routeIs('akun') ? '7' : '4' }}">Tidak ada data.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<button class="sekunder tcetak" onclick="ringkasTabel(this)" style="margin:0.5em 0">Panjang/Pendekkan Tampilan Tabel</button>
    
<script>
    pilDasar('.trek-data .pil-dasar');
    pilSaja('.trek-data .pil-saja');
    urutData('#sdm_penempatan_status_urut','#sdm_penempatan_status_urut [data-indeks]');
    formatTabel('#riwa-penem-sdm_tabel thead th', '#riwa-penem-sdm_tabel tbody tr');
</script>

@includeWhen($rekRangka->session()->has('spanduk') || $rekRangka->session()->has('pesan') || $errors->any(), 'pemberitahuan')
