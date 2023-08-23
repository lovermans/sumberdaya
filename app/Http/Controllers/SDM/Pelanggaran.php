<?php

namespace App\Http\Controllers\SDM;

use App\Interaksi\Cache;
use App\Interaksi\Rangka;
use App\Interaksi\SDM\SDMBerkas;
use App\Interaksi\SDM\SDMCache;
use Illuminate\Support\Arr;
use App\Interaksi\SDM\SDMValidasi;
use App\Interaksi\SDM\SDMDBQuery;
use App\Interaksi\SDM\SDMExcel;
use App\Interaksi\Websoket;

class Pelanggaran
{
    public function index($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        $str = str();

        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $validator = SDMValidasi::validasiPencarianLapPelanggaranSDM([$reqs->all()]);

        if ($validator->fails()) {
            return $app->redirect->route('sdm.pelanggaran.data')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $kataKunci = $reqs->kata_kunci;
        $urutArray = $reqs->urut;
        $kunciUrut = array_filter((array) $urutArray);
        $uruts = $urutArray ? implode(',', $kunciUrut) : null;
        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $cari = SDMDBQuery::saringLapPelanggaranSDM($reqs, $kataKunci, $uruts, $lingkupIjin);

        if ($reqs->unduh == 'excel') {
            return SDMExcel::eksporExcelPencarianLapPelanggaranSDM($cari);
        }

        $cacheAtur = Cache::ambilCacheAtur();

        $data = [
            'tabels' => $cari->clone()->paginate($reqs->bph ?: 25)->withQueryString()->appends(['fragment' => 'pelanggaran-sdm_tabels', 'uuid' => $uuid ?? '']),
            'lokasis' => $cacheAtur->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin)->sortBy(['atur_jenis', 'asc'], ['atur_butir', 'asc']);
            }),
            'statusSDMs' => $cacheAtur->where('atur_jenis', 'STATUS KONTRAK')->sortBy(['atur_jenis', 'asc'], ['atur_butir', 'asc']),
            'urutTanggal' => $str->contains($uruts, 'langgar_tanggal'),
            'indexTanggal' => (head(array_keys($kunciUrut, 'langgar_tanggal ASC')) + head(array_keys($kunciUrut, 'langgar_tanggal DESC')) + 1),
            'urutNomor' => $str->contains($uruts, 'langgar_lap_no'),
            'indexNomor' => (head(array_keys($kunciUrut, 'langgar_lap_no ASC')) + head(array_keys($kunciUrut, 'langgar_lap_no DESC')) + 1),
            'halamanAkun' => $uuid ?? '',
            'jumlahOS' => $cari->clone()->whereNotNull('kontrak_t.penempatan_kontrak')->where('kontrak_t.penempatan_kontrak', 'like', 'OS-%')->count(),
            'jumlahOrganik' => $cari->clone()->whereNotNull('kontrak_t.penempatan_kontrak')->where('kontrak_t.penempatan_kontrak', 'not like', 'OS-%')->count()
        ];

        if (!isset($uuid)) {
            $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);
        }

        $HtmlPenuh = $app->view->make('sdm.pelanggaran.data', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');

        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
            ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function lihat($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $langgar = SDMDBQuery::ambilPelanggaran_SanksiSDM($uuid, array_filter(explode(',', $pengguna->sdm_ijin_akses)));

        abort_unless($langgar, 404, 'Data Laporan Pelanggaran tidak ditemukan.');

        $HtmlPenuh = $app->view->make('sdm.pelanggaran.lihat', compact('langgar'));
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function tambah()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        $str = str();

        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS']), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        if ($reqs->isMethod('post')) {

            $hitungNomor = SDMDBQuery::ambilUrutanPelanggaranSDM();

            $urutanLaporan = $hitungNomor + 1;

            $jmlTerlapor = count($reqs->langgar_no_absen) + $hitungNomor;

            $dataMap = array_map(function ($x, $y) use ($reqs) {
                return ['langgar_no_absen' => $x]
                    + ['langgar_lap_no' => date('Y') . date('m') . str($y)->padLeft(4, '0')]
                    + ['langgar_pelapor' => $reqs->langgar_pelapor]
                    + ['langgar_tanggal' => $reqs->langgar_tanggal]
                    + ['langgar_status' => 'DIPROSES']
                    + ['langgar_isi' => $reqs->langgar_isi]
                    + ['langgar_keterangan' => $reqs->langgar_keterangan]
                    + ['langgar_id_pembuat' => $reqs->user()->sdm_no_absen];
            }, $reqs->langgar_no_absen, range($urutanLaporan, $jmlTerlapor));

            $validasi = SDMValidasi::validasiTambahDataLapPelanggaranSDM($dataMap);

            $validasi->validate();

            $validasiBerkas = SDMValidasi::validasiBerkasLapPelanggaranSDM($reqs->only('berkas_laporan'));

            $validasiBerkas->validate();

            SDMDBQuery::tambahhDataLapPelanggaranSDM($validasi->validated());

            $berkas = $validasiBerkas->validated()['berkas_laporan'] ?? false;

            if ($berkas) {
                $nomorLaporan = $validasi->safe()->only(['*.langgar_lap_no'])['*']['langgar_lap_no'];

                SDMBerkas::simpanBerkasLapPelanggaranSDM($berkas, $nomorLaporan);
            }

            SDMCache::hapusCachePelanggaranSDMTerkini();

            $pesanSoket = $pengguna?->sdm_nama . ' telah menambah ' . $jmlTerlapor . ' laporan pelanggaran SDM baru pada ' . strtoupper($app->date->now()->translatedFormat('d F Y H:i:s'));

            Websoket::siaranUmum($pesanSoket);

            $redirect = $app->redirect;
            $perujuk = $reqs->session()->get('tautan_perujuk');
            $pesan = Rangka::statusBerhasil();

            return $str->contains($perujuk, ['pelanggaran'])
                ? $redirect->to($perujuk)->with('pesan', $pesan)
                : $redirect->route('sdm.pelanggaran.data')->with('pesan', $pesan);
        }

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $HtmlPenuh = $app->view->make('sdm.pelanggaran.tambah-ubah', [
            'sdms' => SDMCache::ambilCacheSDM()->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('penempatan_lokasi', $lingkupIjin);
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

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $langgar = SDMDBQuery::ambilPelanggaran_SanksiSDM($uuid, $lingkupIjin);

        abort_unless($langgar, 404, 'Data Laporan Pelanggaran tidak ditemukan.');

        $session = $reqs->session();
        $perujuk = $session->get('tautan_perujuk');
        $redirect = $app->redirect;
        $kesalahan = 'Laporan pelanggaran yang sudah dikenai sanksi tidak dapat diubah.';

        if ($langgar->final_sanksi_uuid) {
            return $perujuk
                ? $redirect->to($perujuk)->withErrors($kesalahan)
                : $redirect->route('sdm.pelanggaran.data')->withErrors($kesalahan);
        }

        if ($reqs->isMethod('post')) {

            $reqs->merge(['langgar_id_pengubah' => $pengguna->sdm_no_absen]);

            $aturan = [
                ...SDMValidasi::dasarValidasiLapPelanggaranSDM(),
                '*.langgar_id_pengubah' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
            ];

            if ($reqs->header('X-Minta-Javascript', false)) {
                $aturan = Arr::only($aturan, ['*.langgar_status', '*.langgar_id_pengubah']);
            }

            $validasi = SDMValidasi::validasiUbahDataLapPelanggaranSDM([$reqs->all()], $aturan);

            $validasi->validate();

            $validasiBerkas = SDMValidasi::validasiBerkasLapPelanggaranSDM($reqs->only('berkas_laporan'));

            $validasiBerkas->validate();

            $valid = $validasi->safe()->all()[0];

            SDMDBQuery::ubahDataLapPelanggaranSDM($valid, $uuid);

            $nomorLaporan = $langgar->langgar_lap_no;
            $berkas =  $validasiBerkas->validated()['berkas_laporan'] ?? false;

            if ($berkas) {
                SDMBerkas::simpanBerkasLapPelanggaranSDM($berkas, $nomorLaporan);
            }

            SDMCache::hapusCachePelanggaranSDMTerkini();

            $pesanSoket = $pengguna?->sdm_nama . ' telah mengubah laporan pelanggaran SDM nomor ' . $nomorLaporan . ' pada ' . strtoupper($app->date->now()->translatedFormat('d F Y H:i:s'));

            Websoket::siaranUmum($pesanSoket);

            $pesan = Rangka::statusBerhasil();

            if ($reqs->header('X-Minta-Javascript', false)) {
                $session->now('pesan', $pesan);
                return view('pemberitahuan');
            }

            return $perujuk
                ? $redirect->to($perujuk)->with('pesan', $pesan)
                : $redirect->route('sdm.pelanggaran.data')->with('pesan', $pesan);
        }

        $data = [
            'langgar' => $langgar,
            'sdms' => SDMCache::ambilCacheSDM()->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('penempatan_lokasi', $lingkupIjin);
            }),
        ];

        $HtmlPenuh = $app->view->make('sdm.pelanggaran.tambah-ubah', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }
}
