<?php

namespace App\Http\Controllers\SDM;

use App\Interaksi\Cache;
use App\Interaksi\Berkas;
use App\Interaksi\Rangka;
use Illuminate\Support\Arr;
use App\Interaksi\SDM\SDMCache;
use App\Interaksi\SDM\SDMExcel;
use App\Interaksi\SDM\SDMBerkas;
use App\Interaksi\SDM\SDMDBQuery;
use App\Interaksi\SDM\SDMValidasi;
use App\Interaksi\SDM\SDMPapanInformasi;
use App\Interaksi\SDM\SDMWord;

class Umum
{
    public function mulai()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        $str = str();

        abort_unless($str->contains($pengguna?->sdm_hak_akses, 'SDM'), 403, 'Akses dibatasi hanya untuk Pengguna Aplikasi SDM.');

        $pengurus = $str->contains($pengguna->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']);
        $fragmen = $reqs->fragment;

        $respon = match (true) {
            $fragmen == 'navigasi' => $this->fragmentNavigasiSDM(),
            $fragmen == 'sdmIngatUltah' => SDMPapanInformasi::pengingatUltah(),
            $pengurus && $fragmen == 'sdmIngatPtsb' => SDMPapanInformasi::pengingatPermintaanTambahSDM(),
            $pengurus && $fragmen == 'sdmIngatPkpd' => SDMPapanInformasi::pengingatPKWTHabis(),
            $pengurus && $fragmen == 'sdmIngatPstatus' => SDMPapanInformasi::pengingatPerubahanStatusSDMTerbaru(),
            $pengurus && $fragmen == 'sdmIngatBaru' => SDMPapanInformasi::pengingatSDMGabungTerbaru(),
            $pengurus && $fragmen == 'sdmIngatKeluar' => SDMPapanInformasi::pengingatSDMKeluarTerbaru(),
            $pengurus && $fragmen == 'sdmIngatPelanggaran' => SDMPapanInformasi::pengingatPelanggaran(),
            $pengurus && $fragmen == 'sdmIngatSanksi' => SDMPapanInformasi::pengingatSanksi(),
            $pengurus && $fragmen == 'sdmIngatNilai' => SDMPapanInformasi::pengingatNilai(),
            default => $this->rangkaHalamanSDM(),
        };

        return $respon;
    }

    public function fragmentNavigasiSDM()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless(str()->contains($pengguna?->sdm_hak_akses, 'SDM'), 403, 'Akses dibatasi hanya untuk Pengguna Aplikasi SDM.');

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($app->view->make('sdm.navigasi'))->withHeaders(['Vary' => 'Accept']);
    }

    public function rangkaHalamanSDM()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless(str()->contains($pengguna?->sdm_hak_akses, 'SDM'), 403, 'Akses dibatasi hanya untuk Pengguna Aplikasi SDM.');

        $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrl()]);

        $HtmlPenuh = $app->view->make('sdm.mulai');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $HtmlPenuh;
    }

    public function fotoProfil($berkas_foto_profil = null)
    {
        return SDMBerkas::ambilFotoSDM($berkas_foto_profil);
    }

    public function unduhKartuSDM($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid, 401);

        $storage = $app->filesystem;

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $ijin_akses));

        $akun = SDMDBQuery::ambilDBSDMUtkKartuID($uuid);

        abort_unless($akun, 404, 'Data SDM tidak ditemukan.');

        $no_absen_sdm = $pengguna->sdm_no_absen;
        $no_absen = $akun->sdm_no_absen;
        $lingkup_lokasi = collect($akun->penempatan_lokasi);
        $lingkup_akses = $lingkup_lokasi->intersect($lingkup)->count();

        abort_unless(blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0) || ($no_absen_sdm == $no_absen), 403, 'Akses pengguna dibatasi.');

        return SDMBerkas::buatKartuIDSDM($akun);
    }

    public function akun($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna, 401);

        $akun = SDMDBQuery::ambilDataAkunLengkap($uuid)->first();

        abort_unless($akun, 404, 'Profil yang dicari tidak ada.');

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $no_absen_sdm = $pengguna->sdm_no_absen;
        $no_absen_atasan = $pengguna->sdm_id_atasan;
        $lingkup = array_filter(explode(',', $ijin_akses));
        $lingkup_lokasi = collect($akun->lokasi_akun);
        $lingkup_akses = $lingkup_lokasi->unique()->intersect($lingkup)->count();
        $str = str();

        abort_unless(($str->contains($pengguna->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']) && (blank($ijin_akses) || $lingkup_lokasi->isEmpty() || ($lingkup_akses > 0))) || ($no_absen_sdm == $akun->sdm_no_absen) || ($akun->sdm_no_absen == $no_absen_atasan) || ($akun->sdm_id_atasan == $no_absen_sdm) || (!blank($no_absen_atasan) && ($akun->sdm_id_atasan == $no_absen_atasan)), 403, 'Ijin akses dibatasi.');

        $no_wa_tst = $str->of($akun->sdm_telepon)->replace('-', '')->replace(' ', '');

        $no_wa = match (true) {
            $str->startsWith($no_wa_tst, '0') => $str->replaceFirst('0', '62', $no_wa_tst),
            $str->startsWith($no_wa_tst, '8') => $str->start($no_wa_tst, '62'),
            default => $no_wa_tst
        };

        $cacheSDM = SDMCache::ambilCacheSDM();

        $data = [
            'akun' => $akun,
            'personils' => $cacheSDM->where('sdm_id_atasan', $akun->sdm_no_absen),
            'no_wa' =>  $no_wa ?: '0',
            'batasi' => $str->contains($pengguna->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']) || $no_absen_sdm == $akun->sdm_no_absen,
        ];

        $HtmlPenuh = $app->view->make('akun', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function ubahAkun($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid, 401);

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkup = array_filter(explode(',', $ijin_akses));

        $akun = SDMDBQuery::ambilDataAkun($uuid)->first();

        $no_absen_sdm = $pengguna->sdm_no_absen;
        $lingkup_lokasi = $akun->penempatan_lokasi;
        $lingkup_akses = collect($lingkup_lokasi)->intersect($lingkup)->count();

        abort_unless(blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0) || ($no_absen_sdm == $akun->sdm_no_absen), 403, 'Akses pengguna dibatasi.');

        if ($reqs->isMethod('post')) {
            abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

            $reqs->whenFilled(
                'sdm_hak_akses',
                function ($input) use ($reqs) {
                    $reqs->except('sdm_hak_akses');
                    $reqs->merge(['sdm_hak_akses' => implode(',', $input)]);
                },
                function () use ($reqs) {
                    $reqs->merge(['sdm_hak_akses' => null]);
                }
            );

            $reqs->whenFilled(
                'sdm_ijin_akses',
                function ($input) use ($reqs) {
                    $reqs->except('sdm_ijin_akses');
                    $reqs->merge(['sdm_ijin_akses' => implode(',', $input)]);
                },
                function () use ($reqs) {
                    $reqs->merge(['sdm_ijin_akses' => null]);
                }
            );

            $reqs->merge(['sdm_id_pengubah' => $pengguna->sdm_no_absen]);

            $validasi = SDMValidasi::validasiUbahDataSDM($uuid, [$reqs->all()]);

            $validasi->validate();

            $valid = $validasi->safe()->all()[0];
            $pengurus = str()->contains($pengguna->sdm_hak_akses, 'SDM-PENGURUS');

            $data = match (true) {
                $pengurus && blank($ijin_akses) => Arr::except($valid, ['foto_profil', 'sdm_berkas']),
                $pengurus && !blank($ijin_akses) => Arr::except($valid, [
                    'foto_profil', 'sdm_berkas', 'sdm_hak_akses', 'sdm_ijin_akses'
                ]),
                default => Arr::except($valid, [
                    'foto_profil', 'sdm_berkas', 'sdm_hak_akses', 'sdm_ijin_akses', 'sdm_tgl_berhenti', 'sdm_jenis_berhenti', 'sdm_ket_berhenti', 'sdm_ket_kary', 'sdm_nama_dok', 'sdm_nomor_dok', 'sdm_penerbit_dok', 'sdm_an_dok', 'sdm_kadaluarsa_dok', 'sdm_no_permintaan', 'sdm_no_absen', 'sdm_id_atasan', 'sdm_tgl_gabung', 'sdm_warganegara', 'sdm_disabilitas', 'sdm_nama_bank', 'sdm_cabang_bank', 'sdm_rek_bank', 'sdm_an_bank'
                ])
            };

            SDMDBQuery::ubahDataSDM($uuid, $data);

            $foto = Arr::only($valid, ['foto_profil'])['foto_profil'] ?? false;
            $berkas = Arr::only($valid, ['sdm_berkas'])['sdm_berkas'] ?? false;
            $no_absen = Arr::only($valid, ['sdm_no_absen'])['sdm_no_absen'];
            $session = $reqs->session();

            if ($foto) {
                SDMBerkas::simpanFotoSDM($foto, $no_absen);
            }

            if ($berkas && $pengurus && (blank($ijin_akses) || blank($lingkup_lokasi) || ($lingkup_akses > 0) || ($no_absen_sdm == $akun->sdm_no_absen))) {
                SDMBerkas::simpanBerkasSDM($berkas, $no_absen);
            }

            if ($foto && $no_absen == $no_absen_sdm) {
                $sesiJS = "lemparXHR({
                    tujuan : '#tbl-menu',
                    tautan : '{$app->url->route('komponen', ['komponen' => 'menu', 'fragment' => 'avatar'])}',
                    normalview : true
                    });";
                $session->flash('sesiJS', $sesiJS);
            }

            SDMCache::hapusCacheSDMUmum();

            $pesan = Rangka::statusBerhasil();
            $perujuk = $session->get('tautan_perujuk');
            $redirect = $app->redirect;

            return $perujuk ? $redirect->to($perujuk)->with('pesan', $pesan) : $redirect->route('sdm.mulai')->with('pesan', $pesan);
        }

        $aturs = Cache::ambilCacheAtur();
        $permintaanSdms = SDMCache::ambilCachePermintaanTambahSDM();
        $atasan = SDMCache::ambilCacheSDM();

        $data = [
            'sdm' => $akun,
            'permintaanSdms' => $permintaanSdms,
            'atasans' => $atasan,
            'negaras' => $aturs->where('atur_jenis', 'NEGARA')->sortBy(['atur_butir', 'asc']),
            'kelamins' => $aturs->where('atur_jenis', 'KELAMIN')->sortBy(['atur_butir', 'asc']),
            'gdarahs' => $aturs->where('atur_jenis', 'GOLONGAN DARAH')->sortBy(['atur_butir', 'asc']),
            'agamas' => $aturs->where('atur_jenis', 'AGAMA')->sortBy(['atur_butir', 'asc']),
            'kawins' => $aturs->where('atur_jenis', 'STATUS MENIKAH')->sortBy(['atur_butir', 'asc']),
            'pendidikans' => $aturs->where('atur_jenis', 'PENDIDIKAN')->sortBy(['atur_butir', 'asc']),
            'disabilitas' => $aturs->where('atur_jenis', 'DISABILITAS')->sortBy(['atur_butir', 'asc']),
            'banks' => $aturs->where('atur_jenis', 'BANK')->sortBy(['atur_butir', 'asc']),
            'seragams' => $aturs->where('atur_jenis', 'UKURAN SERAGAM')->sortBy(['atur_butir', 'asc']),
            'phks' => $aturs->where('atur_jenis', 'JENIS BERHENTI')->sortBy(['atur_butir', 'asc']),
            'perans' => $aturs->where('atur_jenis', 'PERAN')->sortBy(['atur_butir', 'asc']),
            'penempatans' => $aturs->where('atur_jenis', 'PENEMPATAN')->sortBy(['atur_butir', 'asc']),
        ];

        $HtmlPenuh = $app->view->make('tambah-ubah-akun', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function ubahSandi()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna, 401);

        $idPenguna = $pengguna->id;
        $akun = SDMDBQuery::ambilIDPengguna($idPenguna)->first();

        abort_unless($idPenguna == $akun->id, 403, 'Identitas pengguna berbeda.');

        if ($reqs->isMethod('post')) {
            abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

            $validasiSandi = SDMValidasi::validasiUbahSandi($reqs->all());

            $validasiSandi->validate();

            $sandiBaru = $app->hash->make($validasiSandi->safe()->only('password')['password']);

            SDMDBQuery::ubahSandiPengguna($idPenguna, $sandiBaru);

            $reqs->session()->forget('spanduk');

            return $app->redirect->route('mulai')->with('pesan', 'Sandi berhasil diubah.');
        }

        $HtmlPenuh = $app->view->make('ubah-sandi');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $HtmlPenuh;
    }

    public function contohUnggah()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna->sdm_hak_akses, ['PENGURUS', 'MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku Aplikasi.');

        abort_unless($app->filesystem->exists("contoh/unggah-umum.xlsx"), 404, 'Berkas Contoh Ekspor Tidak Ditemukan.');

        $lingkup = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        return SDMExcel::eksporExcelContohUnggahSDM(SDMDBQuery::contohImporDatabaseSDM($lingkup)->orderBy('id', 'desc'));
    }

    public function unggah()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        if ($reqs->isMethod('post')) {
            $validasifile = SDMValidasi::validasiBerkasImporDataSDM($reqs->all());

            $validasifile->validate();

            $file = $validasifile->safe()->only('unggah_profil_sdm')['unggah_profil_sdm'];
            $namafile = 'unggahprofilsdm-' . date('YmdHis') . '.xlsx';

            Berkas::simpanBerkasImporExcelSementara($file, $namafile);

            $fileexcel = Berkas::ambilBerkasImporExcelSementara($namafile);

            return SDMExcel::imporExcelDataSDM($fileexcel);
        }

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make(implode('', $app->view->make('unggah')->renderSections()))->withHeaders(['Vary' => 'Accept']);
    }

    public function panduan()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, 'SDM'), 403, 'Akses dibatasi hanya untuk Pengguna Aplikasi SDM.');

        $storage = $app->filesystem;

        $data = [
            'dokumenUmum' => $storage->directories('sdm/panduan-umum'),
            'dokumenPengurus' => match ($pengguna->sdm_hak_akses) {
                'SDM-PENGURUS', 'SDM-MANAJEMEN' => $storage->directories('sdm/panduan-pengurus'),
                default => null
            },
            'dokumenPengurusCabang' => match ($pengguna->sdm_hak_akses) {
                'SDM-PENGURUS', 'SDM-MANAJEMEN' => $storage->directories('sdm/panduan-pengurus-cabang'),
                default => null
            }
        ];

        $HtmlPenuh = $app->view->make('sdm.dokumen-resmi', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function berkas($berkas = null)
    {
        return SDMBerkas::unduhBerkasProfilSDM($berkas);
    }

    public function formulirSerahTerimaSDMBaru($uuid = null)
    {
        return SDMWord::formulirSerahTerimaSDMBaru($uuid);
    }

    public function formulirPersetujuanGaji($uuid = null)
    {
        return SDMWord::formulirPersetujuanGaji($uuid);
    }

    public function formulirTTDokumenTitipan($uuid = null)
    {
        return SDMWord::formulirTTDokumenTitipan($uuid);
    }

    public function formulirTTInventaris($uuid = null)
    {
        return SDMWord::formulirTTInventaris($uuid);
    }

    public function formulirPelepasanSDM($uuid = null)
    {
        return SDMWord::formulirPelepasanSDM($uuid);
    }

    public function suratKeteranganSDM($uuid = null)
    {
        return SDMWord::formulirPelepasanSDM($uuid);
    }
}
