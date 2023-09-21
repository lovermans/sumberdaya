<?php

namespace App\Http\Controllers\SDM;

use App\Interaksi\Cache;
use App\Interaksi\Excel;
use App\Interaksi\Rangka;
use App\Interaksi\SDM\SDMBerkas;
use App\Interaksi\SDM\SDMCache;
use App\Interaksi\SDM\SDMDBQuery;
use App\Interaksi\SDM\SDMExcel;
use App\Interaksi\SDM\SDMValidasi;
use App\Interaksi\SDM\SDMWord;
use App\Interaksi\Validasi;
use App\Interaksi\Websoket;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class PermintaanTambahSDM
{
    public function index()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        $str = str();

        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $validator = SDMValidasi::validasiPencarianPermintaanTambahSDM([$reqs->all()]);

        if ($validator->fails()) {
            return $app->redirect->route('sdm.permintaan-tambah-sdm.data')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        }

        $kataKunci = $reqs->kata_kunci;
        $urutArray = $reqs->urut;
        $kunciUrut = array_filter((array) $urutArray);
        $uruts = $urutArray ? implode(',', $kunciUrut) : null;
        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $cari = SDMDBQuery::ambilPencarianPermintaanTambahSDM($reqs, $kataKunci, $uruts, $lingkupIjin);

        if ($reqs->unduh == 'excel') {
            return SDMExcel::eksporExcelPencarianPermintaanTambahSDM($cari);
        }

        $kebutuhan = $cari->clone()->where('tambahsdm_status', '=', 'DISETUJUI')->sum('tambahsdm_jumlah');
        $terpenuhi = $cari->clone()->where('tambahsdm_status', '=', 'DISETUJUI')->sum('tambahsdm_terpenuhi');
        $selisih = $terpenuhi - $kebutuhan;

        $cacheAtur = Cache::ambilCacheAtur();

        $data = [
            'tabels' => $cari->clone()->paginate($reqs->bph ?: 25)->withQueryString()->appends(['fragment' => 'tambah_sdm_tabels']),
            'statuses' => $cacheAtur->where('atur_jenis', 'STATUS PERMOHONAN')->sortBy(['atur_butir', 'asc']),
            'lokasis' => $cacheAtur->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin)->sortBy(['atur_butir', 'asc']);
            }),
            'urutPenempatan' => $str->contains($uruts, 'tambahsdm_penempatan'),
            'indexPenempatan' => (head(array_keys($kunciUrut, 'tambahsdm_penempatan ASC')) + head(array_keys($kunciUrut, 'tambahsdm_penempatan DESC')) + 1),
            'urutPosisi' => $str->contains($uruts, 'tambahsdm_posisi'),
            'indexPosisi' => (head(array_keys($kunciUrut, 'tambahsdm_posisi ASC')) + head(array_keys($kunciUrut, 'tambahsdm_posisi DESC')) + 1),
            'urutJumlah' => $str->contains($uruts, 'tambahsdm_jumlah'),
            'indexJumlah' => (head(array_keys($kunciUrut, 'tambahsdm_jumlah ASC')) + head(array_keys($kunciUrut, 'tambahsdm_jumlah DESC')) + 1),
            'urutNomor' => $str->contains($uruts, 'tambahsdm_no'),
            'indexNomor' => (head(array_keys($kunciUrut, 'tambahsdm_no ASC')) + head(array_keys($kunciUrut, 'tambahsdm_no DESC')) + 1),
            'posisis' => SDMCache::ambilCachePosisiSDM(),
            'kebutuhan' => $kebutuhan,
            'terpenuhi' => $terpenuhi,
            'selisih' => $selisih,
        ];

        $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);

        $HtmlPenuh = $app->view->make('sdm.permintaan-tambah-sdm.data', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');

        return $reqs->pjax() && (! $reqs->filled('fragment') || ! $reqs->header('X-Frag', false))
            ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function lihat($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $permin = SDMDBQuery::ambilDBPermintaanTambahSDM()
            ->when($lingkupIjin, function ($query, $lingkupIjin) {
                $query->whereIn('tambahsdm_penempatan', $lingkupIjin);
            })
            ->where('tambahsdm_uuid', $uuid)
            ->first();

        abort_unless($permin, 404, 'Data Permintaan Tambah SDM tidak ditemukan.');

        $HtmlPenuh = $app->view->make('sdm.permintaan-tambah-sdm.lihat', compact('permin'));
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function tambah()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS']), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        if ($reqs->isMethod('post')) {

            $hitungPermintaan = SDMDBQuery::ambilUrutanPermintaanTambahSDM();

            $maxno = $hitungPermintaan->maxno ? (intval($hitungPermintaan->maxno) - (intval(date('Y').date('m')) * 10000)) : 0;

            $urutanPermintaan = max($maxno, $hitungPermintaan->countno) + 1;

            $nomorPermintaan = date('Y').date('m').str($urutanPermintaan)->padLeft(4, '0');

            $reqs->merge(['tambahsdm_id_pembuat' => $pengguna->sdm_no_absen, 'tambahsdm_no' => $nomorPermintaan]);

            $validasi = SDMValidasi::validasiTambahDataPermintaanTambahSDM([$reqs->all()]);

            $validasi->validate();

            $valid = $validasi->safe()->all()[0];

            $data = Arr::except($valid, ['tambahsdm_berkas']);

            SDMDBQuery::tambahDataPermintaanTambahSDM($data);

            $berkas = Arr::only($valid, ['tambahsdm_berkas'])['tambahsdm_berkas'] ?? false;

            if ($berkas) {
                SDMBerkas::simpanBerkasPermintaanTambahSDM($berkas, $nomorPermintaan);
            }

            SDMCache::hapusCacheSDMUmum();

            $pesanSoket = $pengguna?->sdm_nama.' telah menambah data Permintaan Tambah SDM pada '.strtoupper($app->date->now()->translatedFormat('d F Y H:i:s'));

            Websoket::siaranUmum($pesanSoket);

            $redirect = $app->redirect;
            $perujuk = $reqs->session()->get('tautan_perujuk');
            $pesan = Rangka::statusBerhasil();

            return $perujuk
                ? $redirect->to($perujuk)->with('pesan', $pesan)
                : $redirect->route('sdm.permintaan-tambah-sdm.data')->with('pesan', $pesan);
        }

        $aturs = Cache::ambilCacheAtur();
        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $data = [
            'penempatans' => $aturs->where('atur_jenis', 'PENEMPATAN')->sortBy(['atur_butir', 'asc'])->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin);
            }),
            'posisis' => SDMCache::ambilCachePosisiSDM(),
            'statuses' => $aturs->where('atur_jenis', 'STATUS PERMOHONAN')->sortBy(['atur_butir', 'asc']),
            'sdms' => SDMCache::ambilCacheSDM()->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('penempatan_lokasi', $lingkupIjin);
            }),
        ];

        $HtmlPenuh = $app->view->make('sdm.permintaan-tambah-sdm.tambah-ubah', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function ubah($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS']), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $permin = SDMDBQuery::ambilDBPermintaanTambahSDM()
            ->when($lingkupIjin, function ($query, $lingkupIjin) {
                $query->whereIn('tambahsdm_penempatan', $lingkupIjin);
            })
            ->where('tambahsdm_uuid', $uuid)
            ->first();

        abort_unless($permin, 404, 'Data Permintaan Tambah SDM tidak ditemukan.');

        if ($reqs->isMethod('post')) {

            $reqs->merge(['tambahsdm_id_pengubah' => $pengguna->sdm_no_absen]);

            $aturan = [
                '*.tambahsdm_no' => ['required', 'string', 'max:40', Rule::unique('tambahsdms')->where(fn ($query) => $query->whereNot('tambahsdm_uuid', $uuid))],
                '*.tambahsdm_id_pengubah' => ['required', 'nullable', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                ...SDMValidasi::dasarValidasiPermintaanTambahSDM(),
            ];

            if ($reqs->header('X-Minta-Javascript', false)) {
                $aturan = Arr::only($aturan, ['*.tambahsdm_status', '*.tambahsdm_id_pengubah']);
            }

            $validasi = SDMValidasi::validasiUbahDataPermintaanTambahSDM([$reqs->all()], $aturan);

            $validasi->validate();

            $valid = $validasi->safe()->all()[0];

            $data = Arr::except($valid, ['tambahsdm_berkas']);

            SDMDBQuery::ubahDataPermintaanTambahSDM($data, $uuid);

            $nomorPermintaan = Arr::only($valid, ['tambahsdm_no'])['tambahsdm_no'] ?? $permin->tambahsdm_no;
            $berkas = Arr::only($valid, ['tambahsdm_berkas'])['tambahsdm_berkas'] ?? false;

            if ($berkas) {
                SDMBerkas::simpanBerkasPermintaanTambahSDM($berkas, $nomorPermintaan);
            }

            SDMCache::hapusCacheSDMUmum();

            $pesanSoket = $pengguna?->sdm_nama.' telah mengubah data Permintaan Tambah SDM nomor '.$nomorPermintaan.' pada '.strtoupper($app->date->now()->translatedFormat('d F Y H:i:s'));

            Websoket::siaranUmum($pesanSoket);

            $pesan = Rangka::statusBerhasil();
            $session = $reqs->session();

            if ($reqs->header('X-Minta-Javascript', false)) {
                $session->now('pesan', $pesan);

                return view('pemberitahuan');
            }

            $perujuk = $session->get('tautan_perujuk');
            $redirect = $app->redirect;

            return $perujuk
                ? $redirect->to($perujuk)->with('pesan', $pesan)
                : $redirect->route('sdm.permintaan-tambah-sdm.data')->with('pesan', $pesan);
        }

        $aturs = Cache::ambilCacheAtur();

        $data = [
            'permin' => $permin,
            'penempatans' => $aturs->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin);
            })->sortBy(['atur_butir', 'asc']),
            'posisis' => SDMCache::ambilCachePosisiSDM(),
            'statuses' => $aturs->where('atur_jenis', 'STATUS PERMOHONAN')->sortBy(['atur_butir', 'asc']),
            'sdms' => SDMCache::ambilCacheSDM()->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('penempatan_lokasi', $lingkupIjin);
            }),
        ];

        $HtmlPenuh = $app->view->make('sdm.permintaan-tambah-sdm.tambah-ubah', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function hapus($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $permin = SDMDBQuery::ambilDBPermintaanTambahSDM()
            ->when($lingkupIjin, function ($query, $lingkupIjin) {
                $query->whereIn('tambahsdm_penempatan', $lingkupIjin);
            })
            ->where('tambahsdm_uuid', $uuid)
            ->first();

        abort_unless($permin, 404, 'Data Permintaan Tambah SDM tidak ditemukan.');

        if ($reqs->isMethod('post')) {
            abort_unless($app->filesystem->exists('contoh/data-dihapus.xlsx'), 404, 'Berkas riwayat penghapusan tidak ditemukan.');

            $reqs->merge(['id_penghapus' => $pengguna->sdm_no_absen, 'waktu_dihapus' => $app->date->now()]);

            $validasi = Validasi::validasiHapusDataDB($reqs->all());

            $validasi->validate();

            $dataValid = $validasi->validated();

            Excel::cadangkanPenghapusanDatabase([
                'Permintaan Tambah SDM',
                collect($permin)->toJson(),
                $dataValid['id_penghapus'],
                $dataValid['waktu_dihapus']->format('Y-m-d H:i:s'),
                $dataValid['alasan'],
            ]);

            SDMDBQuery::hapusDataPermintaanTambahSDM($uuid);

            SDMBerkas::hapusBerkasPermintaanTambahSDM($permin->tambahsdm_no);

            SDMCache::hapusCacheSDMUmum();

            $pesanSoket = $pengguna?->sdm_nama.' telah menghapus data Permintaan Tambah SDM nomor '.$permin->tambahsdm_no.' pada '.strtoupper($app->date->now()->translatedFormat('d F Y H:i:s'));

            Websoket::siaranUmum($pesanSoket);

            $perujuk = $reqs->session()->get('tautan_perujuk');
            $pesan = 'Data berhasil dihapus';
            $redirect = $app->redirect;

            return $perujuk
                ? $redirect->to($perujuk)->with('pesan', $pesan)
                : $redirect->route('sdm.permintaan-tambah-sdm.data')->with('pesan', $pesan);
        }

        $HtmlPenuh = $app->view->make('sdm.permintaan-tambah-sdm.hapus', compact('permin'));
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function formulirPermintaanTambahSDM($uuid = null)
    {
        return SDMWord::formulirPermintaanTambahSDM($uuid);
    }
}
