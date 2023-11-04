<?php

namespace App\Http\Controllers\SDM;

use App\Interaksi\Cache;
use App\Interaksi\Rangka;
use App\Interaksi\SDM\SDMBerkas;
use App\Interaksi\SDM\SDMCache;
use App\Interaksi\SDM\SDMDBQuery;
use App\Interaksi\SDM\SDMExcel;
use App\Interaksi\SDM\SDMValidasi;
use App\Interaksi\Websoket;
use Illuminate\Support\Arr;

class Kepuasan
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

        $validator = SDMValidasi::validasiPencarianKepuasanSDM([$reqs->all()]);

        if ($validator->fails()) {
            return $app->redirect->route('sdm.kepuasan.data')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        }

        $lingkupIjin = array_filter(explode(',', $pengguna?->sdm_ijin_akses));

        $urutArray = $reqs->urut;
        $kunciUrut = array_filter((array) $urutArray);
        $uruts = $urutArray ? implode(',', $kunciUrut) : null;

        $cari = SDMDBQuery::cariKepuasanSDM($reqs, $reqs->kata_kunci, $uuid, $lingkupIjin, $uruts);

        if ($reqs->unduh == 'excel') {
            return SDMExcel::eksporExcelPencarianKepuasanSDM($cari);
        }

        $cacheAtur = Cache::ambilCacheAtur();

        $data = [
            'tabels' => $cari->clone()->paginate($reqs->bph ?: 25)->withQueryString()->appends(['fragment' => 'kepuasan-sdm_tabels', 'uuid' => $uuid ?? '']),
            'lokasis' => $cacheAtur->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin)->sortBy(['atur_butir', 'asc']);
            }),
            'statusSDMs' => $cacheAtur->where('atur_jenis', 'STATUS KONTRAK')->sortBy(['atur_jenis', 'asc'], ['atur_butir', 'asc']),
            'urutTahun' => $str->contains($uruts, 'surveysdm_tahun'),
            'indexTahun' => (head(array_keys($kunciUrut, 'surveysdm_tahun ASC')) + head(array_keys($kunciUrut, 'surveysdm_tahun DESC')) + 1),
            'halamanAkun' => $uuid ?? '',
            'jumlahOS' => $cari->clone()->whereNotNull('kontrak.penempatan_kontrak')->where('kontrak.penempatan_kontrak', 'like', 'OS-%')->count(),
            'jumlahOrganik' => $cari->clone()->whereNotNull('kontrak.penempatan_kontrak')->where('kontrak.penempatan_kontrak', 'not like', 'OS-%')->count(),
        ];

        if (! isset($uuid)) {
            $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);
        }

        $HtmlPenuh = $app->view->make('sdm.kepuasan.data', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');

        return $reqs->pjax() && (! $reqs->filled('fragment') || ! $reqs->header('X-Frag', false))
            ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function lihat($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $kepuasan = SDMDBQuery::ambilDataKepuasanSDM($uuid, array_filter(explode(',', $pengguna->sdm_ijin_akses)));

        abort_unless($kepuasan, 404, 'Data Penialain SDM tidak ditemukan.');

        $HtmlPenuh = $app->view->make('sdm.kepuasan.lihat', compact('kepuasan'));
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

            $reqs->merge(['surveysdm_id_pembuat' => $pengguna->sdm_no_absen]);

            $validasi = SDMValidasi::validasiTambahDataKepuasanSDM([$reqs->all()]);

            $validasi->validate();

            $valid = $validasi->safe()->all()[0];

            $data = Arr::except($valid, ['surveysdm_berkas']);

            SDMDBQuery::tambahDataKepuasanSDM($data);

            $berkas = Arr::only($valid, ['surveysdm_berkas'])['surveysdm_berkas'] ?? false;

            if ($berkas) {
                $namaBerkas = Arr::only($valid, ['surveysdm_no_absen'])['surveysdm_no_absen'].' - '.Arr::only($valid, ['surveysdm_tahun'])['surveysdm_tahun'].'.pdf';

                SDMBerkas::simpanBerkasKepuasanSDM($berkas, $namaBerkas);
            }

            // SDMCache::hapusCacheNilaiSDM();

            $pesanSoket = $pengguna?->sdm_nama.' telah menambah data Survey Kepuasan SDM nomor absen '
                .Arr::only($valid, ['surveysdm_no_absen'])['surveysdm_no_absen'].' Tahun '.Arr::only($valid, ['surveysdm_tahun'])['surveysdm_tahun'].' pada '.strtoupper($app->date->now()->translatedFormat('d F Y H:i:s'));

            Websoket::siaranUmum($pesanSoket);

            $perujuk = $reqs->session()->get('tautan_perujuk');
            $redirect = $app->redirect;
            $pesan = Rangka::statusBerhasil();

            return $perujuk
                ? $redirect->to($perujuk)->with('pesan', $pesan)
                : $redirect->route('sdm.kepuasan.data')->with('pesan', $pesan);
        }

        $HtmlPenuh = $app->view->make('sdm.kepuasan.tambah-ubah', [
            'sdms' => SDMCache::ambilCacheSDM()->when($lingkupIjin, function ($c) use ($lingkupIjin) {
                return $c->whereIn('penempatan_lokasi', [null, ...$lingkupIjin]);
            }),
            'jawabans' => [
                ['value' => 5, 'text' => 'SANGAT SETUJU'],
                ['value' => 4, 'text' => 'SETUJU'],
                ['value' => 3, 'text' => 'RAGU'],
                ['value' => 2, 'text' => 'KURANG SETUJU'],
                ['value' => 1, 'text' => 'TIDAK SETUJU'],
            ],
        ]);

        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function unggahKepuasanSDM()
    {

    }

    public function contohUnggahKepuasanSDM()
    {

    }

    public function ubah()
    {

    }
}
