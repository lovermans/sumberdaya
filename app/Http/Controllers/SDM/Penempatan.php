<?php

namespace App\Http\Controllers\SDM;

use App\Interaksi\Cache;
use App\Interaksi\Excel;
use App\Interaksi\Berkas;
use App\Interaksi\Rangka;
use App\Interaksi\Validasi;
use App\Interaksi\Websoket;
use Illuminate\Support\Arr;
use App\Interaksi\SDM\SDMCache;
use App\Interaksi\SDM\SDMExcel;
use App\Interaksi\SDM\SDMBerkas;
use App\Interaksi\SDM\SDMDBQuery;
use App\Interaksi\SDM\SDMValidasi;

class Penempatan
{
    public function index($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        $aksesAkun = $uuid ? SDMDBQuery::aksesAkun($uuid) : null;

        abort_unless(
            $pengguna && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN'])
                || ($pengguna?->sdm_uuid == $uuid && $pengguna?->sdm_uuid !== null)
                || ($pengguna?->sdm_id_atasan == $aksesAkun?->sdm_no_absen)
                || ($pengguna?->sdm_id_atasan == $aksesAkun?->sdm_id_atasan),
            403,
            'Akses dibatasi hanya untuk Pemangku SDM.'
        );

        $validator = SDMValidasi::validasiPencarianPenempatanSDM([$reqs->all()]);

        if ($validator->fails()) {
            return $app->redirect->route('sdm.penempatan.riwayat')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $lingkupIjin = array_filter(explode(',', $pengguna?->sdm_ijin_akses));
        $urutArray = $reqs->urut;
        $kunciUrut = array_filter((array) $urutArray);
        $uruts = $urutArray ? implode(',', $kunciUrut) : null;

        $cari = SDMDBQuery::ambilSemuaRiwayatPenempatanSDM($reqs, $reqs->kata_kunci, $lingkupIjin, $uruts, $uuid);

        if ($reqs->unduh == 'excel') {
            return SDMExcel::eksporExcelSemuRiwayatPenempatanSDM($cari, $app);
        }

        $data = [
            'tabels' => $cari->clone()->paginate($reqs->bph ?: 100)->withQueryString()->appends(['fragment' => 'riwa-penem-sdm_tabels', 'uuid' => $uuid ?? '']),
            'halamanAkun' => $uuid ?? '',
            ...$this->kirimData($lingkupIjin, $uruts, $kunciUrut, $cari)
        ];

        return $this->tampilkanDataPenempatanSDM($data, $uuid);
    }

    public function indexMasaKerjaNyata()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $validator = SDMValidasi::validasiPencarianPenempatanSDM([$reqs->all()]);

        if ($validator->fails()) {
            return $app->redirect->route('sdm.penempatan.riwayat-nyata')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $lingkupIjin = array_filter(explode(',', $pengguna?->sdm_ijin_akses));
        $urutArray = $reqs->urut;
        $kunciUrut = array_filter((array) $urutArray);
        $uruts = $urutArray ? implode(',', $kunciUrut) : null;

        $cari = SDMDBQuery::ambilMasaKerjaNyataSDM($reqs, $reqs->kata_kunci, $lingkupIjin, $uruts);

        if ($reqs->unduh == 'excel') {
            return SDMExcel::eksporExcelMasaKerjaNyataSDM($cari);
        }

        $data = [
            'tabels' => $cari->clone()->paginate($reqs->bph ?: 100)->withQueryString()->appends(['fragment' => 'riwa-penem-sdm_tabels']),
            ...$this->kirimData($lingkupIjin, $uruts, $kunciUrut, $cari)
        ];

        return $this->tampilkanDataPenempatanSDM($data);
    }

    public function indexMasaKerjaNyataAktif()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $validator = SDMValidasi::validasiPencarianPenempatanSDM([$reqs->all()]);

        if ($validator->fails()) {
            return $app->redirect->route('sdm.penempatan.riwayat-nyata-aktif')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $lingkupIjin = array_filter(explode(',', $pengguna?->sdm_ijin_akses));
        $urutArray = $reqs->urut;
        $kunciUrut = array_filter((array) $urutArray);
        $uruts = $urutArray ? implode(',', $kunciUrut) : null;

        $cari = SDMDBQuery::ambilMasaKerjaNyataSDMAktif($reqs, $reqs->kata_kunci, $lingkupIjin, $uruts);

        if ($reqs->unduh == 'excel') {
            return SDMExcel::eksporExcelMasaKerjaNyataSDM($cari);
        }

        $data = [
            'tabels' => $cari->clone()->paginate($reqs->bph ?: 100)->withQueryString()->appends(['fragment' => 'riwa-penem-sdm_tabels']),
            ...$this->kirimData($lingkupIjin, $uruts, $kunciUrut, $cari)
        ];

        return $this->tampilkanDataPenempatanSDM($data);
    }

    public function indexMasaKerjaNyataNonAktif()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $validator = SDMValidasi::validasiPencarianPenempatanSDM([$reqs->all()]);

        if ($validator->fails()) {
            return $app->redirect->route('sdm.penempatan.riwayat-nyata-nonaktif')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $lingkupIjin = array_filter(explode(',', $pengguna?->sdm_ijin_akses));
        $urutArray = $reqs->urut;
        $kunciUrut = array_filter((array) $urutArray);
        $uruts = $urutArray ? implode(',', $kunciUrut) : null;

        $cari = SDMDBQuery::ambilMasaKerjaNyataSDMNonAktif($reqs, $reqs->kata_kunci, $lingkupIjin, $uruts);

        if ($reqs->unduh == 'excel') {
            return SDMExcel::eksporExcelMasaKerjaNyataSDM($cari);
        }

        $data = [
            'tabels' => $cari->clone()->paginate($reqs->bph ?: 100)->withQueryString()->appends(['fragment' => 'riwa-penem-sdm_tabels']),
            ...$this->kirimData($lingkupIjin, $uruts, $kunciUrut, $cari)
        ];

        return $this->tampilkanDataPenempatanSDM($data);
    }

    public function indexAktif()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $validator = SDMValidasi::validasiPencarianPenempatanSDM([$reqs->all()]);

        if ($validator->fails()) {
            return $app->redirect->route('sdm.penempatan.data-aktif')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $lingkupIjin = array_filter(explode(',', $pengguna?->sdm_ijin_akses));
        $urutArray = $reqs->urut;
        $kunciUrut = array_filter((array) $urutArray);
        $uruts = $urutArray ? implode(',', $kunciUrut) : null;

        $cari = SDMDBQuery::ambilPenempatanAktifSDM($reqs, $reqs->kata_kunci, $lingkupIjin, $uruts);

        if ($reqs->unduh == 'excel') {
            return SDMExcel::eksporExcelPenempatanAktifSDM($cari);
        }

        $data = [
            'tabels' => $cari->clone()->paginate($reqs->bph ?: 100)->withQueryString()->appends(['fragment' => 'riwa-penem-sdm_tabels']),
            ...$this->kirimData($lingkupIjin, $uruts, $kunciUrut, $cari)
        ];

        return $this->tampilkanDataPenempatanSDM($data);
    }

    public function indexNonAktif()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $validator = SDMValidasi::validasiPencarianPenempatanSDM([$reqs->all()]);

        if ($validator->fails()) {
            return $app->redirect->route('sdm.penempatan.data-nonaktif')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $lingkupIjin = array_filter(explode(',', $pengguna?->sdm_ijin_akses));
        $urutArray = $reqs->urut;
        $kunciUrut = array_filter((array) $urutArray);
        $uruts = $urutArray ? implode(',', $kunciUrut) : null;

        $cari = SDMDBQuery::ambilPenempatanNonAktifSDM($reqs, $reqs->kata_kunci, $lingkupIjin, $uruts);

        if ($reqs->unduh == 'excel') {
            return SDMExcel::eksporExcelPenempatanNonAktifSDM($cari);
        }

        $data = [
            'tabels' => $cari->clone()->paginate($reqs->bph ?: 100)->withQueryString()->appends(['fragment' => 'riwa-penem-sdm_tabels']),
            ...$this->kirimData($lingkupIjin, $uruts, $kunciUrut, $cari)
        ];

        return $this->tampilkanDataPenempatanSDM($data);
    }

    public function indexAkanHabis()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $validator = SDMValidasi::validasiPencarianPenempatanSDM([$reqs->all()]);

        if ($validator->fails()) {
            return $app->redirect->route('sdm.penempatan.data-akanhabis')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $lingkupIjin = array_filter(explode(',', $pengguna?->sdm_ijin_akses));
        $urutArray = $reqs->urut;
        $kunciUrut = array_filter((array) $urutArray);
        $uruts = $urutArray ? implode(',', $kunciUrut) : null;
        $date = $app->date;


        $cari = SDMDBQuery::ambilPKWTAkanHabisSDM($reqs, $reqs->kata_kunci, $lingkupIjin, $uruts, [$date->today()->toDateString(), $date->today()->addDays(40)->toDateString()]);

        if ($reqs->unduh == 'excel') {
            return SDMExcel::eksporExcelPenempatanSDMAkanHabis($cari);
        }

        $data = [
            'tabels' => $cari->clone()->paginate($reqs->bph ?: 100)->withQueryString()->appends(['fragment' => 'riwa-penem-sdm_tabels']),
            ...$this->kirimData($lingkupIjin, $uruts, $kunciUrut, $cari)
        ];

        return $this->tampilkanDataPenempatanSDM($data);
    }

    public function indexKadaluarsa()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $validator = SDMValidasi::validasiPencarianPenempatanSDM([$reqs->all()]);

        if ($validator->fails()) {
            return $app->redirect->route('sdm.penempatan.data-kadaluarsa')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $lingkupIjin = array_filter(explode(',', $pengguna?->sdm_ijin_akses));
        $urutArray = $reqs->urut;
        $kunciUrut = array_filter((array) $urutArray);
        $uruts = $urutArray ? implode(',', $kunciUrut) : null;

        $cari = SDMDBQuery::ambilPKWTKadaluarsaSDM($reqs, $reqs->kata_kunci, $lingkupIjin, $uruts, $app->date->today()->toDateString());

        if ($reqs->unduh == 'excel') {
            return SDMExcel::eksporExcelPenempatanSDMKadaluarsa($cari);
        }

        $data = [
            'tabels' => $cari->clone()->paginate($reqs->bph ?: 100)->withQueryString()->appends(['fragment' => 'riwa-penem-sdm_tabels']),
            ...$this->kirimData($lingkupIjin, $uruts, $kunciUrut, $cari)
        ];

        return $this->tampilkanDataPenempatanSDM($data);
    }

    public function indexBaru()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $validator = SDMValidasi::validasiPencarianPenempatanSDM([$reqs->all()]);

        if ($validator->fails()) {
            return $app->redirect->route('sdm.penempatan.data-baru')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $lingkupIjin = array_filter(explode(',', $pengguna?->sdm_ijin_akses));
        $urutArray = $reqs->urut;
        $kunciUrut = array_filter((array) $urutArray);
        $uruts = $urutArray ? implode(',', $kunciUrut) : null;

        $cari = SDMDBQuery::ambilSDMAktifBelumDitempatkan($reqs, $reqs->kata_kunci, $lingkupIjin, $uruts);

        if ($reqs->unduh == 'excel') {
            return SDMExcel::eksporExcelSDMAktifBelumDitempatkan($cari);
        }

        $data = [
            'tabels' => $cari->clone()->paginate($reqs->bph ?: 100)->withQueryString()->appends(['fragment' => 'riwa-penem-sdm_tabels']),
            ...$this->kirimData($lingkupIjin, $uruts, $kunciUrut, $cari)
        ];

        return $this->tampilkanDataPenempatanSDM($data);
    }

    public function indexBatal()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $validator = SDMValidasi::validasiPencarianPenempatanSDM([$reqs->all()]);

        if ($validator->fails()) {
            return $app->redirect->route('sdm.penempatan.data-batal')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $lingkupIjin = array_filter(explode(',', $pengguna?->sdm_ijin_akses));
        $urutArray = $reqs->urut;
        $kunciUrut = array_filter((array) $urutArray);
        $uruts = $urutArray ? implode(',', $kunciUrut) : null;

        $cari = SDMDBQuery::ambilSDMBatalBergabung($reqs, $reqs->kata_kunci, $lingkupIjin, $uruts);

        if ($reqs->unduh == 'excel') {
            return SDMExcel::eksporExcelSDMBatalBergabung($cari);
        }

        $data = [
            'tabels' => $cari->clone()->paginate($reqs->bph ?: 100)->withQueryString()->appends(['fragment' => 'riwa-penem-sdm_tabels']),
            ...$this->kirimData($lingkupIjin, $uruts, $kunciUrut, $cari)
        ];

        return $this->tampilkanDataPenempatanSDM($data);
    }

    public function lihat($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkupIjin = array_filter(explode(',', $ijin_akses));

        $penem = SDMDBQuery::ambilDataPenempatanSDM($lingkupIjin, $uuid);

        $lingkup_lokasi = collect($penem?->penempatan_lokasi);
        $lingkup_akses = $lingkup_lokasi->unique()->intersect($lingkupIjin)->count();

        abort_unless($penem && (blank($ijin_akses) || $lingkup_lokasi->isEmpty() || ($lingkup_akses > 0) || ($pengguna->sdm_no_absen == $penem?->sdm_no_absen)), 403, 'Akses pengguna dibatasi.');

        $HtmlPenuh = $app->view->make('sdm.penempatan.lihat', compact('penem'));
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function tambah($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkupIjin = array_filter(explode(',', $ijin_akses));

        $penem = SDMDBQuery::ambilIDTambahPenempatanSDM($lingkupIjin, $uuid);

        $lingkup_lokasi = collect($penem?->penempatan_lokasi);
        $lingkup_akses = $lingkup_lokasi->unique()->intersect($lingkupIjin)->count();

        abort_unless($penem && ($pengguna->sdm_no_absen !== $penem?->sdm_no_absen) && (blank($ijin_akses) || $lingkup_lokasi->isEmpty() || ($lingkup_akses > 0)), 403, 'Akses pengguna dibatasi.');

        if ($reqs->isMethod('post')) {
            $reqs->merge(['penempatan_id_pembuat' => $pengguna->sdm_no_absen]);

            $validasi = SDMValidasi::validasiTambahPenempatanSDM([$reqs->all()]);

            $validasi->validate();

            $valid = $validasi->safe()->all()[0];

            $data = Arr::except($valid, ['penempatan_berkas']);

            SDMDBQuery::tambahDataPenempatanSDM($data);

            $berkas = Arr::only($valid, ['penempatan_berkas'])['penempatan_berkas'] ?? false;


            if ($berkas) {

                $namaBerkas = Arr::only($valid, ['penempatan_no_absen'])['penempatan_no_absen'] . ' - '  . Arr::only($valid, ['penempatan_mulai'])['penempatan_mulai'] . '.pdf';

                SDMBerkas::simpanBerkasPenempatanSDM($berkas, $namaBerkas);
            }

            SDMCache::hapusCacheSDMUmum();

            $pesanSoket = $pengguna?->sdm_nama . ' telah menambah data Penempatan SDM nomor absen '
                . Arr::only($valid, ['penempatan_no_absen'])['penempatan_no_absen'] . ' tanggal penempatan ' . Arr::only($valid, ['penempatan_mulai'])['penempatan_mulai'] . ' pada ' . strtoupper($app->date->now()->translatedFormat('d F Y H:i:s'));

            Websoket::siaranUmum($pesanSoket);

            $perujuk = $reqs->session()->get('tautan_perujuk');
            $pesan = Rangka::statusBerhasil();
            $redirect = $app->redirect;

            return $perujuk
                ? $redirect->to($perujuk)->with('pesan', $pesan)
                : $redirect->route('sdm.penempatan.riwayat')->with('pesan', $pesan);
        }

        $aturs = Cache::ambilCacheAtur();

        $data = [
            'penempatans' => $aturs->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin);
            })->sortBy(['atur_butir', 'asc']),
            'kontraks' => $aturs->where('atur_jenis', 'STATUS KONTRAK')->sortBy(['atur_butir', 'asc']),
            'kategoris' => $aturs->where('atur_jenis', 'KATEGORI')->sortBy(['atur_butir', 'asc']),
            'pangkats' => $aturs->where('atur_jenis', 'PANGKAT')->sortBy(['atur_butir', 'asc']),
            'golongans' => $aturs->where('atur_jenis', 'GOLONGAN')->sortBy(['atur_butir', 'asc']),
            'posisis' => SDMCache::ambilCachePosisiSDM(),
            'penem' => $penem
        ];

        $HtmlPenuh = $app->view->make('sdm.penempatan.tambah-ubah', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function ubah($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkupIjin = array_filter(explode(',', $ijin_akses));

        $penem = SDMDBQuery::ambilIDUbah_HapusPenempatanSDM($lingkupIjin, $uuid);

        $lingkup_lokasi = collect($penem?->penempatan_lokasi);
        $lingkup_akses = $lingkup_lokasi->unique()->intersect($lingkupIjin)->count();

        abort_unless($penem && ($pengguna->sdm_no_absen !== $penem?->sdm_no_absen) && (blank($ijin_akses) || $lingkup_lokasi->isEmpty() || ($lingkup_akses > 0)), 403, 'Akses pengguna dibatasi.');

        if ($reqs->isMethod('post')) {
            $reqs->merge(['penempatan_id_pengubah' => $pengguna->sdm_no_absen]);

            $validasi = SDMValidasi::validasiUbahPenempatanSDM([$reqs->all()], $uuid);

            $validasi->validate();

            $valid = $validasi->safe()->all()[0];

            $data = Arr::except($valid, ['penempatan_berkas']);

            SDMDBQuery::ubahDataPenempatanSDM($data, $uuid);

            $berkas = Arr::only($valid, ['penempatan_berkas'])['penempatan_berkas'] ?? false;

            if ($berkas) {
                $namaBerkas = Arr::only($valid, ['penempatan_no_absen'])['penempatan_no_absen'] . ' - '  . Arr::only($valid, ['penempatan_mulai'])['penempatan_mulai'] . '.pdf';

                SDMBerkas::simpanBerkasPenempatanSDM($berkas, $namaBerkas);
            }

            SDMCache::hapusCacheSDMUmum();

            $pesanSoket = $pengguna?->sdm_nama . ' telah mengubah data Penempatan SDM nomor absen '
                . Arr::only($valid, ['penempatan_no_absen'])['penempatan_no_absen'] . ' tanggal penempatan ' . Arr::only($valid, ['penempatan_mulai'])['penempatan_mulai'] . ' pada ' . strtoupper($app->date->now()->translatedFormat('d F Y H:i:s'));

            Websoket::siaranUmum($pesanSoket);

            $perujuk = $reqs->session()->get('tautan_perujuk');
            $pesan = Rangka::statusBerhasil();
            $redirect = $app->redirect;

            return $perujuk
                ? $redirect->to($perujuk)->with('pesan', $pesan)
                : $redirect->route('sdm.penempatan.riwayat')->with('pesan', $pesan);
        }

        $aturs = Cache::ambilCacheAtur();

        $data = [
            'penempatans' => $aturs->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin);
            })->sortBy(['atur_butir', 'asc']),
            'kontraks' => $aturs->where('atur_jenis', 'STATUS KONTRAK')->sortBy(['atur_butir', 'asc']),
            'kategoris' => $aturs->where('atur_jenis', 'KATEGORI')->sortBy(['atur_butir', 'asc']),
            'pangkats' => $aturs->where('atur_jenis', 'PANGKAT')->sortBy(['atur_butir', 'asc']),
            'golongans' => $aturs->where('atur_jenis', 'GOLONGAN')->sortBy(['atur_butir', 'asc']),
            'posisis' => SDMCache::ambilCachePosisiSDM(),
            'penem' => $penem
        ];

        $HtmlPenuh = $app->view->make('sdm.penempatan.tambah-ubah', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function hapus($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $database = $app->db;

        $ijin_akses = $pengguna->sdm_ijin_akses;
        $lingkupIjin = array_filter(explode(',', $ijin_akses));

        $penem = SDMDBQuery::ambilIDUbah_HapusPenempatanSDM($lingkupIjin, $uuid);

        $lingkup_lokasi = collect($penem?->penempatan_lokasi);
        $lingkup_akses = $lingkup_lokasi->unique()->intersect($lingkupIjin)->count();

        abort_unless($penem && ($pengguna->sdm_no_absen !== $penem?->sdm_no_absen) && (blank($ijin_akses) || $lingkup_lokasi->isEmpty() || ($lingkup_akses > 0)), 403, 'Akses pengguna dibatasi.');

        if ($reqs->isMethod('post')) {
            abort_unless($app->filesystem->exists('contoh/data-dihapus.xlsx'), 404, 'Berkas riwayat penghapusan tidak ditemukan.');

            $reqs->merge(['id_penghapus' => $pengguna->sdm_no_absen, 'waktu_dihapus' => $app->date->now()]);

            $validasi = Validasi::validasiHapusDataDB($reqs->all());

            $validasi->validate();

            $dataValid = $validasi->validated();

            Excel::cadangkanPenghapusanDatabase([
                'Penempatan SDM',
                collect($penem)->toJson(),
                $dataValid['id_penghapus'],
                $dataValid['waktu_dihapus']->format('Y-m-d H:i:s'),
                $dataValid['alasan']
            ]);

            SDMDBQuery::hapusDataPenempatanSDM($uuid);

            $namaBerkas = $penem->sdm_no_absen . ' - ' . $penem->penempatan_mulai . '.pdf';

            SDMBerkas::hapusBerkasPenempatanSDM($namaBerkas);

            SDMCache::hapusCacheSDMUmum();

            $pesanSoket = $pengguna?->sdm_nama . ' telah menghapus data Penempatan SDM nomor absen '
                . $penem->sdm_no_absen . ' tanggal penempatan ' . $penem->penempatan_mulai . ' pada ' . strtoupper($app->date->now()->translatedFormat('d F Y H:i:s'));

            Websoket::siaranUmum($pesanSoket);

            $perujuk = $reqs->session()->get('tautan_perujuk');
            $pesan = 'Data berhasil dihapus';
            $redirect = $app->redirect;

            return $perujuk
                ? $redirect->to($perujuk)->with('pesan', $pesan)
                : $redirect->route('sdm.penempatan.riwayat')->with('pesan', $pesan);
        }

        $HtmlPenuh = $app->view->make('sdm.penempatan.hapus', compact('penem'));
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    protected function kirimData($lingkupIjin, $uruts, $kunciUrut, $cari)
    {
        $aturs = Cache::ambilCacheAtur();
        $str = str();

        return [
            'penempatans' => $aturs->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin);
            })->sortBy(['atur_butir', 'asc']),
            'kontraks' => $aturs->where('atur_jenis', 'STATUS KONTRAK')->sortBy(['atur_butir', 'asc']),
            'kategoris' => $aturs->where('atur_jenis', 'KATEGORI')->sortBy(['atur_butir', 'asc']),
            'pangkats' => $aturs->where('atur_jenis', 'PANGKAT')->sortBy(['atur_butir', 'asc']),
            'kelamins' => $aturs->where('atur_jenis', 'KELAMIN')->sortBy(['atur_butir', 'asc']),
            'agamas' => $aturs->where('atur_jenis', 'AGAMA')->sortBy(['atur_butir', 'asc']),
            'kawins' => $aturs->where('atur_jenis', 'STATUS MENIKAH')->sortBy(['atur_butir', 'asc']),
            'pendidikans' => $aturs->where('atur_jenis', 'PENDIDIKAN')->sortBy(['atur_butir', 'asc']),
            'warganegaras' => $aturs->where('atur_jenis', 'NEGARA')->sortBy(['atur_butir', 'asc']),
            'disabilitases' => $aturs->where('atur_jenis', 'DISABILITAS')->sortBy(['atur_butir', 'asc']),
            'posisis' => SDMCache::ambilCachePosisiSDM(),
            'urutAbsen' => $str->contains($uruts, 'sdm_no_absen'),
            'indexAbsen' => (head(array_keys($kunciUrut, 'sdm_no_absen ASC')) + head(array_keys($kunciUrut, 'sdm_no_absen DESC')) + 1),
            'urutMasuk' => $str->contains($uruts, 'sdm_tgl_gabung'),
            'indexMasuk' => (head(array_keys($kunciUrut, 'sdm_tgl_gabung ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_gabung DESC')) + 1),
            'urutLahir' => $str->contains($uruts, 'sdm_tgl_lahir'),
            'indexLahir' => (head(array_keys($kunciUrut, 'sdm_tgl_lahir ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_lahir DESC')) + 1),
            'urutKeluar' => $str->contains($uruts, 'sdm_tgl_berhenti'),
            'indexKeluar' => (head(array_keys($kunciUrut, 'sdm_tgl_berhenti ASC')) + head(array_keys($kunciUrut, 'sdm_tgl_berhenti DESC')) + 1),
            'jumlahOS' => $cari->clone()->whereNotNull('penempatan_kontrak')->where('penempatan_kontrak', 'like', 'OS-%')->count(),
            'jumlahOrganik' => $cari->clone()->whereNotNull('penempatan_kontrak')->where('penempatan_kontrak', 'not like', 'OS-%')->count(),
        ];
    }

    protected function tampilkanDataPenempatanSDM($data, $uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka());

        if (!isset($uuid)) {
            $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);
        }

        $HtmlPenuh = $app->view->make('sdm.penempatan.riwayat', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');

        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
            ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function statistikPenempatanSDM()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        // $rumusMasaKerja = '=IF([@sdm_tgl_berhenti]="",DATEDIF([@sdm_tgl_gabung],TODAY(),"Y"),DATEDIF([@sdm_tgl_gabung],[@sdm_tgl_berhenti],"Y"))';
        // $rumusUsia = '=IF([@sdm_tgl_berhenti]="",DATEDIF([@sdm_tgl_lahir],TODAY(),"Y"),DATEDIF([@sdm_tgl_lahir],[@sdm_tgl_berhenti],"Y"))';

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $cari = SDMDBQuery::ambilStatistikPenempatanSDM($lingkupIjin);

        return SDMExcel::eksporStatistikSDM($cari);
    }

    public function contohUnggahPenempatanSDM()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        abort_unless($app->filesystem->exists("contoh/unggah-umum.xlsx"), 404, 'Berkas Contoh Ekspor Tidak Ditemukan.');

        $cari = SDMDBQuery::contohImporPenempatanSDM(array_filter(explode(',', $pengguna->sdm_ijin_akses)));

        return SDMExcel::eksporExcelContohUnggahPenempatanSDM($cari);
    }

    public function unggahPenempatanSDM()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        if ($reqs->isMethod('post')) {
            $validasifile = SDMValidasi::validasiBerkasImporPenempatanSDM($reqs->all());

            $validasifile->validate();

            $file = $validasifile->safe()->only('unggah_penempatan_sdm')['unggah_penempatan_sdm'];
            $namafile = 'unggahpenempatansdm-' . date('YmdHis') . '.xlsx';

            Berkas::simpanBerkasImporExcelSementara($file, $namafile);

            $fileexcel = Berkas::ambilBerkasImporExcelSementara($namafile);

            return SDMExcel::imporExcelPenempatanSDM($fileexcel);
        }

        $HtmlPenuh = $app->view->make('sdm.penempatan.unggah');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }
}
