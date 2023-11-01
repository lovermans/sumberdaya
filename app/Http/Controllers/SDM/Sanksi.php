<?php

namespace App\Http\Controllers\SDM;

use App\Interaksi\Berkas;
use App\Interaksi\Cache;
use App\Interaksi\Excel;
use App\Interaksi\Rangka;
use App\Interaksi\SDM\SDMBerkas;
use App\Interaksi\SDM\SDMCache;
use App\Interaksi\SDM\SDMDBQuery;
use App\Interaksi\SDM\SDMExcel;
use App\Interaksi\SDM\SDMValidasi;
use App\Interaksi\Validasi;
use App\Interaksi\Websoket;
use Illuminate\Support\Arr;

class Sanksi
{
    public function index($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        $aksesAkun = $uuid ? SDMDBQuery::aksesAkun($uuid) : null;

        $str = str();

        abort_unless(
            $pengguna && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN'])
            || ($pengguna->sdm_uuid == $uuid && $pengguna->sdm_uuid !== null)
            || ($aksesAkun && $pengguna->sdm_id_atasan == $aksesAkun?->sdm_no_absen)
            || ($aksesAkun && $pengguna->sdm_no_absen == $aksesAkun?->sdm_id_atasan)
            || ($aksesAkun && $pengguna->sdm_id_atasan == $aksesAkun?->sdm_id_atasan),
            403,
            'Akses dibatasi hanya untuk Pemangku SDM.'
        );

        $validator = SDMValidasi::validasiPencarianSanksiSDM([$reqs->all()]);

        if ($validator->fails()) {
            return $app->redirect->route('sdm.sanksi.data')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        }

        $lingkupIjin = array_filter(explode(',', $pengguna?->sdm_ijin_akses));
        $urutArray = $reqs->urut;
        $kunciUrut = array_filter((array) $urutArray);
        $uruts = $urutArray ? implode(',', $kunciUrut) : null;

        $cari = SDMDBQuery::cariDataSanksiSDM(
            $reqs,
            $app->date->today()->format('Y-m-d'),
            $reqs->kata_kunci,
            $uuid,
            $lingkupIjin,
            $uruts
        );

        if ($reqs->unduh == 'excel') {
            return SDMExcel::eksporExcelPencarianSanksiSDM($cari);
        }

        $cacheAtur = Cache::ambilCacheAtur();

        $data = [
            'tabels' => $cari->clone()->paginate($reqs->bph ?: 25)->withQueryString()->appends(['fragment' => 'sanksi-sdm_tabels', 'uuid' => $uuid ?? '']),
            'lokasis' => $cacheAtur->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin)->sortBy(['atur_butir', 'asc']);
            }),
            'statusSDMs' => $cacheAtur->where('atur_jenis', 'STATUS KONTRAK')->sortBy(['atur_jenis', 'asc'], ['atur_butir', 'asc']),
            'jenisSanksis' => $cacheAtur->where('atur_jenis', 'SANKSI SDM')->sortBy(['atur_jenis', 'asc'], ['atur_butir', 'desc']),
            'urutTanggalMulai' => $str->contains($uruts, 'sanksi_mulai'),
            'indexTanggalMulai' => (head(array_keys($kunciUrut, 'sanksi_mulai ASC')) + head(array_keys($kunciUrut, 'sanksi_mulai DESC')) + 1),
            'urutTanggalSelesai' => $str->contains($uruts, 'sanksi_selesai'),
            'indexTanggalSelesai' => (head(array_keys($kunciUrut, 'sanksi_selesai ASC')) + head(array_keys($kunciUrut, 'sanksi_selesai DESC')) + 1),
            'halamanAkun' => $uuid ?? '',
            'jumlahOS' => $cari->clone()->whereNotNull('kontrak_t.penempatan_kontrak')->where('kontrak_t.penempatan_kontrak', 'like', 'OS-%')->count(),
            'jumlahOrganik' => $cari->clone()->whereNotNull('kontrak_t.penempatan_kontrak')->where('kontrak_t.penempatan_kontrak', 'not like', 'OS-%')->count(),
        ];

        if (! isset($uuid)) {
            $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);
        }

        $HtmlPenuh = $app->view->make('sdm.sanksi.data', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');

        return $reqs->pjax() && (! $reqs->filled('fragment') || ! $reqs->header('X-Frag', false))
            ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function lihat($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $sanksi = SDMDBQuery::ambilDataSanksi_PelanggaranSDM($uuid, array_filter(explode(',', $pengguna->sdm_ijin_akses)));

        abort_unless($sanksi, 404, 'Data Laporan Pelanggaran tidak ditemukan.');

        $HtmlPenuh = $app->view->make('sdm.sanksi.lihat', compact('sanksi'));
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function tambah($lap_uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $lap_uuid && str()->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $laporan = SDMDBQuery::ambilDataPelanggaranSDM($lap_uuid, array_filter(explode(',', $pengguna->sdm_ijin_akses)));

        abort_unless($laporan, 404, 'Data Laporan Pelanggaran tidak ditemukan.');

        if ($reqs->isMethod('post')) {

            $reqs->merge(
                [
                    'sanksi_id_pembuat' => $pengguna->sdm_no_absen,
                    'sanksi_lap_no' => $laporan->langgar_lap_no,
                    'sanksi_no_absen' => $laporan->langgar_no_absen,
                ]
            );

            $validasi = SDMValidasi::validasiTambahDataSanksiSDM([$reqs->all()]);

            $validasi->validate();

            $redirect = $app->redirect;
            $perujuk = $reqs->session()->get('tautan_perujuk');
            $kesalahan = 'Laporan pelanggaran yang dibatalkan tidak dapat dikenai sanksi.';

            if ($laporan->langgar_status == 'DIBATALKAN') {
                return $perujuk
                    ? $redirect->to($perujuk)->withErrors($kesalahan)
                    : $redirect->route('sdm.pelanggaran.data')->withErrors($kesalahan);
            }

            $valid = $validasi->safe()->all()[0];

            $data = Arr::except($valid, ['sanksi_berkas']);

            SDMDBQuery::tambahDataSanksiSDM($data);

            $berkas = Arr::only($valid, ['sanksi_berkas'])['sanksi_berkas'] ?? false;

            if ($berkas) {
                $namaBerkas = Arr::only($valid, ['sanksi_no_absen'])['sanksi_no_absen'].' - '.Arr::only($valid, ['sanksi_jenis'])['sanksi_jenis'].' - '.Arr::only($valid, ['sanksi_mulai'])['sanksi_mulai'].'.pdf';

                SDMBerkas::simpanBerkasSanksiSDM($berkas, $namaBerkas);
            }

            SDMCache::hapusCachePelanggaranSDM();
            SDMCache::hapusCacheSanksiSDM();

            $pesanSoket = $pengguna?->sdm_nama.' telah menambah data Sanksi SDM nomor absen '
                .Arr::only($valid, ['sanksi_no_absen'])['sanksi_no_absen'].' tanggal '.Arr::only($valid, ['sanksi_mulai'])['sanksi_mulai']
                .' pada '.strtoupper($app->date->now()->translatedFormat('d F Y H:i:s'));

            Websoket::siaranUmum($pesanSoket);

            $pesan = Rangka::statusBerhasil();

            return $perujuk
                ? $redirect->to($perujuk)->with('pesan', $pesan)
                : $redirect->route('sdm.pelanggaran.data')->with('pesan', $pesan);
        }

        $HtmlPenuh = $app->view->make(
            'sdm.sanksi.tambah-ubah',
            [
                'sanksis' => Cache::ambilCacheAtur()->where('atur_jenis', 'SANKSI SDM')->sortBy(['atur_jenis', 'asc'], ['atur_butir', 'desc']),
            ]
        );

        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function ubah($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $sanksiLama = SDMDBQuery::ambilDataSanksiSDM($uuid, array_filter(explode(',', $pengguna->sdm_ijin_akses)));

        abort_unless($sanksiLama, 404, 'Data Laporan Pelanggaran tidak ditemukan.');

        if ($reqs->isMethod('post')) {

            $reqs->merge(['sanksi_id_pengubah' => $pengguna->sdm_no_absen]);

            $validasi = SDMValidasi::validasiUbahDataSanksiSDM([$reqs->all()]);

            $validasi->validate();

            $redirect = $app->redirect;
            $perujuk = $reqs->session()->get('tautan_perujuk');
            $kesalahan = 'Laporan pelanggaran yang dibatalkan tidak dapat dikenai sanksi.';

            if ($sanksiLama->langgar_status == 'DIBATALKAN') {
                return $perujuk
                    ? $redirect->to($perujuk)->withErrors($kesalahan)
                    : $redirect->route('sdm.pelanggaran.data')->withErrors($kesalahan);
            }

            $valid = $validasi->safe()->all()[0];

            $data = Arr::except($valid, ['sanksi_berkas']);

            SDMDBQuery::ubahDataSanksiSDM($uuid, $data);

            $berkas = Arr::only($valid, ['sanksi_berkas'])['sanksi_berkas'] ?? false;

            if ($berkas) {
                $namaBerkas = $sanksiLama->sanksi_no_absen.' - '.Arr::only($valid, ['sanksi_jenis'])['sanksi_jenis'].' - '.Arr::only($valid, ['sanksi_mulai'])['sanksi_mulai'].'.pdf';

                SDMBerkas::simpanBerkasSanksiSDM($berkas, $namaBerkas);
            }

            SDMCache::hapusCachePelanggaranSDM();
            SDMCache::hapusCacheSanksiSDM();

            $pesanSoket = $pengguna?->sdm_nama.' telah mengubah data Sanksi SDM nomor absen '
                .$sanksiLama->sanksi_no_absen.' tanggal '.Arr::only($valid, ['sanksi_mulai'])['sanksi_mulai']
                .' pada '.strtoupper($app->date->now()->translatedFormat('d F Y H:i:s'));

            Websoket::siaranUmum($pesanSoket);

            $pesan = Rangka::statusBerhasil();

            return $perujuk
                ? $redirect->to($perujuk)->with('pesan', $pesan)
                : $redirect->route('sdm.sanksi.data')->with('pesan', $pesan);
        }

        $data = [
            'sanksiLama' => $sanksiLama,
            'sanksis' => Cache::ambilCacheAtur()->where('atur_jenis', 'SANKSI SDM')->sortBy(['atur_jenis', 'asc'], ['atur_butir', 'desc']),
            'lapPelanggaran' => SDMDBQuery::ambilPelanggaranSDMTerkini()->where('langgar_no_absen', $sanksiLama->sanksi_no_absen)->get(),
        ];

        $HtmlPenuh = $app->view->make('sdm.sanksi.tambah-ubah', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function contohUnggahSanksiSDM()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        abort_unless($app->filesystem->exists('contoh/unggah-umum.xlsx'), 404, 'Berkas Contoh Ekspor Tidak Ditemukan.');

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $cari = SDMDBQuery::ambilSanksiSDM()
            ->addSelect('sdm_nama')
            ->join('sdms', 'sanksi_no_absen', '=', 'sdm_no_absen')
            ->leftJoinSub(SDMDBQuery::ambilDBPenempatanSDMTerkini(), 'kontrak_t', function ($join) {
                $join->on('sanksi_no_absen', '=', 'kontrak_t.penempatan_no_absen');
            })
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->whereIn('kontrak_t.penempatan_lokasi', $lingkupIjin);
                });
            })
            ->orderBy('sanksisdms.id');

        return SDMExcel::eksporExcelContohUnggahSanksiSDM($cari);
    }

    public function unggahSanksiSDM()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        if ($reqs->isMethod('post')) {
            $validasifile = SDMValidasi::validasiBerkasImporDataSanksiSDM($reqs->all());

            $validasifile->validate();

            $file = $validasifile->safe()->only('unggah_sanksi_sdm')['unggah_sanksi_sdm'];
            $namafile = 'unggahsanksisdm-'.date('YmdHis').'.xlsx';

            Berkas::simpanBerkasImporExcelSementara($file, $namafile);

            $fileexcel = Berkas::ambilBerkasImporExcelSementara($namafile);

            return SDMExcel::imporExcelDataSanksiSDM($fileexcel);
        }

        $HtmlPenuh = $app->view->make('sdm.sanksi.unggah');
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

        $sanksi = SDMDBQuery::ambilDataSanksiSDM($uuid, array_filter(explode(',', $pengguna->sdm_ijin_akses)));

        abort_unless($sanksi, 404, 'Data Sanksi SDM tidak ditemukan.');

        if ($reqs->isMethod('post')) {
            abort_unless($app->filesystem->exists('contoh/data-dihapus.xlsx'), 404, 'Berkas riwayat penghapusan tidak ditemukan.');

            $reqs->merge(['id_penghapus' => $pengguna->sdm_no_absen, 'waktu_dihapus' => $app->date->now()]);

            $validasi = Validasi::validasiHapusDataDB($reqs->all());

            $validasi->validate();

            $dataValid = $validasi->validated();

            Excel::cadangkanPenghapusanDatabase([
                'Sanksi SDM',
                collect($sanksi)->toJson(),
                $dataValid['id_penghapus'],
                $dataValid['waktu_dihapus']->format('Y-m-d H:i:s'),
                $dataValid['alasan'],
            ]);

            SDMDBQuery::hapusDataSanksiSDM($uuid);

            $namaBerkas = $sanksi->sanksi_no_absen.' - '.$sanksi->sanksi_jenis.' - '.$sanksi->sanksi_mulai.'.pdf';

            SDMBerkas::hapusBerkasSanksiSDM($namaBerkas);

            SDMCache::hapusCachePelanggaranSDM();
            SDMCache::hapusCacheSanksiSDM();

            $pesanSoket = $pengguna?->sdm_nama.' telah menghapus data Sanksi SDM '.$sanksi->sanksi_no_absen.' - '.$sanksi->sanksi_jenis.' - '.$sanksi->sanksi_mulai.' pada '.strtoupper($app->date->now()->translatedFormat('d F Y H:i:s'));

            Websoket::siaranUmum($pesanSoket);

            $perujuk = $reqs->session()->get('tautan_perujuk');
            $pesan = 'Data berhasil dihapus';
            $redirect = $app->redirect;

            return $perujuk
                ? $redirect->to($perujuk)->with('pesan', $pesan)
                : $redirect->route('sdm.sanksi.data')->with('pesan', $pesan);
        }

        $HtmlPenuh = $app->view->make('sdm.sanksi.hapus', compact('sanksi'));
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }
}
