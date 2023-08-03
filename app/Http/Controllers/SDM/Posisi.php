<?php

namespace App\Http\Controllers\SDM;

use App\Interaksi\Cache;
use App\Interaksi\Berkas;
use App\Interaksi\Rangka;
use App\Interaksi\SDM\SDMCache;
use App\Interaksi\SDM\SDMExcel;
use App\Interaksi\SDM\SDMDBQuery;
use App\Interaksi\SDM\SDMValidasi;

class Posisi
{
    public function index()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        $str = str();

        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $validator = SDMValidasi::validasiPencarianPosisiSDM([$reqs->all()]);

        if ($validator->fails()) {
            return $app->redirect->route('sdm.posisi.data')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $urutArray = $reqs->urut;
        $uruts = $urutArray ? implode(',', array_filter($urutArray)) : null;
        $kataKunci = $reqs->kata_kunci;

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkupIjin = array_filter(explode(',', $ijin_akses));
        $lingkup_lokasi = collect($reqs->lokasi);
        $lingkup_akses = $lingkup_lokasi->intersect($lingkupIjin)->count();
        $maks_akses = collect($lingkupIjin)->count();
        $permin_akses = $lingkup_lokasi->count();

        abort_unless(blank($ijin_akses) || ($lingkup_akses <= $maks_akses && $maks_akses >= $permin_akses), 403, 'Akses lokasi lain dibatasi.');

        $cari = SDMDBQuery::saringPosisiSDM($kataKunci, $uruts);

        if ($reqs->unduh == 'excel') {
            return SDMExcel::eksporExcelPencarianPosisiSDM($cari);
        }

        $aktif = $cari->clone()->sum('jml_aktif');
        $nonAktif = $cari->clone()->sum('jml_nonaktif');
        $total = $aktif + $nonAktif;

        $tabels = $cari->clone()->paginate($reqs->bph ?: 25)->withQueryString()->appends(['fragment' => 'sdm_posisi_tabels']);

        $cacheAtur = Cache::ambilCacheAtur();

        $kunciUrut = array_filter((array) $urutArray);

        $urutPergantian = $str->contains($uruts, 'pergantian');
        $indexPergantian = (head(array_keys($kunciUrut, 'pergantian ASC')) + head(array_keys(array_filter((array)  $urutArray), 'pergantian DESC')) + 1);
        $urutPosisi = $str->contains($uruts, 'posisi_nama');
        $indexPosisi = (head(array_keys($kunciUrut, 'posisi_nama ASC')) + head(array_keys(array_filter((array)  $urutArray), 'posisi_nama DESC')) + 1);
        $urutAktif = $str->contains($uruts, 'jml_aktif');
        $indexAktif = (head(array_keys($kunciUrut, 'jml_aktif ASC')) + head(array_keys(array_filter((array)  $urutArray), 'jml_aktif DESC')) + 1);
        $urutNonAktif = $str->contains($uruts, 'jml_nonaktif');
        $indexNonAktif = (head(array_keys($kunciUrut, 'jml_nonaktif ASC')) + head(array_keys(array_filter((array)  $urutArray), 'jml_nonaktif DESC')) + 1);

        $data = [
            'tabels' => $tabels,
            'urutPergantian' => $urutPergantian,
            'indexPergantian' => $indexPergantian,
            'urutPosisi' => $urutPosisi,
            'indexPosisi' => $indexPosisi,
            'urutAktif' => $urutAktif,
            'indexAktif' => $indexAktif,
            'urutNonAktif' => $urutNonAktif,
            'indexNonAktif' => $indexNonAktif,
            'lokasis' => $cacheAtur->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin);
            })->sortBy(['atur_butir', 'asc']),
            'kontraks' => $cacheAtur->where('atur_jenis', 'STATUS KONTRAK')->sortBy(['atur_butir', 'asc']),
            'aktif' => $aktif,
            'nonAktif' => $nonAktif,
            'total' => $total
        ];

        $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);

        $HtmlPenuh = $app->view->make('sdm.posisi.data', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');

        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
            ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function lihat($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $pos = SDMDBQuery::ambilDBPosisiSDM()
            ->addSelect(
                'posisi_uuid',
                'posisi_atasan',
                'posisi_wlkp',
                'posisi_keterangan',
            )
            ->where('posisi_uuid', $uuid)->first();

        abort_unless($pos, 404, 'Data Jabatan tidak ditemukan.');

        $HtmlPenuh = $app->view->make('sdm.posisi.lihat', compact('pos'));
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function tambah()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        if ($reqs->isMethod('post')) {

            $reqs->merge(['posisi_id_pembuat' => $pengguna->sdm_no_absen]);

            $validasi = SDMValidasi::validasiTambahDataPosisiSDM([$reqs->all()]);

            $validasi->validate();

            $data = $validasi->safe()->all();

            SDMDBQuery::tambahDataPosisiSDM($data[0]);

            SDMCache::hapusCacheSDMUmum();

            return $app->redirect->route('sdm.posisi.data')->with('pesan', Rangka::statusBerhasil());
        }

        $data = [
            'posisis' => SDMCache::ambilCachePosisiSDM(),
        ];

        $HtmlPenuh = $app->view->make('sdm.posisi.tambah-ubah', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function ubah($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $pos = SDMDBQuery::ambilDBPosisiSDM()
            ->addSelect(
                'posisi_uuid',
                'posisi_atasan',
                'posisi_wlkp',
                'posisi_keterangan',
            )
            ->where('posisi_uuid', $uuid)->first();

        abort_unless($pos, 404, 'Data Jabatan tidak ditemukan.');

        if ($reqs->isMethod('post')) {

            $reqs->merge(['posisi_id_pengubah' => $pengguna->sdm_no_absen]);

            $validasi = SDMValidasi::validasiUbahDataPosisiSDM([$reqs->all()], $uuid);

            $validasi->validate();

            $data = $validasi->safe()->all();

            SDMDBQuery::ubahDataPosisiSDM($data[0], $uuid);

            SDMCache::hapusCacheSDMUmum();

            $perujuk = $reqs->session()->get('tautan_perujuk');
            $pesan = Rangka::statusBerhasil();
            $redirect = $app->redirect;

            return $perujuk
                ? $redirect->to($perujuk)->with('pesan', $pesan)
                : $redirect->route('sdm.posisi.data')->with('pesan', $pesan);
        }

        $data = [
            'posisis' => SDMCache::ambilCachePosisiSDM(),
            'pos' => $pos
        ];

        $HtmlPenuh = $app->view->make('sdm.posisi.tambah-ubah', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function contohUnggahPosisiSDM()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        abort_unless($app->filesystem->exists("contoh/unggah-umum.xlsx"), 404, 'Berkas Contoh Ekspor Tidak Ditemukan.');

        return SDMExcel::eksporExcelContohUnggahPosisiSDM(
            SDMDBQuery::ambilDBPosisiSDM()
                ->addSelect(
                    'posisi_atasan',
                    'posisi_wlkp',
                    'posisi_keterangan'
                )
                ->orderBy('posisis.id', 'desc')
        );
    }

    public function unggahPosisiSDM()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        if ($reqs->isMethod('post')) {
            $validasifile = SDMValidasi::validasiBerkasImporDataPosisiSDM($reqs->all());

            $validasifile->validate();

            $file = $validasifile->safe()->only('posisi_unggah')['posisi_unggah'];
            $namafile = 'unggahjabatansdm-' . date('YmdHis') . '.xlsx';

            Berkas::simpanBerkasImporExcelSementara($file, $namafile);

            $fileexcel = Berkas::ambilBerkasImporExcelSementara($namafile);

            return SDMExcel::imporExcelDataPosisiSDM($fileexcel);
        }

        $HtmlPenuh = $app->view->make('sdm.posisi.unggah');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }
}
