<?php

namespace App\Http\Controllers\SDM;

use App\Interaksi\Cache;
use App\Interaksi\Rangka;
use App\Interaksi\Websoket;
use Illuminate\Support\Arr;
use App\Tambahan\FungsiStatis;
use App\Interaksi\SDM\SDMCache;
use Illuminate\Validation\Rule;
use App\Interaksi\SDM\SDMBerkas;
use App\Interaksi\SDM\SDMDBQuery;
use App\Interaksi\SDM\SDMValidasi;
use App\Http\Controllers\SDM\Berkas;

class Sanksi
{
    public function index(Rule $rule, FungsiStatis $fungsiStatis, Berkas $berkas, $uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']) || ($pengguna?->sdm_uuid == $uuid && $pengguna?->sdm_uuid !== null), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $validator = $app->validator->make(
            $reqs->all(),
            [
                'kata_kunci' => ['sometimes', 'nullable', 'string'],
                'tgl_sanksi_mulai' => ['sometimes', 'nullable', 'date'],
                'tgl_sanksi_sampai' => ['sometimes', 'nullable', 'required_with:tgl_sanksi_mulai', 'date', 'after:tgl_sanksi_mulai'],
                'sanksi_jenis.*' => ['required', 'string', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
                    return $query->where('atur_jenis', 'SANKSI SDM');
                })],
                'sanksi_penempatan.*' => ['sometimes', 'nullable', 'string'],
                'status_sdm.*' => ['sometimes', 'nullable', 'string'],
                'status_sanksi' => ['sometimes', 'nullable', 'string', $rule->in(['AKTIF', 'BERAKHIR'])],
                'unduh' => ['sometimes', 'nullable', 'string', $rule->in(['excel'])],
                'bph' => ['sometimes', 'nullable', 'numeric', $rule->in([25, 50, 75, 100])],
                'urut.*' => ['sometimes', 'nullable', 'string']
            ],
            [
                'kata_kunci.*' => 'Kata Kunci Pencarian harus berupa karakter.',
                'tgl_sanksi_mulai.*' => 'Tanggal Mulai Terbit Sanksi wajib berupa tanggal valid.',
                'tgl_sanksi_sampai.*' => 'Tanggal Akhir Terbit Sanksi wajib berupa tanggal valid dan lebih lama dari Tanggal Mulai.',
                'sanksi_jenis.*' => 'Jenis Sanksi wajib berupa karakter dan terdaftar.',
                'sanksi_penempatan.*' => 'Lokasi #:position wajib berupa karakter.',
                'status_sdm.*' => 'Status #:position wajib berupa karakter.',
                'status_sanksi.*' => 'Status Sanksi wajib berupa karakter dan terdaftar.',
                'unduh.*' => 'Format ekspor tidak dikenali.',
                'bph.*' => 'Baris Per halaman tidak sesuai daftar.',
                'urut.*.string' => 'Butir Pengaturan urutan #:position wajib berupa karakter.',
            ]
        );

        if ($validator->fails()) {
            return $app->redirect->route('sdm.sanksi.data')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $lingkupIjin = array_filter(explode(',', $pengguna?->sdm_ijin_akses));

        $cacheAtur = $fungsiStatis->ambilCacheAtur();

        $lokasis = $cacheAtur->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
            return $query->whereIn('atur_butir', $lingkupIjin)->sortBy(['atur_butir', 'asc']);
        });

        $urutArray = $reqs->urut;

        $uruts = $urutArray ? implode(',', array_filter($urutArray)) : null;

        $kataKunci = $reqs->kata_kunci;

        $hariIni = $app->date->today()->format('Y-m-d');

        $database = $app->db;

        $kontrak = $this->dataKontrak($database);

        $cari = $this->dataDasar($database)
            ->addSelect('a.sdm_uuid as langgar_tsdm_uuid', 'a.sdm_nama as langgar_tsdm_nama', 'a.sdm_tgl_berhenti as langgar_tsdm_tgl_berhenti', 'kontrak_t.penempatan_lokasi as langgar_tlokasi', 'kontrak_t.penempatan_posisi as langgar_tposisi', 'kontrak_t.penempatan_kontrak as langgar_tkontrak', 'langgar_isi', 'langgar_tanggal', 'langgar_status', 'langgar_pelapor', 'b.sdm_uuid as langgar_psdm_uuid', 'b.sdm_nama as langgar_psdm_nama', 'b.sdm_tgl_berhenti as langgar_psdm_tgl_berhenti', 'kontrak_p.penempatan_lokasi as langgar_plokasi', 'kontrak_p.penempatan_posisi as langgar_pposisi', 'kontrak_p.penempatan_kontrak as langgar_pkontrak')
            ->join('sdms as a', 'sanksi_no_absen', '=', 'a.sdm_no_absen')
            ->leftJoinSub($kontrak, 'kontrak_t', function ($join) {
                $join->on('sanksi_no_absen', '=', 'kontrak_t.penempatan_no_absen');
            })
            ->leftJoin('pelanggaransdms', 'sanksi_lap_no', '=', 'langgar_lap_no')
            ->leftJoin('sdms as b', 'langgar_pelapor', '=', 'b.sdm_no_absen')
            ->leftJoinSub($kontrak, 'kontrak_p', function ($join) {
                $join->on('langgar_pelapor', '=', 'kontrak_p.penempatan_no_absen');
            })
            ->when($reqs->sanksi_jenis, function ($query) use ($reqs) {
                $query->whereIn('sanksi_jenis', (array) $reqs->sanksi_jenis);
            })
            ->when($reqs->sanksi_status == 'AKTIF', function ($query) use ($hariIni) {
                $query->where('sanksi_selesai', '>=', $hariIni);
            })
            ->when($reqs->sanksi_status == 'BERAKHIR', function ($query) use ($hariIni) {
                $query->where('sanksi_selesai', '<', $hariIni);
            })
            ->when($reqs->sanksi_penempatan, function ($query) use ($reqs) {
                $query->where(function ($group) use ($reqs) {
                    $group->whereIn('kontrak_t.penempatan_lokasi', (array) $reqs->sanksi_penempatan)
                        ->orWhereIn('kontrak_p.penempatan_lokasi', (array) $reqs->sanksi_penempatan);
                });
            })
            ->when($reqs->status_sdm, function ($query) use ($reqs) {
                $query->where(function ($group) use ($reqs) {
                    $group->whereIn('kontrak_t.penempatan_kontrak', (array) $reqs->status_sdm);
                });
            })
            ->when($kataKunci, function ($query, $kataKunci) {
                $query->where(function ($group) use ($kataKunci) {
                    $group->where('sanksi_no_absen', 'like', '%' . $kataKunci . '%')
                        ->orWhere('a.sdm_nama', 'like', '%' . $kataKunci . '%')
                        ->orWhere('b.sdm_nama', 'like', '%' . $kataKunci . '%')
                        ->orWhere('sanksi_tambahan', 'like', '%' . $kataKunci . '%')
                        ->orWhere('sanksi_keterangan', 'like', '%' . $kataKunci . '%');
                });
            })
            ->when($reqs->tgl_sanksi_mulai && $reqs->tgl_sanksi_sampai, function ($query) use ($reqs) {
                $query->whereBetween('sanksi_mulai', [$reqs->tgl_sanksi_mulai, $reqs->tgl_sanksi_sampai]);
            })
            ->when($uuid, function ($query) use ($uuid) {
                $query->where('a.sdm_uuid', $uuid);
            })
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->whereIn('kontrak_t.penempatan_lokasi', $lingkupIjin)
                        ->orWhereIn('kontrak_p.penempatan_lokasi', $lingkupIjin);
                });
            })
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->latest('sanksi_mulai');
                }
            );

        if ($reqs->unduh == 'excel') {
            return $berkas->unduhIndexSanksiSDM($cari, $app);
        }

        $jumlahOS = $cari->clone()->whereNotNull('kontrak_t.penempatan_kontrak')->where('kontrak_t.penempatan_kontrak', 'like', 'OS-%')->count();
        $jumlahOrganik = $cari->clone()->whereNotNull('kontrak_t.penempatan_kontrak')->where('kontrak_t.penempatan_kontrak', 'not like', 'OS-%')->count();

        $tabels = $cari->clone()->paginate($reqs->bph ?: 25)->withQueryString()->appends(['fragment' => 'sanksi-sdm_tabels', 'uuid' => $uuid ?? '']);

        $kunciUrut = array_filter((array) $urutArray);

        $urutTanggalMulai = $str->contains($uruts, 'sanksi_mulai');
        $indexTanggalMulai = (head(array_keys($kunciUrut, 'sanksi_mulai ASC')) + head(array_keys($kunciUrut, 'sanksi_mulai DESC')) + 1);
        $urutTanggalSelesai = $str->contains($uruts, 'sanksi_selesai');
        $indexTanggalSelesai = (head(array_keys($kunciUrut, 'sanksi_selesai ASC')) + head(array_keys($kunciUrut, 'sanksi_selesai DESC')) + 1);

        $data = [
            'tabels' => $tabels,
            'lokasis' => $lokasis,
            'statusSDMs' => $cacheAtur->where('atur_jenis', 'STATUS KONTRAK')->sortBy(['atur_jenis', 'asc'], ['atur_butir', 'asc']),
            'jenisSanksis' => $cacheAtur->where('atur_jenis', 'SANKSI SDM')->sortBy(['atur_jenis', 'asc'], ['atur_butir', 'desc']),
            'urutTanggalMulai' => $urutTanggalMulai,
            'indexTanggalMulai' => $indexTanggalMulai,
            'urutTanggalSelesai' => $urutTanggalSelesai,
            'indexTanggalSelesai' => $indexTanggalSelesai,
            'halamanAkun' => $uuid ?? '',
            'jumlahOS' => $jumlahOS,
            'jumlahOrganik' => $jumlahOrganik
        ];

        if (!isset($uuid)) {
            $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);
        }

        $HtmlPenuh = $app->view->make('sdm.sanksi.data', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
            ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function atributInput()
    {
        return [
            'sanksi_lap_no' => 'Nomor Laporan Pelanggaran',
            'sanksi_no_absen' => 'ID Absen Penerima Sanksi',
            'sanksi_jenis' => 'Jenis Sanksi',
            'sanksi_mulai' => 'Tanggal Sanksi Mulai Berlaku',
            'sanksi_selesai' => 'Tanggal Sanksi Berakhir',
            'sanksi_keterangan' => 'Keterangan',
            'sanksi_tambahan' => 'Sanksi Tambahan',
            'sanksi_berkas' => 'Berkas Unggah Sanksi',
            'sanksi_id_pengunggah' => 'ID Pengunggah',
            'sanksi_id_pembuat' => 'ID Pembuat',
            'sanksi_id_pengubah' => 'ID Pengubah',
        ];
    }

    public function dataDasar($database)
    {
        return $database->query()->select('sanksi_uuid', 'sanksi_no_absen', 'sanksi_jenis', 'sanksi_mulai', 'sanksi_selesai', 'sanksi_lap_no', 'sanksi_tambahan', 'sanksi_keterangan')->from('sanksisdms');
    }

    public function dataKontrak($database)
    {
        return $database->query()->select('penempatan_uuid', 'penempatan_no_absen', 'penempatan_posisi', 'penempatan_lokasi', 'penempatan_kontrak', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_keterangan')
            ->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
                $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
            });
    }

    public function dataPelanggaran($database)
    {
        return $database->query()->select('langgar_uuid', 'langgar_lap_no', 'langgar_no_absen', 'langgar_pelapor', 'langgar_tanggal', 'langgar_status', 'langgar_isi', 'langgar_keterangan')->from('pelanggaransdms');
    }

    public function dasarValidasi()
    {
        return [
            'sanksi_jenis' => ['required', 'string', Rule::exists('aturs', 'atur_butir')->where(function ($query) {
                return $query->where('atur_jenis', 'SANKSI SDM');
            })],
            'sanksi_mulai' => ['required', 'date'],
            'sanksi_selesai' => ['required', 'date', 'after:sanksi_mulai'],
            'sanksi_tambahan' => ['nullable', 'string'],
            'sanksi_keterangan' => ['nullable', 'string'],
            'sanksi_berkas' => ['sometimes', 'file', 'mimetypes:application/pdf'],
        ];
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

        $database = $app->db;

        $laporan = SDMDBQuery::ambilDataPelanggaranSDM($lap_uuid, array_filter(explode(',', $pengguna->sdm_ijin_akses)));

        abort_unless($laporan, 404, 'Data Laporan Pelanggaran tidak ditemukan.');

        if ($reqs->isMethod('post')) {

            $reqs->merge(
                [
                    'sanksi_id_pembuat' => $pengguna->sdm_no_absen,
                    'sanksi_lap_no' => $laporan->langgar_lap_no,
                    'sanksi_no_absen' => $laporan->langgar_no_absen
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

            $data =  Arr::except($valid, ['sanksi_berkas']);

            $database->table('sanksisdms')->insert($data);

            $berkas = Arr::only($valid, ['sanksi_berkas'])['sanksi_berkas'] ?? false;

            if ($berkas) {
                $namaBerkas = Arr::only($valid, ['sanksi_no_absen'])['sanksi_no_absen'] . ' - '  . Arr::only($valid, ['sanksi_jenis'])['sanksi_jenis'] . ' - ' . Arr::only($valid, ['sanksi_mulai'])['sanksi_mulai'] . '.pdf';

                SDMBerkas::simpanBerkasSanksiSDM($berkas, $namaBerkas);
            }

            SDMCache::hapusCachePelanggaranSDM();
            SDMCache::hapusCacheSanksiSDM();
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
                $namaBerkas = $sanksiLama->sanksi_no_absen . ' - '  . Arr::only($valid, ['sanksi_jenis'])['sanksi_jenis'] . ' - ' . Arr::only($valid, ['sanksi_mulai'])['sanksi_mulai'] . '.pdf';

                SDMBerkas::simpanBerkasSanksiSDM($berkas, $namaBerkas);
            }

            SDMCache::hapusCachePelanggaranSDM();
            SDMCache::hapusCacheSanksiSDM();

            $pesanSoket = $pengguna?->sdm_nama . ' telah mengubah data Sanksi SDM nomor absen '
                . $sanksiLama->sanksi_no_absen . ' tanggal ' . Arr::only($valid, ['sanksi_mulai'])['sanksi_mulai']
                . ' pada ' . strtoupper($app->date->now()->translatedFormat('d F Y H:i:s'));

            Websoket::siaranUmum($pesanSoket);

            $pesan = Rangka::statusBerhasil();

            return $perujuk
                ? $redirect->to($perujuk)->with('pesan', $pesan)
                : $redirect->route('sdm.pelanggaran.data')->with('pesan', $pesan);
        }

        $data = [
            'sanksiLama' => $sanksiLama,
            'sanksis' => Cache::ambilCacheAtur()->where('atur_jenis', 'SANKSI SDM')->sortBy(['atur_jenis', 'asc'], ['atur_butir', 'desc']),
        ];

        $HtmlPenuh = $app->view->make('sdm.sanksi.tambah-ubah', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }
}
