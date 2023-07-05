@extends('rangka')

@section('isi')
<div id="akun">
    <h2>Informasi Dasar</h2>

    <div id="profil-akun" class="scroll-margin">
        <div class="kartu form">
            @isset($akun)
            <div class="isian pendek">
                <h3>Foto Profil</h3>
                <img @class(['svg'=> !$storageRangka->exists('sdm/foto-profil/' . $akun->sdm_no_absen . '.webp')])
                src="{{ $storageRangka->exists('sdm/foto-profil/' . $akun->sdm_no_absen . '.webp') ?
                $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' => $akun->sdm_no_absen . '.webp' . '?'
                . filemtime($appRangka->storagePath('app/sdm/foto-profil/' . $akun->sdm_no_absen . '.webp'))], false) :
                $mixRangka('/ikon.svg') . '#akun' }}" alt="{{ $akun->sdm_nama ?? 'foto akun' }}" title="{{
                $akun->sdm_nama ?? 'foto akun' }}" loading="lazy">
            </div>

            <div class="isian normal">
                <h3>Identitas SDM</h3>

                <p>{{ $akun->sdm_no_absen }} - {{ $akun->sdm_nama }}</p>

                <h3>Identitas Atasan</h3>

                <p @class(['merah'=> $akun->tgl_berhenti_atasan])>
                    @if ($akun->uuid_atasan)
                    <a class="taut-akun isi-xhr"
                        href="{{ $urlRangka->route('sdm.akun', ['uuid' => $akun->uuid_atasan], false) }}">
                        <img @class(['akun', 'svg'=> !$storageRangka->exists('sdm/foto-profil/' . $akun->sdm_id_atasan .
                        '.webp')]) src="{{ $storageRangka->exists('sdm/foto-profil/' . $akun->sdm_id_atasan . '.webp') ?
                        $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' => $akun->sdm_id_atasan .
                        '.webp' .
                        '?' . filemtime($appRangka->storagePath('app/sdm/foto-profil/' . $akun->sdm_id_atasan .
                        '.webp'))], false) :
                        $mixRangka('/ikon.svg') . '#akun' }}" alt="{{
                        $akun->nama_atasan ?? 'foto akun' }}" title="{{ $akun->nama_atasan ?? 'foto akun' }}"
                        loading="lazy">
                    </a>

                    {{ $akun->sdm_id_atasan }} - {{ $akun->nama_atasan }} - {{ $akun->penempatan_lokasi }} - {{
                    $akun->penempatan_posisi }} {{ $akun->tgl_berhenti_atasan ? '(NON-AKTIF)' : '' }}
                    @endif
                </p>
            </div>

            <div class="isian">
                <h3>Tanggal Bergabung</h3>

                <p>{{ strtoupper($dateRangka->make($akun->sdm_tgl_gabung)?->translatedFormat('d F Y')) }}</p>

                @if ($batasi)
                <h3>No Permintaan SDM</h3>

                <p><u><a class="isi-xhr"
                            href="{{ $urlRangka->route('sdm.permintaan-tambah-sdm.data', ['kata_kunci' => $akun->sdm_no_permintaan], false) }}"
                            aria-label="Permintaan Tambah SDM No {{ $akun->sdm_no_permintaan }}">{{
                            $akun->sdm_no_permintaan }}</a></u></p>

                <h3>Nomor E-KTP/Passport</h3>

                <p><u><a class="isi-xhr"
                            href="{{ $urlRangka->route('sdm.penempatan.riwayat', ['kata_kunci' => $akun->sdm_no_ktp], false) }}">{{
                            $akun->sdm_no_ktp }}</a></u></p>
                @endif
            </div>

            <div class="isian">
                @if ($batasi)
                <h3>Tempat Lahir</h3>

                <p>{{ $akun->sdm_tempat_lahir }}</p>

                <h3>Tanggal Lahir</h3>

                <p>{{ strtoupper($dateRangka->make($akun->sdm_tgl_lahir)?->translatedFormat('d F Y')) }}</p>
                @endif

                <div class="isian">
                    <h3>Telepon</h3>

                    <p><u><a href="{{ 'https://wa.me/'.$no_wa }}" target="_blank">{{ $no_wa }}</a></u></p>
                </div>
            </div>

            @if ($batasi)
            <details class="gspan-4">
                <summary>Tampilkan Profil Lengkap :</summary>
                <div class="kartu form">
                    <div class="isian">
                        <h3>Warga Negara</h3>

                        <p>{{ $akun->sdm_warganegara }}</p>
                    </div>

                    <div class="isian">
                        <h3>Kelamin</h3>

                        <p>{{ $akun->sdm_kelamin }}</p>
                    </div>

                    <div class="isian">
                        <h3>Gol Darah</h3>

                        <p>{{ $akun->sdm_gol_darah }}</p>
                    </div>

                    <div class="isian">
                        <h3>Alamat</h3>

                        <p>{{ $akun->sdm_alamat }}</p>
                    </div>

                    <div class="isian">
                        <h3>RT</h3>

                        <p>{{ $akun->sdm_alamat_rt }}</p>
                    </div>

                    <div class="isian">
                        <h3>RW</h3>

                        <p>{{ $akun->sdm_alamat_rw }}</p>
                    </div>

                    <div class="isian">
                        <h3>Kelurahan</h3>

                        <p>{{ $akun->sdm_alamat_kelurahan }}</p>
                    </div>

                    <div class="isian">
                        <h3>Kecamatan</h3>

                        <p>{{ $akun->sdm_alamat_kecamatan }}</p>
                    </div>

                    <div class="isian">
                        <h3>Kota/Kabupaten</h3>

                        <p>{{ $akun->sdm_alamat_kota }}</p>
                    </div>

                    <div class="isian">
                        <h3>Provinsi</h3>

                        <p>{{ $akun->sdm_alamat_provinsi }}</p>
                    </div>

                    <div class="isian">
                        <h3>Kode Pos</h3>

                        <p>{{ $akun->sdm_alamat_kodepos }}</p>
                    </div>

                    <div class="isian">
                        <h3>Agama</h3>

                        <p>{{ $akun->sdm_agama }}</p>
                    </div>

                    <div class="isian">
                        <h3>No KK</h3>

                        <p>{{ $akun->sdm_no_kk }}</p>
                    </div>

                    <div class="isian">
                        <h3>Status Kawin</h3>

                        <p>{{ $akun->sdm_status_kawin }}</p>
                    </div>

                    <div class="isian">
                        <h3>Jumlah Anak</h3>

                        <p>{{ $akun->sdm_jml_anak }}</p>
                    </div>

                    <div class="isian">
                        <h3>Pendidikan</h3>

                        <p>{{ $akun->sdm_pendidikan }}</p>
                    </div>

                    <div class="isian">
                        <h3>Jurusan</h3>

                        <p>{{ $akun->sdm_jurusan }}</p>
                    </div>

                    <div class="isian">
                        <h3>Email</h3>

                        <p><u><a href="{{ 'mailto:'.$akun->email }}" target="_blank" rel="noopener noreferrer">{{
                                    $akun->email }}</a></u></p>
                    </div>

                    <div class="isian">
                        <h3>Disabilitas</h3>

                        <p>{{ $akun->sdm_disabilitas }}</p>
                    </div>

                    <div class="isian">
                        <h3>No BPJS</h3>

                        <p>{{ $akun->sdm_no_bpjs }}</p>
                    </div>

                    <div class="isian">
                        <h3>No Jamsostek</h3>

                        <p>{{ $akun->sdm_no_jamsostek }}</p>
                    </div>

                    <div class="isian">
                        <h3>NPWP</h3>

                        <p>{{ $akun->sdm_no_npwp }}</p>
                    </div>

                    <div class="isian">
                        <h3>Nama Bank</h3>

                        <p>{{ $akun->sdm_nama_bank }}</p>
                    </div>

                    <div class="isian">
                        <h3>Cabang Bank</h3>

                        <p>{{ $akun->sdm_cabang_bank }}</p>
                    </div>

                    <div class="isian">
                        <h3>Nomor Rekening</h3>

                        <p>{{ $akun->sdm_rek_bank }}</p>
                    </div>

                    <div class="isian">
                        <h3>A.n Rekening</h3>

                        <p>{{ $akun->sdm_an_rek }}</p>
                    </div>

                    <div class="isian">
                        <h3>Dokumen Jaminan</h3>

                        <p>{{ $akun->sdm_nama_dok }}</p>
                    </div>

                    <div class="isian">
                        <h3>Nomor Dokumen</h3>

                        <p>{{ $akun->sdm_nomor_dok }}</p>
                    </div>

                    <div class="isian">
                        <h3>Penerbit Dokumen</h3>

                        <p>{{ $akun->sdm_penerbit_dok }}</p>
                    </div>

                    <div class="isian">
                        <h3>A.n Dokumen</h3>

                        <p>{{ $akun->sdm_an_dok }}</p>
                    </div>

                    <div class="isian">
                        <h3>Kadaluarsa Dokumen</h3>

                        <p>{{ strtoupper($dateRangka->make($akun->sdm_kadaluarsa_dok)?->translatedFormat('d F Y')) }}
                        </p>
                    </div>

                    <div class="isian">
                        <h3>Ukuran Seragam</h3>

                        <p>{{ $akun->sdm_uk_seragam }}</p>
                    </div>

                    <div class="isian">
                        <h3>Ukuran Sepatu</h3>

                        <p>{{ $akun->sdm_uk_sepatu }}</p>
                    </div>

                    <div class="isian gspan-4">
                        <h3>Keterangan</h3>

                        <p>{!! nl2br($akun->sdm_ket_kary) !!}</p>
                    </div>

                    <div class="isian">
                        <h3>Hak Akses</h3>

                        <p>{{$akun->sdm_hak_akses}}</p>
                    </div>

                    <div class="isian">
                        <h3>Ijin Akses</h3>

                        <p>{{$akun->sdm_ijin_akses}}</p>
                    </div>

                    <div class="isian">
                        <h3>Tanggal Berhenti</h3>

                        <p>{{ strtoupper($dateRangka->make($akun->sdm_tgl_berhenti)?->translatedFormat('d F Y')) }}</p>
                    </div>

                    <div class="isian">
                        <h3>Jenis Berhenti</h3>

                        <p>{{$akun->sdm_jenis_berhenti}}</p>
                    </div>

                    <div class="isian gspan-4">
                        <h3>Keterangan Berhenti</h3>

                        <p>{!! nl2br($akun->sdm_ket_berhenti) !!}</p>
                    </div>
                </div>
            </details>
            @endif

            <details class="isian gspan-4">
                <summary>Tampilkan SDM Yang Dipimpin ({{number_format($personils->count(), 0, ',','.')}} Personil) :
                </summary>

                @isset($personils)
                <ol>

                    @forelse ($personils as $personil)
                    <li class="bersih">
                        <a class="taut-akun isi-xhr"
                            href="{{ $urlRangka->route('sdm.akun', ['uuid' => $personil->sdm_uuid], false) }}">
                            <img @class(['akun', 'svg'=> !$storageRangka->exists('sdm/foto-profil/' .
                            $personil->sdm_no_absen .
                            '.webp')]) src="{{ $storageRangka->exists('sdm/foto-profil/' . $personil->sdm_no_absen .
                            '.webp') ?
                            $urlRangka->route('sdm.tautan-foto-profil', ['berkas_foto_profil' => $personil->sdm_no_absen
                            . '.webp' .
                            '?' . filemtime($appRangka->storagePath('app/sdm/foto-profil/' . $personil->sdm_no_absen .
                            '.webp'))], false) :
                            $mixRangka('/ikon.svg') . '#akun' }}" alt="{{
                            $personil->sdm_nama ?? 'foto akun' }}" title="{{ $personil->sdm_nama ?? 'foto akun' }}"
                            loading="lazy">
                        </a>

                        {{ $personil->sdm_no_absen }} - {{ $personil->sdm_nama }} - {{ $personil->penempatan_lokasi }} -
                        {{ $personil->penempatan_posisi }}
                    </li>

                    @empty
                    <li class="merah">Belum Ada Anggota Personil.</li>

                    @endforelse
                </ol>
                @endisset
            </details>

            <div class="isian gspan-4">
                <h3>Berkas SDM</h3>

                @if ($storageRangka->exists('sdm/berkas/'.$akun->sdm_no_absen.'.pdf'))
                <iframe class="tcetak berkas"
                    src="{{ $urlRangka->route('sdm.berkas', ['berkas' => $akun->sdm_no_absen . '.pdf' . '?' . filemtime($appRangka->storagePath('app/sdm/berkas/' . $akun->sdm_no_absen . '.pdf'))], false) }}"
                    title="Berkas SDM" loading="lazy"
                    onload="if (this.contentDocument.body.id == 'badan-dokumen') this.remove()"></iframe>

                <a class="sekunder tcetak" target="_blank" title="Unduh Berkas Terunggah"
                    href="{{ $urlRangka->route('sdm.berkas', ['berkas' => $akun->sdm_no_absen . '.pdf' . '?' . filemtime($appRangka->storagePath('app/sdm/berkas/' . $akun->sdm_no_absen . '.pdf'))], false) }}">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <use xlink:href="{{ $mixRangka('/ikon.svg') . '#unduh' }}"
                            xmlns:xlink="http://www.w3.org/1999/xlink"></use>
                    </svg>
                    BERKAS
                </a>

                @else
                <p class="merah">Tidak ada berkas terunggah.</p>
                @endif
            </div>

            @if($strRangka->contains($userRangka->sdm_hak_akses, 'SDM-PENGURUS'))
            <div class="isian tcetak" id="akun-cetakFormulir">
                <select class="pil-saja tombol"
                    onchange="if (this.value !== '') lemparXHR({tujuan : '#akun-cetakFormulirStatus', tautan : this.value, strim : true})">
                    <option value="">CETAK FORMULIR</option>
                    <option
                        value="{{$urlRangka->route('sdm.formulir-serah-terima-sdm-baru', ['uuid' => $akun->sdm_uuid], false)}}">
                        SERAH TERIMA SDM BARU</option>
                    <option
                        value="{{$urlRangka->route('sdm.formulir-persetujuan-gaji', ['uuid' => $akun->sdm_uuid], false)}}">
                        PERSETUJUAN GAJI</option>
                    <option
                        value="{{$urlRangka->route('sdm.formulir-tt-dokumen-titipan', ['uuid' => $akun->sdm_uuid], false)}}">
                        TANDA TERIMA DOKUMEN TITIPAN</option>
                    <option
                        value="{{$urlRangka->route('sdm.formulir-tt-inventaris', ['uuid' => $akun->sdm_uuid], false)}}">
                        TANDA TERIMA SERAGAM/SEPATU/INVENTARIS</option>
                    <option
                        value="{{$urlRangka->route('sdm.formulir-pelepasan-sdm', ['uuid' => $akun->sdm_uuid], false)}}">
                        PELEPASAN KARYAWAN</option>
                    <option
                        value="{{$urlRangka->route('sdm.surat-keterangan-sdm', ['uuid' => $akun->sdm_uuid], false)}}">
                        SURAT KETERANGAN KERJA</option>
                </select>
            </div>
            @endif

            <div class="isian gspan-4 scroll-margin" id="akun-cetakFormulirStatus"></div>

            <a class="utama isi-xhr tcetak" data-tujuan="#profil-akun"
                href="{{ $urlRangka->route('sdm.ubah-akun', ['uuid' => $akun->sdm_uuid], false) }}">UBAH</a>

            @else
            <p>Periksa data yang dikirim.</p>

            @endisset
        </div>
    </div>

    <h2>Riwayat Penempatan</h2>
    <div id="riwa-penem-sdm_tabels" class="kartu scroll-margin">Memuat Riwayat Penempatan...</div>

    <h2>Riwayat Sanksi</h2>
    <div id="sanksi-sdm_tabels" class="kartu scroll-margin">Memuat Riwayat Sanksi...</div>

    <h2>Riwayat Penilaian</h2>
    <div id="nilai-sdm_tabels" class="kartu scroll-margin">Memuat Riwayat Penilaian...</div>


    <div class="pintasan tcetak">
        <a href="#" onclick="event.preventDefault();window.scrollTo(0,0)" title="Kembali Ke Atas">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#panahatas' }}"
                    xmlns:xlink="http://www.w3.org/1999/xlink"></use>
            </svg>
        </a>

        <a href="{{ $urlRangka->route('sdm.unduh.kartu-sdm', ['uuid' => $akun->sdm_uuid], false) }}"
            title="Unduh Kartu Identitas">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <use xlink:href="{{ $mixRangka('/ikon.svg') . '#kartuID' }}" xmlns:xlink="http://www.w3.org/1999/xlink">
                </use>
            </svg>
        </a>
    </div>

    <script>
        (async() => {
            while(!window.aplikasiSiap) {
                await new Promise((resolve,reject) =>
                setTimeout(resolve, 1000));
            }
            
            pilSaja('#akun-cetakFormulir .pil-saja');
            lemparXHR({tujuan : "#riwa-penem-sdm_tabels", tautan : "{{ $urlRangka->route('sdm.penempatan.riwayat', ['uuid' => $akun->sdm_uuid, 'fragment' => 'riwa-penem-sdm_tabels']) }}", normalview : true, fragmen : true});
            lemparXHR({tujuan : "#sanksi-sdm_tabels", tautan : "{{ $urlRangka->route('sdm.sanksi.data', ['uuid' => $akun->sdm_uuid, 'fragment' => 'sanksi-sdm_tabels']) }}", normalview : true, fragmen : true});
            lemparXHR({tujuan : "#nilai-sdm_tabels", tautan : "{{ $urlRangka->route('sdm.penilaian.data', ['uuid' => $akun->sdm_uuid, 'fragment' => 'nilai-sdm_tabels']) }}", normalview : true, fragmen : true});
        })();
    </script>

    @include('pemberitahuan')
    @include('komponen')
</div>
@endsection