<?php

namespace App\Http\Controllers\SDM;

use Illuminate\Support\Arr;
use App\Tambahan\FungsiStatis;
use Illuminate\Validation\Rule;
use App\Http\Controllers\SDM\Berkas;

class PermintaanTambahSDM
{
    public function index(Rule $rule, FungsiStatis $fungsiStatis, Berkas $berkas)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $validator = $app->validator->make(
            $reqs->all(),
            [
                'tgl_diusulkan_mulai' => ['sometimes', 'nullable', 'date'],
                'tgl_diusulkan_sampai' => ['sometimes', 'nullable', 'required_with:tgl_diusulkan_mulai', 'date', 'after:tgl_diusulkan_mulai'],
                'tambahsdm_status.*' => ['sometimes', 'nullable', 'string', $rule->in(['DIUSULKAN', 'DISETUJUI', 'DITOLAK', 'DITUNDA', 'DIBATALKAN'])],
                'tambahsdm_laju' => ['sometimes', 'nullable', 'string', $rule->in(['BELUM TERPENUHI', 'SUDAH TERPENUHI', 'KELEBIHAN'])],
                'tambahsdm_penempatan.*' => ['sometimes', 'nullable', 'string'],
                'posisi.*' => ['sometimes', 'nullable', 'string'],
                'kata_kunci' => ['sometimes', 'nullable', 'string'],
                'bph' => ['sometimes', 'nullable', 'numeric', $rule->in([25, 50, 75, 100])],
                'urut.*' => ['sometimes', 'nullable', 'string']
            ],
            [
                'tgl_diusulkan_mulai.*' => 'Tanggal Mulai Diusulkan wajib berupa tanggal valid.',
                'tgl_diusulkan_sampai.*' => 'Tanggal Akhir Diusulkan wajib berupa tanggal valid dan lebih lama dari Tanggal Mulai.',
                'tambahsdm_status.*.string' => 'Status Permohonan #:position wajib berupa karakter.',
                'tambahsdm_status.*.in' => 'Status Permohonan #:position tidak sesuai daftar.',
                'tambahsdm_penempatan.*' => 'Lokasi #:position wajib berupa karakter.',
                'tambahsdm_laju.*' => 'Status Terpenuhi tidak sesuai daftar.',
                'posisi.*' => 'Jabatan harus berupa karakter.',
                'kata_kunci.*' => 'Kata Kunci Pencarian harus berupa karakter.',
                'bph.*' => 'Baris Per halaman tidak sesuai daftar.',
                'urut.*.string' => 'Butir Pengaturan urutan #:position wajib berupa karakter.'
            ]
        );

        if ($validator->fails()) {
            return $app->redirect->route('sdm.permintaan-tambah-sdm.data')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $cacheAtur = $fungsiStatis->ambilCacheAtur();

        $statuses = $cacheAtur->where('atur_jenis', 'STATUS PERMOHONAN')->sortBy(['atur_butir', 'asc']);
        $lokasis = $cacheAtur->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
            return $query->whereIn('atur_butir', $lingkupIjin)->sortBy(['atur_butir', 'asc']);
        });

        $urutArray = $reqs->urut;

        $uruts = $urutArray ? implode(',', array_filter($urutArray)) : null;

        $database = $app->db;

        $tabelSub = $this->dataDasar()->clone()->addSelect('tambahsdm_uuid', 'b.sdm_uuid', 'b.sdm_nama', $database->raw('COUNT(a.sdm_no_permintaan) as tambahsdm_terpenuhi, MAX(a.sdm_tgl_gabung) as pemenuhan_terkini'))
            ->leftJoin('sdms as a', 'tambahsdm_no', '=', 'a.sdm_no_permintaan')
            ->join('sdms as b', 'tambahsdm_sdm_id', '=', 'b.sdm_no_absen')
            ->groupBy('tambahsdm_no');

        $kataKunci = $reqs->kata_kunci;

        $cari = $database->query()->addSelect('tsdm.*')->fromSub($tabelSub, 'tsdm')
            ->when($reqs->tambahsdm_status, function ($query) use ($reqs) {
                $query->whereIn('tambahsdm_status', (array) $reqs->tambahsdm_status);
            })
            ->when($reqs->tambahsdm_penempatan, function ($query) use ($reqs) {
                $query->whereIn('tambahsdm_penempatan', (array) $reqs->tambahsdm_penempatan);
            })
            ->when($kataKunci, function ($query) use ($kataKunci) {
                $query->where(function ($group) use ($kataKunci) {
                    $group->where('tambahsdm_no', 'like', '%' . $kataKunci . '%')
                        ->orWhere('tambahsdm_posisi', 'like', '%' . $kataKunci . '%')
                        ->orWhere('tambahsdm_sdm_id', 'like', '%' . $kataKunci . '%')
                        ->orWhere('sdm_nama', 'like', '%' . $kataKunci . '%');
                });
            })
            ->when($reqs->tgl_diusulkan_mulai && $reqs->tgl_diusulkan_sampai, function ($query) use ($reqs) {
                $query->whereBetween('tambahsdm_tgl_diusulkan', [$reqs->tgl_diusulkan_mulai, $reqs->tgl_diusulkan_sampai]);
            })
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->whereIn('tambahsdm_penempatan', $lingkupIjin);
            })
            ->when($reqs->posisi, function ($query) use ($reqs) {
                $query->whereIn('tambahsdm_posisi', (array) $reqs->posisi);
            })
            ->when($reqs->tambahsdm_laju == 'BELUM TERPENUHI', function ($query) {
                $query->whereColumn('tambahsdm_jumlah', '>', 'tambahsdm_terpenuhi');
            })
            ->when($reqs->tambahsdm_laju == 'SUDAH TERPENUHI', function ($query) {
                $query->whereColumn('tambahsdm_jumlah', 'tambahsdm_terpenuhi');
            })
            ->when($reqs->tambahsdm_laju == 'KELEBIHAN', function ($query) {
                $query->whereColumn('tambahsdm_jumlah', '<', 'tambahsdm_terpenuhi');
            })
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->orderBy('tambahsdm_no', 'desc');
                }
            );

        if ($reqs->unduh == 'excel') {
            return $berkas->unduhIndexPermintaanTambahSDMExcel($cari, $reqs, $app);
        }

        $kebutuhan = $cari->clone()->sum('tambahsdm_jumlah');
        $terpenuhi = $cari->clone()->sum('tambahsdm_terpenuhi');
        $selisih = $terpenuhi - $kebutuhan;

        $tabels = $cari->clone()->paginate($reqs->bph ?: 25)->withQueryString()->appends(['fragment' => 'tambah_sdm_tabels']);

        $kunciUrut = array_filter((array) $urutArray);

        $urutPenempatan = $str->contains($uruts, 'tambahsdm_penempatan');
        $indexPenempatan = (head(array_keys($kunciUrut, 'tambahsdm_penempatan ASC')) + head(array_keys($kunciUrut, 'tambahsdm_penempatan DESC')) + 1);
        $urutPosisi = $str->contains($uruts, 'tambahsdm_posisi');
        $indexPosisi = (head(array_keys($kunciUrut, 'tambahsdm_posisi ASC')) + head(array_keys($kunciUrut, 'tambahsdm_posisi DESC')) + 1);
        $urutJumlah = $str->contains($uruts, 'tambahsdm_jumlah');
        $indexJumlah = (head(array_keys($kunciUrut, 'tambahsdm_jumlah ASC')) + head(array_keys($kunciUrut, 'tambahsdm_jumlah DESC')) + 1);
        $urutNomor = $str->contains($uruts, 'tambahsdm_no');
        $indexNomor = (head(array_keys($kunciUrut, 'tambahsdm_no ASC')) + head(array_keys($kunciUrut, 'tambahsdm_no DESC')) + 1);

        $posisis = $fungsiStatis->ambilCachePosisiSDM();

        $data = [
            'tabels' => $tabels,
            'statuses' => $statuses,
            'lokasis' => $lokasis,
            'urutPenempatan' => $urutPenempatan,
            'indexPenempatan' => $indexPenempatan,
            'urutPosisi' => $urutPosisi,
            'indexPosisi' => $indexPosisi,
            'urutJumlah' => $urutJumlah,
            'indexJumlah' => $indexJumlah,
            'urutNomor' => $urutNomor,
            'indexNomor' => $indexNomor,
            'posisis' => $posisis,
            'kebutuhan' => $kebutuhan,
            'terpenuhi' => $terpenuhi,
            'selisih' => $selisih,
        ];

        $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);

        $HtmlPenuh = $app->view->make('sdm.permintaan-tambah-sdm.data', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
            ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function atributInput()
    {
        return [
            'tambahsdm_penempatan' => 'Penempatan',
            'tambahsdm_posisi' => 'Jabatan',
            'tambahsdm_jumlah' => 'Jumlah Dibutuhkan',
            'tambahsdm_tgl_diusulkan' => 'Tanggal Diusulkan',
            'tambahsdm_tgl_dibutuhkan' => 'Tanggal Dibutuhkan',
            'tambahsdm_sdm_id' => 'Pemohon',
            'tambahsdm_alasan' => 'Alasan Permohonan',
            'tambahsdm_keterangan' => 'Keterangan Permohonan',
            'tambahsdm_status' => 'Status Permohonan',
            'tambahsdm_berkas' => 'Berkas Permohonan',
            'tambahsdm_id_pengunggah' => 'ID Pengunggah',
            'tambahsdm_id_pembuat' => 'ID Pembuat',
            'tambahsdm_id_pengubah' => 'ID Pengubah',
        ];
    }

    public function dataDasar()
    {
        return app('db')->query()->select('tambahsdm_no', 'tambahsdm_penempatan', 'tambahsdm_posisi', 'tambahsdm_jumlah', 'tambahsdm_tgl_diusulkan', 'tambahsdm_tgl_dibutuhkan', 'tambahsdm_alasan', 'tambahsdm_keterangan', 'tambahsdm_status', 'tambahsdm_sdm_id')->from('tambahsdms');
    }

    public function dasarValidasi()
    {
        return [
            'tambahsdm_penempatan' => ['required', 'string', 'max:20'],
            'tambahsdm_posisi' => ['required', 'string', 'max:40'],
            'tambahsdm_jumlah' => ['required', 'numeric', 'min:1'],
            'tambahsdm_tgl_diusulkan' => ['required', 'date'],
            'tambahsdm_tgl_dibutuhkan' => ['required', 'date', 'after:tambahsdm_tgl_diusulkan'],
            'tambahsdm_sdm_id' => ['required', 'string'],
            'tambahsdm_alasan' => ['required', 'string'],
            'tambahsdm_keterangan' => ['nullable', 'string'],
            'tambahsdm_status' => ['sometimes', 'nullable', 'string', Rule::in(['DIUSULKAN', 'DISETUJUI', 'DITOLAK', 'DITUNDA', 'DIBATALKAN'])],
            'tambahsdm_berkas' => ['sometimes', 'file', 'mimetypes:application/pdf'],
        ];
    }

    public function lihat($uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $permin = $this->dataDasar()->clone()->addSelect('tambahsdm_uuid', 'b.sdm_nama', $app->db->raw('COUNT(a.sdm_no_permintaan) as tambahsdm_terpenuhi'))
            ->leftJoin('sdms as a', 'tambahsdm_no', '=', 'a.sdm_no_permintaan')
            ->join('sdms as b', 'tambahsdm_sdm_id', '=', 'b.sdm_no_absen')
            ->groupBy('tambahsdm_no')->where('tambahsdm_uuid', $uuid)->when($lingkupIjin, function ($query, $lingkupIjin) {
                $query->whereIn('tambahsdm_penempatan', $lingkupIjin);
            })->first();

        abort_unless($permin, 404, 'Data Permintaan Tambah SDM tidak ditemukan.');

        $HtmlPenuh = $app->view->make('sdm.permintaan-tambah-sdm.lihat', compact('permin'));
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }

    public function tambah(FungsiStatis $fungsiStatis)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();

        abort_unless($pengguna && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS']), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        if ($reqs->isMethod('post')) {

            $hitungPermintaan = $this->dataDasar()->whereYear('tambahsdm_dibuat', date('Y'))->whereMonth('tambahsdm_dibuat', date('m'))->count();

            $urutanPermintaan = $hitungPermintaan + 1;

            $nomorPermintaan = date('Y') . date('m') . str($urutanPermintaan)->padLeft(4, '0');

            $reqs->merge(['tambahsdm_id_pembuat' => $pengguna->sdm_no_absen, 'tambahsdm_no' => $nomorPermintaan]);

            $validasi = $app->validator->make(
                $reqs->all(),
                [
                    'tambahsdm_no' => ['required', 'string', 'max:20', 'unique:tambahsdms,tambahsdm_no'],
                    'tambahsdm_id_pembuat' => ['sometimes', 'nullable', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                    ...$this->dasarValidasi()
                ],
                [],
                $this->atributInput()
            );

            $validasi->validate();

            $data = $validasi->safe()->except('tambahsdm_berkas');

            $app->db->table('tambahsdms')->insert($data);

            $berkas = $validasi->safe()->only('tambahsdm_berkas')['tambahsdm_berkas'] ?? false;

            if ($berkas) {
                $berkas->storeAs('sdm/permintaan-tambah-sdm/berkas', $nomorPermintaan . '.pdf');
            }

            $fungsiStatis->hapusCacheSDMUmum();
            $redirect = $app->redirect;
            $perujuk = $reqs->session()->get('tautan_perujuk');
            $pesan = $fungsiStatis->statusBerhasil();

            return $perujuk ? $redirect->to($perujuk)->with('pesan', $pesan) : $redirect->route('sdm.permintaan-tambah-sdm.data')->with('pesan', $pesan);
        }

        $aturs = $fungsiStatis->ambilCacheAtur();
        $sdms = $fungsiStatis->ambilCacheSDM();
        $posisis = $fungsiStatis->ambilCachePosisiSDM();
        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $data = [
            'penempatans' => $aturs->where('atur_jenis', 'PENEMPATAN')->sortBy(['atur_butir', 'asc'])->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin);
            }),
            'posisis' => $posisis,
            'statuses' => $aturs->where('atur_jenis', 'STATUS PERMOHONAN')->sortBy(['atur_butir', 'asc']),
            'sdms' => $sdms->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('penempatan_lokasi', $lingkupIjin);
            }),
        ];

        $HtmlPenuh = $app->view->make('sdm.permintaan-tambah-sdm.tambah-ubah', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }

    public function ubah(FungsiStatis $fungsiStatis, $uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS']), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $permin = $this->dataDasar()->clone()->addSelect('tambahsdm_uuid')->where('tambahsdm_uuid', $uuid)->when($lingkupIjin, function ($query, $lingkupIjin) {
            $query->whereIn('tambahsdm_penempatan', $lingkupIjin);
        })->first();

        abort_unless($permin, 404, 'Data Permintaan Tambah SDM tidak ditemukan.');

        if ($reqs->isMethod('post')) {

            $reqs->merge(['tambahsdm_id_pengubah' => $pengguna->sdm_no_absen]);

            $aturan = [
                'tambahsdm_no' => ['required', 'string', 'max:40', Rule::unique('tambahsdms')->where(fn ($query) => $query->whereNot('tambahsdm_uuid', $uuid))],
                'tambahsdm_id_pengubah' => ['sometimes', 'nullable', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                ...$this->dasarValidasi()
            ];

            if ($reqs->header('X-Minta-Javascript', false)) {
                $aturan = Arr::only($aturan, ['tambahsdm_status', 'tambahsdm_id_pengubah']);
            }

            $validasi = $app->validator->make(
                $reqs->all(),
                $aturan,
                [],
                $this->atributInput()
            );

            $validasi->validate();

            $data = $validasi->safe()->except('tambahsdm_berkas');

            $app->db->table('tambahsdms')->where('tambahsdm_uuid', $uuid)->update($data);

            $fungsiStatis->hapusCacheSDMUmum();
            $pesan = $fungsiStatis->statusBerhasil();
            $session = $reqs->session();

            if ($reqs->header('X-Minta-Javascript', false)) {
                $session->now('pesan', $pesan);
                return view('pemberitahuan');
            }

            $nomorPermintaan = $validasi->safe()->only('tambahsdm_no')['tambahsdm_no'];

            $berkas = $validasi->safe()->only('tambahsdm_berkas')['tambahsdm_berkas'] ?? false;

            if ($berkas) {
                $berkas->storeAs('sdm/permintaan-tambah-sdm/berkas', $nomorPermintaan . '.pdf');
            }

            $perujuk = $session->get('tautan_perujuk');
            $redirect = $app->redirect;

            return $perujuk ? $redirect->to($perujuk)->with('pesan', $pesan) : $redirect->route('sdm.permintaan-tambah-sdm.data')->with('pesan', $pesan);
        }

        $aturs = $fungsiStatis->ambilCacheAtur();
        $sdms = $fungsiStatis->ambilCacheSDM();
        $posisis = $fungsiStatis->ambilCachePosisiSDM();

        $data = [
            'permin' => $permin,
            'penempatans' => $aturs->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('atur_butir', $lingkupIjin);
            })->sortBy(['atur_butir', 'asc']),
            'posisis' => $posisis,
            'statuses' => $aturs->where('atur_jenis', 'STATUS PERMOHONAN')->sortBy(['atur_butir', 'asc']),
            'sdms' => $sdms->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('penempatan_lokasi', $lingkupIjin);
            }),
        ];

        $HtmlPenuh = $app->view->make('sdm.permintaan-tambah-sdm.tambah-ubah', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }

    public function hapus(FungsiStatis $fungsiStatis, Berkas $berkas, $uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $uuid && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $database = $app->db;

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $permin = $this->dataDasar()->clone()->addSelect('tambahsdm_uuid')->where('tambahsdm_uuid', $uuid)->when($lingkupIjin, function ($query, $lingkupIjin) {
            $query->whereIn('tambahsdm_penempatan', $lingkupIjin);
        })->first();

        abort_unless($permin, 404, 'Data Permintaan Tambah SDM tidak ditemukan.');

        if ($reqs->isMethod('post')) {
            $reqs->merge(['id_penghapus' => $pengguna->sdm_no_absen, 'waktu_dihapus' => $app->date->now()]);
            $validasi = $app->validator->make(
                $reqs->all(),
                [
                    'alasan' => ['required', 'string'],
                    'id_penghapus' => ['required', 'string'],
                    'waktu_dihapus' => ['required', 'date'],
                ],
                [],
                [
                    'alasan' => 'Alasan Penghapusan',
                    'id_penghapus' => 'ID Penghapus',
                    'waktu_dihapus' => 'Waktu Dihapus',
                ]
            );

            $validasi->validate();

            abort_unless($app->filesystem->exists('contoh/data-dihapus.xlsx'), 404, 'Berkas riwayat penghapusan tidak ditemukan.');

            $dataValid = $validasi->validated();

            $jenisHapus = 'Penempatan SDM';
            $idHapus = $dataValid['id_penghapus'];
            $alasanHapus = $dataValid['alasan'];
            $waktuHapus = $dataValid['waktu_dihapus']->format('Y-m-d H:i:s');
            $hapus = collect($permin)->toJson();

            $dataHapus = [
                $jenisHapus, $hapus, $idHapus, $waktuHapus, $alasanHapus
            ];

            $berkas->rekamHapusDataPermintaanSDM($app, $dataHapus);

            $database->table('tambahsdms')->where('tambahsdm_uuid', $uuid)->delete();

            $fungsiStatis->hapusCacheSDMUmum();

            $perujuk = $reqs->session()->get('tautan_perujuk');
            $pesan = 'Data berhasil dihapus';
            $redirect = $app->redirect;

            return $perujuk ? $redirect->to($perujuk)->with('pesan', $pesan) : $redirect->route('sdm.permintaan-tambah-sdm.data')->with('pesan', $pesan);
        }

        $data = [
            'permin' => $permin
        ];

        $HtmlPenuh = $app->view->make('sdm.permintaan-tambah-sdm.hapus', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }
}
