<?php

namespace App\Http\Controllers\SDM;

use App\Interaksi\Cache;
use App\Interaksi\Berkas;
use App\Interaksi\Rangka;
use App\Interaksi\Websoket;
use Illuminate\Support\Arr;
use App\Interaksi\SDM\SDMCache;
use App\Interaksi\SDM\SDMExcel;
use App\Interaksi\SDM\SDMBerkas;
use App\Interaksi\SDM\SDMDBQuery;
use App\Interaksi\SDM\SDMValidasi;

class Penilaian
{
    public function index($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        $aksesAkun = $uuid ? SDMDBQuery::aksesAkun($uuid) : null;

        $str = str();

        abort_unless(
            $pengguna && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN'])
                || ($pengguna?->sdm_uuid == $uuid && $pengguna?->sdm_uuid !== null)
                || ($pengguna?->sdm_id_atasan == $aksesAkun?->sdm_no_absen)
                || ($pengguna?->sdm_id_atasan == $aksesAkun?->sdm_id_atasan),
            403,
            'Akses dibatasi hanya untuk Pemangku SDM.'
        );

        $validator = SDMValidasi::validasiPencarianNilaiSDM([$reqs->all()]);

        if ($validator->fails()) {
            return $app->redirect->route('sdm.penilaian.data')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $lingkupIjin = array_filter(explode(',', $pengguna?->sdm_ijin_akses));

        $urutArray = $reqs->urut;
        $kunciUrut = array_filter((array) $urutArray);
        $uruts = $urutArray ? implode(',', $kunciUrut) : null;

        $cari = SDMDBQuery::cariPenilaianSDM($reqs, $reqs->kata_kunci, $uuid, $lingkupIjin, $uruts);

        if ($reqs->unduh == 'excel') {
            return SDMExcel::eksporExcelPencarianNilaiSDM($cari);
        }

        $cacheAtur = Cache::ambilCacheAtur();

        $data = [
            'tabels' => $cari->clone()->paginate($reqs->bph ?: 25)->withQueryString()->appends(['fragment' => 'nilai-sdm_tabels', 'uuid' => $uuid ?? '']),
            'lokasis' => $cacheAtur->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin)->sortBy(['atur_butir', 'asc']);
            }),
            'statusSDMs' => $cacheAtur->where('atur_jenis', 'STATUS KONTRAK')->sortBy(['atur_jenis', 'asc'], ['atur_butir', 'asc']),
            'urutTahun' => $str->contains($uruts, 'nilaisdm_tahun'),
            'indexTahun' => (head(array_keys($kunciUrut, 'nilaisdm_tahun ASC')) + head(array_keys($kunciUrut, 'nilaisdm_tahun DESC')) + 1),
            'urutPeriode' => $str->contains($uruts, 'nilaisdm_periode'),
            'indexPeriode' => (head(array_keys($kunciUrut, 'nilaisdm_periode ASC')) + head(array_keys($kunciUrut, 'nilaisdm_periode DESC')) + 1),
            'urutNilai' => $str->contains($uruts, 'nilaisdm_total'),
            'indexNilai' => (head(array_keys($kunciUrut, 'nilaisdm_total ASC')) + head(array_keys($kunciUrut, 'nilaisdm_total DESC')) + 1),
            'halamanAkun' => $uuid ?? '',
            'jumlahOS' => $cari->clone()->whereNotNull('kontrak.penempatan_kontrak')->where('kontrak.penempatan_kontrak', 'like', 'OS-%')->count(),
            'jumlahOrganik' => $cari->clone()->whereNotNull('kontrak.penempatan_kontrak')->where('kontrak.penempatan_kontrak', 'not like', 'OS-%')->count()
        ];

        if (!isset($uuid)) {
            $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);
        }

        $HtmlPenuh = $app->view->make('sdm.penilaian.data', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');

        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
            ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function lihat($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $nilai = SDMDBQuery::ambilDataPenilaianSDM($uuid, array_filter(explode(',', $pengguna->sdm_ijin_akses)));

        abort_unless($nilai, 404, 'Data Penialain SDM tidak ditemukan.');

        $HtmlPenuh = $app->view->make('sdm.penilaian.lihat', compact('nilai'));
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function tambah()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        if ($reqs->isMethod('post')) {

            $reqs->merge(['nilaisdm_id_pembuat' => $pengguna->sdm_no_absen]);

            $validasi = SDMValidasi::validasiTambahDataNilaiSDM([$reqs->all()]);

            $validasi->validate();

            $valid = $validasi->safe()->all()[0];

            $data = Arr::except($valid, ['nilai_berkas']);

            SDMDBQuery::tambahDataNilaiSDM($data);

            $berkas = Arr::only($valid, ['nilai_berkas'])['nilai_berkas'] ?? false;

            if ($berkas) {
                $namaBerkas = Arr::only($valid, ['nilaisdm_no_absen'])['nilaisdm_no_absen'] . ' - '  . Arr::only($valid, ['nilaisdm_tahun'])['nilaisdm_tahun'] . ' - ' . Arr::only($valid, ['nilaisdm_periode'])['nilaisdm_periode'] . '.pdf';

                SDMBerkas::simpanBerkasNilaiSDM($berkas, $namaBerkas);
            }

            SDMCache::hapusCacheNilaiSDM();

            $pesanSoket = $pengguna?->sdm_nama . ' telah menambah data Penilaian SDM nomor absen '
                . Arr::only($valid, ['nilaisdm_no_absen'])['nilaisdm_no_absen'] . ' Tahun/Periode ' . Arr::only($valid, ['nilaisdm_tahun'])['nilaisdm_tahun'] . '/' . Arr::only($valid, ['nilaisdm_periode'])['nilaisdm_periode']
                . ' pada ' . strtoupper($app->date->now()->translatedFormat('d F Y H:i:s'));

            Websoket::siaranUmum($pesanSoket);

            $perujuk = $reqs->session()->get('tautan_perujuk');
            $redirect = $app->redirect;
            $pesan = Rangka::statusBerhasil();

            return $perujuk
                ? $redirect->to($perujuk)->with('pesan', $pesan)
                : $redirect->route('sdm.penilaian.data')->with('pesan', $pesan);
        }

        $HtmlPenuh = $app->view->make('sdm.penilaian.tambah-ubah', [
            'sdms' => SDMCache::ambilCacheSDM()->when($lingkupIjin, function ($c) use ($lingkupIjin) {
                return $c->whereIn('penempatan_lokasi', [null, ...$lingkupIjin]);
            }),
        ]);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function ubah($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $nilai = SDMDBQuery::ambilDataPenilaianSDM($uuid, array_filter(explode(',', $pengguna->sdm_ijin_akses)));

        abort_unless($nilai, 404, 'Data Penilaian tidak ditemukan.');

        if ($reqs->isMethod('post')) {

            $reqs->merge(['nilaisdm_id_pengubah' => $pengguna->sdm_no_absen]);

            $validasi = SDMValidasi::validasiUbahDataNilaiSDM([$reqs->all()]);

            $validasi->validate();

            $valid = $validasi->safe()->all()[0];

            $data = Arr::except($valid, ['nilai_berkas']);

            SDMDBQuery::ubahDataNilaiSDM($uuid, $data);

            $berkas = Arr::only($valid, ['nilai_berkas'])['nilai_berkas'] ?? false;

            if ($berkas) {
                $namaBerkas = $nilai->nilaisdm_no_absen . ' - '  . Arr::only($valid, ['nilaisdm_tahun'])['nilaisdm_tahun'] . ' - ' . Arr::only($valid, ['nilaisdm_periode'])['nilaisdm_periode'] . '.pdf';

                SDMBerkas::simpanBerkasNilaiSDM($berkas, $namaBerkas);
            }

            SDMCache::hapusCacheNilaiSDM();

            $pesanSoket = $pengguna?->sdm_nama . ' telah mengubah data Penilaian SDM nomor absen '
                . $nilai->nilaisdm_no_absen . ' Tahun/Periode ' . Arr::only($valid, ['nilaisdm_tahun'])['nilaisdm_tahun'] . '/' . Arr::only($valid, ['nilaisdm_periode'])['nilaisdm_periode']
                . ' pada ' . strtoupper($app->date->now()->translatedFormat('d F Y H:i:s'));

            Websoket::siaranUmum($pesanSoket);

            $perujuk = $reqs->session()->get('tautan_perujuk');
            $redirect = $app->redirect;
            $pesan = Rangka::statusBerhasil();

            return $perujuk
                ? $redirect->to($perujuk)->with('pesan', $pesan)
                : $redirect->route('sdm.penilaian.data')->with('pesan', $pesan);
        }

        $HtmlPenuh = $app->view->make('sdm.penilaian.tambah-ubah', compact('nilai'));
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function contohUnggahPenilaianSDM()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        abort_unless($app->filesystem->exists("contoh/unggah-umum.xlsx"), 404, 'Berkas Contoh Ekspor Tidak Ditemukan.');

        $cari = SDMDBQuery::contohImporSanksiSDM(array_filter(explode(',', $pengguna->sdm_ijin_akses)));

        return SDMExcel::eksporExcelContohUnggahNilaiSDM($cari);
    }

    public function unggahPenilaianSDM()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        if ($reqs->isMethod('post')) {
            $validasifile = SDMValidasi::validasiBerkasImporDataNilaiSDM($reqs->all());;

            $validasifile->validate();

            $file = $validasifile->safe()->only('unggah_nilai_sdm')['unggah_nilai_sdm'];
            $namafile = 'unggahnilaisdm-' . date('YmdHis') . '.xlsx';

            Berkas::simpanBerkasImporExcelSementara($file, $namafile);

            $fileexcel = Berkas::ambilBerkasImporExcelSementara($namafile);

            return SDMExcel::imporExcelDataNilaiSDM($fileexcel);
        };

        $HtmlPenuh = $app->view->make('sdm.penilaian.unggah');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }
}
