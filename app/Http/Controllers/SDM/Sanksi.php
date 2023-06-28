<?php

namespace App\Http\Controllers\SDM;

use App\Tambahan\FungsiStatis;
use Illuminate\Validation\Rule;

class Sanksi
{
    public function index(Rule $rule)
    {
        // $app = app();
        // $reqs = $app->request;
        // $pengguna = $reqs->user();
        // $str = str();

        // abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        // $validator = $app->validator->make(
        //     $reqs->all(),
        //     [
        //         'posisi_status' => ['sometimes', 'nullable', 'string', $rule->in(['AKTIF', 'NON-AKTIF'])],
        //         'kata_kunci' => ['sometimes', 'nullable', 'string'],
        //         'bph' => ['sometimes', 'nullable', 'numeric', $rule->in([25, 50, 75, 100])],
        //         'urut.*' => ['sometimes', 'nullable', 'string'],
        //         'penempatan_lokasi.*' => ['sometimes', 'nullable', 'string'],
        //         'penempatan_kontrak.*' => ['sometimes', 'nullable', 'string']
        //     ],
        //     [
        //         'posisi_status.*' => 'Status Jabatan harus sesuai daftar.',
        //         'kata_kunci.*' => 'Kata Kunci Pencarian harus berupa karakter.',
        //         'bph.*' => 'Baris Per halaman tidak sesuai daftar.',
        //         'urut.*.string' => 'Butir Pengaturan urutan #:position wajib berupa karakter.',
        //         'penempatan_lokasi.*.string' => 'Butir Pengaturan urutan #:position wajib berupa karakter.',
        //         'penempatan_kontrak.*.string' => 'Butir Pengaturan urutan #:position wajib berupa karakter.',
        //     ]
        // );

        // if ($validator->fails()) {
        //     return $app->redirect->route('sdm.posisi.data')->withErrors($validator)->withInput();
        // };

        // $urutArray = $reqs->urut;
        // $uruts = $urutArray ? implode(',', array_filter($urutArray)) : null;
        // $kataKunci = $reqs->kata_kunci;

        // $ijin_akses = $pengguna->sdm_ijin_akses;
        // $lingkupIjin = array_filter(explode(',', $ijin_akses));
        // $lingkup_lokasi = collect($reqs->lokasi);
        // $lingkup_akses = $lingkup_lokasi->intersect($lingkupIjin)->count();
        // $maks_akses = collect($lingkupIjin)->count();
        // $permin_akses = $lingkup_lokasi->count();

        // abort_unless(blank($ijin_akses) || ($lingkup_akses <= $maks_akses && $maks_akses >= $permin_akses), 403, 'Akses lokasi lain dibatasi.');

        // $database = $app->db;

        // $kontrak = $database->query()->select('penempatan_posisi', 'penempatan_no_absen', 'penempatan_lokasi', 'penempatan_kontrak')
        // ->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
        //     $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
        // });

        // $cariSub = $this->dataDasar()->clone()->addSelect('posisi_uuid', 'posisi_dibuat', 'penempatan_lokasi', 'penempatan_kontrak', $database->raw('COUNT(DISTINCT CASE WHEN sdm_tgl_berhenti IS NULL THEN sdm_no_absen END) jml_aktif, COUNT(DISTINCT CASE WHEN sdm_tgl_berhenti IS NOT NULL THEN sdm_no_absen END) jml_nonaktif'))
        // ->leftJoinSub($kontrak, 'kontrak', function ($join) {
        //     $join->on('posisi_nama', '=', 'kontrak.penempatan_posisi');
        // })
        // ->leftJoin('sdms', 'sdm_no_absen', '=', 'penempatan_no_absen')
        // ->groupBy('posisi_nama');

        // $cari = $database->query()->addSelect('tsdm.*', $app->db->raw('IF((jml_aktif + jml_nonaktif) > 0, (jml_nonaktif / (jml_nonaktif + jml_aktif)) * 100, 0) as pergantian'))->fromSub($cariSub, 'tsdm')
        // ->when($reqs->posisi_status, function ($query) use ($reqs) {
        //     $query->where('posisi_status', $reqs->posisi_status);
        // })
        // ->when($kataKunci, function ($query, $kataKunci) {
        //     $query->where(function ($group) use ($kataKunci) {
        //         $group->where('posisi_nama', 'like', '%' . $kataKunci . '%')
        //         ->orWhere('posisi_atasan', 'like', '%' . $kataKunci . '%')
        //         ->orWhere('posisi_wlkp', 'like', '%' . $kataKunci . '%')
        //         ->orWhere('posisi_keterangan', 'like', '%' . $kataKunci . '%');
        //     });
        // })
        // ->when($reqs->lokasi, function ($query) use ($reqs) {
        //     $query->whereIn('penempatan_lokasi', $reqs->lokasi);
        // })
        // ->when($reqs->kontrak, function ($query) use ($reqs) {
        //     $query->whereIn('penempatan_kontrak', $reqs->kontrak);
        // })
        // ->when(
        //     $uruts,
        //     function ($query, $uruts) {
        //         $query->orderByRaw($uruts);
        //     },
        //     function ($query) {
        //         $query->latest('posisi_dibuat');
        //     }
        // );


        // if ($reqs->unduh == 'excel') {

        //     set_time_limit(0);
        //     ob_implicit_flush();
        //     ob_end_flush();
        //     header('X-Accel-Buffering: no');

        //     $spreadsheet = new Spreadsheet();
        //     $filename = 'eksporjabatansdm-' . date('YmdHis') . '.xlsx';
        //     Cell::setValueBinder(new CustomValueBinder());
        //     $worksheet = $spreadsheet->getActiveSheet();
        //     $x = 1;

        //     $cari->clone()->chunk(100, function ($hasil) use (&$x, $worksheet) {
        //         if ($x == 1) {
        //             $list = $hasil->map(function ($x) {
        //                 return collect($x)->except(['posisi_uuid']);
        //             })->toArray();
        //             array_unshift($list, array_keys($list[0]));
        //             $worksheet->fromArray($list, NULL, 'A' . $x);
        //             $x++;
        //         } else {
        //             $list = $hasil->map(function ($x) {
        //                 return collect($x)->except(['posisi_uuid']);
        //             })->toArray();
        //             $worksheet->fromArray($list, NULL, 'A' . $x);
        //         };
        //         $x += count($hasil);
        //         echo '<p>Status : Memproses ' . ($x - 2) . ' data jabatan SDM.</p>';
        //     });

        //     echo '<p>Status : Menyiapkan berkas excel.</p>';

        //     $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        //     $writer->setPreCalculateFormulas(false);
        //     $writer->save($app->storagePath("app/unduh/{$filename}"));
        //     $spreadsheet->disconnectWorksheets();
        //     unset($spreadsheet);

        //     echo '<p>Selesai menyiapkan berkas excel. <a href="' . $app->filesystem->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)) . '">Unduh</a>.</p>';

        //     exit();
        // }

        // $aktif = $cari->clone()->sum('jml_aktif');
        // $nonAktif = $cari->clone()->sum('jml_nonaktif');
        // $total = $aktif + $nonAktif;

        // $tabels = $cari->clone()->paginate($reqs->bph ?: 25)->withQueryString();

        // $kunciUrut = array_filter((array) $urutArray);

        // $cacheAtur = FungsiStatis::ambilCacheAtur();

        // $urutPergantian = $str->contains($uruts, 'pergantian');
        // $indexPergantian = (head(array_keys($kunciUrut, 'pergantian ASC')) + head(array_keys(array_filter((array)  $urutArray), 'pergantian DESC')) + 1);
        // $urutPosisi = $str->contains($uruts, 'posisi_nama');
        // $indexPosisi = (head(array_keys($kunciUrut, 'posisi_nama ASC')) + head(array_keys(array_filter((array)  $urutArray), 'posisi_nama DESC')) + 1);
        // $urutAktif = $str->contains($uruts, 'jml_aktif');
        // $indexAktif = (head(array_keys($kunciUrut, 'jml_aktif ASC')) + head(array_keys(array_filter((array)  $urutArray), 'jml_aktif DESC')) + 1);
        // $urutNonAktif = $str->contains($uruts, 'jml_nonaktif');
        // $indexNonAktif = (head(array_keys($kunciUrut, 'jml_nonaktif ASC')) + head(array_keys(array_filter((array)  $urutArray), 'jml_nonaktif DESC')) + 1);

        // $data = [
        //     'tabels' => $tabels,
        //     'urutPergantian' => $urutPergantian,
        //     'indexPergantian' => $indexPergantian,
        //     'urutPosisi' => $urutPosisi,
        //     'indexPosisi' => $indexPosisi,
        //     'urutAktif' => $urutAktif,
        //     'indexAktif' => $indexAktif,
        //     'urutNonAktif' => $urutNonAktif,
        //     'indexNonAktif' => $indexNonAktif,
        //     'lokasis' => $cacheAtur->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
        //         return $query->whereIn('atur_butir', $lingkupIjin);
        //     })->sortBy(['atur_butir', 'asc']),
        //     'kontraks' => $cacheAtur->where('atur_jenis', 'STATUS KONTRAK')->sortBy(['atur_butir', 'asc']),
        //     'aktif' => $aktif,
        //     'nonAktif' => $nonAktif,
        //     'total' => $total
        // ];

        // $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrl()]);

        // $HtmlPenuh = $app->view->make('sdm.posisi.data', $data);
        // $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        // return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
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
            'sanksi_id_pengunggah' => 'ID Pengunggah',
            'sanksi_id_pembuat' => 'ID Pembuat',
            'sanksi_id_pengubah' => 'ID Pengubah',
        ];
    }

    public function dataDasar($database)
    {
        return $database->query()->select('sanksi_uuid', 'sanksi_no_absen', 'sanksi_jenis', 'sanksi_mulai', 'sanksi_selesai', 'sanksi_lap_no', 'sanksi_tambahan', 'sanksi_keterangan')->from('sanksisdms');
    }

    public function dataPelanggaran($database)
    {
        return $database->query()->select('langgar_uuid', 'langgar_lap_no', 'langgar_no_absen', 'langgar_pelapor', 'langgar_tanggal', 'langgar_status', 'langgar_isi', 'langgar_keterangan')->from('pelanggaransdms');
    }

    public function dasarValidasi()
    {
        $rule = app('Illuminate\Validation\Rule');
        return [
            'sanksi_jenis' => ['required', 'string', $rule->exists('aturs', 'atur_butir')->where(function ($query) {
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
        // $app = app();
        // $reqs = $app->request;
        // $pengguna = $reqs->user();
        // $str = str();

        // abort_unless($pengguna && $uuid && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        // $pos = $this->dataDasar()->clone()->addSelect('posisi_uuid')->where('posisi_uuid', $uuid)->first();

        // abort_unless($pos, 404);

        // $HtmlPenuh = $app->view->make('sdm.posisi.lihat', compact('pos'));
        // $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        // return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }

    public function tambah(FungsiStatis $fungsiStatis, $lap_uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $lap_uuid && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $database = $app->db;

        $kontrak = $database->query()->select('penempatan_uuid', 'penempatan_no_absen', 'penempatan_posisi', 'penempatan_lokasi', 'penempatan_kontrak', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_keterangan')
            ->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
                $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
            });


        $sanksi = $database->query()->select('sanksi_no_absen', 'sanksi_jenis', 'sanksi_lap_no', 'sanksi_selesai')
            ->from('sanksisdms as p1')->where('sanksi_selesai', '=', function ($query) use ($database) {
                $query->select($database->raw('MAX(sanksi_selesai)'))->from('sanksisdms as p2')->whereColumn('p1.sanksi_no_absen', 'p2.sanksi_no_absen');
            });

        $laporan = $this->dataPelanggaran($database)
            ->join('sdms as a', 'langgar_no_absen', '=', 'a.sdm_no_absen')
            ->join('sdms as b', 'langgar_pelapor', '=', 'b.sdm_no_absen')
            ->leftJoinSub($kontrak, 'kontrak_t', function ($join) {
                $join->on('langgar_no_absen', '=', 'kontrak_t.penempatan_no_absen');
            })
            ->leftJoinSub($kontrak, 'kontrak_p', function ($join) {
                $join->on('langgar_pelapor', '=', 'kontrak_p.penempatan_no_absen');
            })
            ->leftJoinSub($sanksi, 'sanksilama', function ($join) {
                $join->on('langgar_no_absen', '=', 'sanksilama.sanksi_no_absen')->on('sanksilama.sanksi_selesai', '>=', 'langgar_tanggal')->on('langgar_lap_no', '!=', 'sanksilama.sanksi_lap_no');
            })
            ->leftJoin('sanksisdms', function ($join) {
                $join->on('langgar_no_absen', '=', 'sanksisdms.sanksi_no_absen')->on('langgar_lap_no', '=', 'sanksisdms.sanksi_lap_no');
            })
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->whereIn('kontrak_t.penempatan_lokasi', $lingkupIjin)
                        ->orWhereIn('kontrak_p.penempatan_lokasi', $lingkupIjin);
                });
            })
            ->where('langgar_uuid', $lap_uuid)->first();

        abort_unless($laporan, 404, 'Data Laporan Pelanggaran tidak ditemukan.');

        if ($reqs->isMethod('post')) {

            $reqs->merge(['sanksi_id_pembuat' => $pengguna->sdm_no_absen, 'sanksi_lap_no' => $laporan->langgar_lap_no, 'sanksi_no_absen' => $laporan->langgar_no_absen]);

            $validasi = $app->validator->make(
                $reqs->all(),
                [
                    'sanksi_lap_no' => ['required', 'string', 'max:20', 'unique:sanksisdms,sanksi_lap_no'],
                    'sanksi_id_pembuat' => ['sometimes', 'nullable', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                    'sanksi_no_absen' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                    ...$this->dasarValidasi()
                ],
                [],
                $this->atributInput()
            );

            $validasi->validate();

            $redirect = $app->redirect;
            $perujuk = $reqs->session()->get('tautan_perujuk');
            $kesalahan = 'Laporan pelanggaran yang dibatalkan tidak dapat dikenai sanksi.';

            if ($laporan->langgar_status == 'DIBATALKAN') {
                return $perujuk ? $redirect->to($perujuk)->withErrors($kesalahan) : $redirect->route('sdm.pelanggaran.data')->withErrors($kesalahan);
            }

            $data = $validasi->safe()->except('sanksi_berkas');

            $database->table('sanksisdms')->insert($data);

            $berkas = $validasi->safe()->only('sanksi_berkas')['sanksi_berkas'] ?? false;

            if ($berkas) {
                $berkas->storeAs('sdm/sanksi/berkas', $validasi->safe()->only('sanksi_no_absen')['sanksi_no_absen'] . ' - '  . $validasi->safe()->only('sanksi_jenis')['sanksi_jenis'] . ' - ' . $validasi->safe()->only('sanksi_mulai')['sanksi_mulai'] . '.pdf');
            }

            // $fungsiStatis->hapusCacheSDMUmum();
            $pesan = $fungsiStatis->statusBerhasil();

            return $perujuk ? $redirect->to($perujuk)->with('pesan', $pesan) : $redirect->route('sdm.pelanggaran.data')->with('pesan', $pesan);
        }

        $aturs = $fungsiStatis->ambilCacheAtur();

        $data = [
            'sanksis' => $aturs->where('atur_jenis', 'SANKSI SDM')->sortBy(['atur_jenis', 'asc'], ['atur_butir', 'desc']),
        ];

        $HtmlPenuh = $app->view->make('sdm.sanksi.tambah-ubah', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }

    public function ubah(FungsiStatis $fungsiStatis, $uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $uuid && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $database = $app->db;

        $kontrak = $database->query()->select('penempatan_uuid', 'penempatan_no_absen', 'penempatan_posisi', 'penempatan_lokasi', 'penempatan_kontrak', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_keterangan')
            ->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
                $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
            });

        $sanksiLama = $this->dataDasar($database)->addSelect('langgar_status')
            ->leftJoin('pelanggaransdms', 'sanksi_lap_no', '=', 'langgar_lap_no')
            ->leftJoinSub($kontrak, 'kontrak_t', function ($join) {
                $join->on('sanksi_no_absen', '=', 'kontrak_t.penempatan_no_absen');
            })
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->whereIn('kontrak_t.penempatan_lokasi', $lingkupIjin);
                });
            })
            ->where('sanksi_uuid', $uuid)->first();

        abort_unless($sanksiLama, 404, 'Data Laporan Pelanggaran tidak ditemukan.');

        if ($reqs->isMethod('post')) {

            $reqs->merge(['sanksi_id_pengubah' => $pengguna->sdm_no_absen]);

            $validasi = $app->validator->make(
                $reqs->all(),
                [
                    'sanksi_id_pengubah' => ['sometimes', 'nullable', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                    ...$this->dasarValidasi()
                ],
                [],
                $this->atributInput()
            );

            $validasi->validate();

            $redirect = $app->redirect;
            $perujuk = $reqs->session()->get('tautan_perujuk');
            $kesalahan = 'Laporan pelanggaran yang dibatalkan tidak dapat dikenai sanksi.';

            if ($sanksiLama->langgar_status == 'DIBATALKAN') {
                return $perujuk ? $redirect->to($perujuk)->withErrors($kesalahan) : $redirect->route('sdm.pelanggaran.data')->withErrors($kesalahan);
            }

            $data = $validasi->safe()->except('sanksi_berkas');

            $database->table('sanksisdms')->where('sanksi_uuid', $uuid)->update($data);

            $berkas = $validasi->safe()->only('sanksi_berkas')['sanksi_berkas'] ?? false;

            if ($berkas) {
                $berkas->storeAs('sdm/sanksi/berkas', $validasi->safe()->only('sanksi_no_absen')['sanksi_no_absen'] . ' - '  . $validasi->safe()->only('sanksi_jenis')['sanksi_jenis'] . ' - ' . $validasi->safe()->only('sanksi_mulai')['sanksi_mulai'] . '.pdf');
            }

            // $fungsiStatis->hapusCacheSDMUmum();
            $pesan = $fungsiStatis->statusBerhasil();

            return $perujuk ? $redirect->to($perujuk)->with('pesan', $pesan) : $redirect->route('sdm.pelanggaran.data')->with('pesan', $pesan);
        }

        $aturs = $fungsiStatis->ambilCacheAtur();

        $data = [
            'sanksiLama' => $sanksiLama,
            'sanksis' => $aturs->where('atur_jenis', 'SANKSI SDM')->sortBy(['atur_jenis', 'asc'], ['atur_butir', 'desc']),
        ];

        $HtmlPenuh = $app->view->make('sdm.sanksi.tambah-ubah', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }
}
