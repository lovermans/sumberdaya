<?php

namespace App\Http\Controllers\SDM;

use Illuminate\Support\Arr;
use App\Tambahan\FungsiStatis;
use Illuminate\Validation\Rule;
use App\Tambahan\CustomValueBinder;
use App\Http\Controllers\SDM\Berkas;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Pelanggaran
{
    public function index(Rule $rule, FungsiStatis $fungsiStatis, Berkas $berkas, $uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $validator = $app->validator->make(
            $reqs->all(),
            [
                'kata_kunci' => ['sometimes', 'nullable', 'string'],
                'tgl_langgar_mulai' => ['sometimes', 'nullable', 'date'],
                'tgl_langgar_sampai' => ['sometimes', 'nullable', 'required_with:tgl_langgar_mulai', 'date', 'after:tgl_langgar_mulai'],
                'langgar_status.*' => ['sometimes', 'nullable', 'string', $rule->in(['DIPROSES', 'DIBATALKAN'])],
                'langgar_penempatan.*' => ['sometimes', 'nullable', 'string'],
                'langgar_proses' => ['sometimes', 'nullable', 'string', $rule->in(['SELESAI', 'BELUM SELESAI'])],
                'bph' => ['sometimes', 'nullable', 'numeric', $rule->in([25, 50, 75, 100])],
                'urut.*' => ['sometimes', 'nullable', 'string']
            ],
            [
                'tgl_langgar_mulai.*' => 'Tanggal Mulai Laporan wajib berupa tanggal valid.',
                'tgl_langgar_sampai.*' => 'Tanggal Akhir Laporan wajib berupa tanggal valid dan lebih lama dari Tanggal Mulai.',
                'langgar_status.*' => 'Status Laporan tidak sesuai daftar.',
                'kata_kunci.*' => 'Kata Kunci Pencarian harus berupa karakter.',
                'langgar_penempatan.*' => 'Lokasi #:position wajib berupa karakter.',
                'langgar_proses.*' => 'Proses Laporan tidak sesuai daftar.',
                'bph.*' => 'Baris Per halaman tidak sesuai daftar.',
                'urut.*.string' => 'Butir Pengaturan urutan #:position wajib berupa karakter.'
            ]
        );

        if ($validator->fails()) {
            return $app->redirect->route('sdm.pelanggaran.data')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $cacheAtur = $fungsiStatis->ambilCacheAtur();

        $lokasis = $cacheAtur->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
            return $query->whereIn('atur_butir', $lingkupIjin)->sortBy(['atur_butir', 'asc']);
        });

        $urutArray = $reqs->urut;

        $uruts = $urutArray ? implode(',', array_filter($urutArray)) : null;

        $kataKunci = $reqs->kata_kunci;

        $database = $app->db;

        $kontrak = $database->query()->select('penempatan_uuid', 'penempatan_no_absen', 'penempatan_posisi', 'penempatan_lokasi', 'penempatan_kontrak', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_keterangan')
            ->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
                $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
            });

        $sanksi = $database->query()->select('sanksi_no_absen', 'sanksi_jenis', 'sanksi_lap_no', 'sanksi_selesai')
            ->from('sanksisdms as p1')->where('sanksi_selesai', '=', function ($query) use ($database) {
                $query->select($database->raw('MAX(sanksi_selesai)'))->from('sanksisdms as p2')->whereColumn('p1.sanksi_no_absen', 'p2.sanksi_no_absen');
            });

        $cari = $this->dataDasar()->addSelect('a.sdm_uuid as langgar_tsdm_uuid', 'a.sdm_nama as langgar_tsdm_nama', 'a.sdm_tgl_berhenti as langgar_tsdm_tgl_berhenti', 'kontrak_t.penempatan_lokasi as langgar_tlokasi', 'kontrak_t.penempatan_posisi as langgar_tposisi', 'kontrak_t.penempatan_kontrak as langgar_tkontrak', 'b.sdm_uuid as langgar_psdm_uuid', 'b.sdm_nama as langgar_psdm_nama', 'b.sdm_tgl_berhenti as langgar_psdm_tgl_berhenti', 'kontrak_p.penempatan_lokasi as langgar_plokasi', 'kontrak_p.penempatan_posisi as langgar_pposisi', 'kontrak_p.penempatan_kontrak as langgar_pkontrak', 'sanksilama.sanksi_jenis as sanksi_aktif_sebelumnya', 'sanksilama.sanksi_lap_no as lap_no_sebelumnya', 'sanksilama.sanksi_selesai as sanksi_selesai_sebelumnya', 'sanksisdms.sanksi_uuid as final_sanksi_uuid', 'sanksisdms.sanksi_jenis as final_sanksi_jenis', 'sanksisdms.sanksi_mulai as final_sanksi_mulai', 'sanksisdms.sanksi_selesai as final_sanksi_selesai', 'sanksisdms.sanksi_tambahan as final_sanksi_tambahan', 'sanksisdms.sanksi_keterangan as final_sanksi_keterangan')
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
            ->when($reqs->langgar_proses == 'SELESAI', function ($query) {
                $query->whereNotNull('sanksisdms.sanksi_jenis')->where('langgar_status', '=', 'DIPROSES');
            })
            ->when($reqs->langgar_proses == 'BELUM SELESAI', function ($query) {
                $query->whereNull('sanksisdms.sanksi_jenis')->where('langgar_status', '=', 'DIPROSES');
            })
            ->when($reqs->langgar_status, function ($query) use ($reqs) {
                $query->whereIn('langgar_status', (array) $reqs->langgar_status);
            })
            ->when($reqs->langgar_penempatan, function ($query) use ($reqs) {
                $query->where(function ($group) use ($reqs) {
                    $group->whereIn('kontrak_t.penempatan_lokasi', (array) $reqs->langgar_penempatan)
                        ->orWhereIn('kontrak_p.penempatan_lokasi', (array) $reqs->langgar_penempatan);
                });
            })
            ->when($kataKunci, function ($query) use ($kataKunci) {
                $query->where(function ($group) use ($kataKunci) {
                    $group->where('langgar_lap_no', 'like', '%' . $kataKunci . '%')
                        ->orWhere('langgar_no_absen', 'like', '%' . $kataKunci . '%')
                        ->orWhere('langgar_pelapor', 'like', '%' . $kataKunci . '%')
                        ->orWhere('a.sdm_nama', 'like', '%' . $kataKunci . '%')
                        ->orWhere('b.sdm_nama', 'like', '%' . $kataKunci . '%')
                        ->orWhere('langgar_isi', 'like', '%' . $kataKunci . '%')
                        ->orWhere('langgar_keterangan', 'like', '%' . $kataKunci . '%');
                });
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
                    $query->orderBy('langgar_lap_no', 'desc');
                }
            );

        if ($reqs->unduh == 'excel') {
            return $berkas->unduhIndexPelanggaranSDM($cari, $app);
        }

        $tabels = $cari->clone()->paginate($reqs->bph ?: 25)->withQueryString()->appends(['fragment' => 'pelanggaran-sdm_tabels', 'uuid' => $uuid ?? '']);;

        $kunciUrut = array_filter((array) $urutArray);

        $urutTanggal = $str->contains($uruts, 'langgar_tanggal');
        $indexTanggal = (head(array_keys($kunciUrut, 'langgar_tanggal ASC')) + head(array_keys($kunciUrut, 'langgar_tanggal DESC')) + 1);
        $urutNomor = $str->contains($uruts, 'langgar_lap_no');
        $indexNomor = (head(array_keys($kunciUrut, 'langgar_lap_no ASC')) + head(array_keys($kunciUrut, 'langgar_lap_no DESC')) + 1);

        $data = [
            'tabels' => $tabels,
            'lokasis' => $lokasis,
            'urutTanggal' => $urutTanggal,
            'indexTanggal' => $indexTanggal,
            'urutNomor' => $urutNomor,
            'indexNomor' => $indexNomor,
            'halamanAkun' => $uuid ?? '',
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
        return app('db')->query()->select('langgar_uuid', 'langgar_lap_no', 'langgar_no_absen', 'langgar_pelapor', 'langgar_tanggal', 'langgar_status', 'langgar_isi', 'langgar_keterangan')->from('pelanggaransdms');
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

    public function formulir($uuid = null)
    {
        // $app = app();
        // $reqs = $app->request;
        // $pengguna = $reqs->user();

        // abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        // set_time_limit(0);
        // ob_implicit_flush();
        // ob_end_flush();
        // header('X-Accel-Buffering: no');

        // echo '<p>Memeriksa formulir.</p>';

        // $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        // $permin = $this->dataDasar()->clone()->addSelect('tambahsdm_uuid', 'b.sdm_nama')
        // ->join('sdms as b', 'tambahsdm_sdm_id', '=', 'b.sdm_no_absen')->where('tambahsdm_uuid', $uuid)->when($lingkupIjin, function ($query,$lingkupIjin) {
        //     $query->whereIn('tambahsdm_penempatan', $lingkupIjin);
        // })->first();

        // abort_unless($permin, 404, 'Data Permintaan Tambah SDM tidak ditemukan.');

        // $storage = $app->filesystem;

        // abort_unless($storage->exists("contoh/permintaan-tambah-sdm.docx"), 404, 'Berkas Contoh Formulir Tidak Ditemukan.');

        // $filename = 'permintaan-tambah-sdm-'.$permin->tambahsdm_no.'.docx';

        // $templateProcessor = new TemplateProcessor($app->storagePath('app/contoh/permintaan-tambah-sdm.docx'));

        // echo '<p>Menyiapkan formulir.</p>';

        // $date = $app->date;
        // $str = str();

        // $templateProcessor->setValues([
        //     'tambahsdm_no' => $permin->tambahsdm_no,
        //     'sdm_nama' => $str->limit($permin->sdm_nama, 30),
        //     'tambahsdm_sdm_id' => $permin->tambahsdm_sdm_id,
        //     'tambahsdm_posisi' => $str->limit($permin->tambahsdm_posisi, 30),
        //     'tambahsdm_jumlah' => $permin->tambahsdm_jumlah,
        //     'tambahsdm_alasan' => $str->limit($permin->tambahsdm_alasan, 100),
        //     'tambahsdm_tgl_diusulkan' => strtoupper($date->make($permin->tambahsdm_tgl_diusulkan)?->translatedFormat('d F Y')),
        //     'tambahsdm_tgl_dibutuhkan' => strtoupper($date->make($permin->tambahsdm_tgl_dibutuhkan)?->translatedFormat('d F Y'))
        // ]);

        // $templateProcessor->saveAs($app->storagePath("app/unduh/{$filename}"));

        // echo '<p>Selesai menyiapkan berkas formulir. <a href="' . $storage->disk('local')->temporaryUrl("unduh/{$filename}", $date->now()->addMinutes(5)) . '">Unduh</a>.</p>';

        // exit();
    }

    public function lihat($uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

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

        $langgar = $this->dataDasar()->addSelect('a.sdm_uuid as langgar_tsdm_uuid', 'a.sdm_nama as langgar_tsdm_nama', 'a.sdm_tgl_berhenti as langgar_tsdm_tgl_berhenti', 'kontrak_t.penempatan_lokasi as langgar_tlokasi', 'kontrak_t.penempatan_posisi as langgar_tposisi', 'kontrak_t.penempatan_kontrak as langgar_tkontrak', 'b.sdm_uuid as langgar_psdm_uuid', 'b.sdm_nama as langgar_psdm_nama', 'b.sdm_tgl_berhenti as langgar_psdm_tgl_berhenti', 'kontrak_p.penempatan_lokasi as langgar_plokasi', 'kontrak_p.penempatan_posisi as langgar_pposisi', 'kontrak_p.penempatan_kontrak as langgar_pkontrak', 'sanksilama.sanksi_jenis as sanksi_aktif_sebelumnya', 'sanksilama.sanksi_lap_no as lap_no_sebelumnya', 'sanksilama.sanksi_selesai as sanksi_selesai_sebelumnya', 'sanksisdms.sanksi_uuid as final_sanksi_uuid', 'sanksisdms.sanksi_jenis as final_sanksi_jenis', 'sanksisdms.sanksi_mulai as final_sanksi_mulai', 'sanksisdms.sanksi_selesai as final_sanksi_selesai', 'sanksisdms.sanksi_tambahan as final_sanksi_tambahan', 'sanksisdms.sanksi_keterangan as final_sanksi_keterangan')
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
            ->where('langgar_uuid', $uuid)->first();

        abort_unless($langgar, 404, 'Data Laporan Pelanggaran tidak ditemukan.');

        $data = [
            'langgar' => $langgar
        ];

        $HtmlPenuh = $app->view->make('sdm.pelanggaran.lihat', $data);
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

            $database = $app->db;

            $hitungLaporan = $this->dataDasar()->addSelect($database->raw('COUNT(DISTINCT langgar_lap_no) jml_lap'))->whereYear('langgar_dibuat', date('Y'))->whereMonth('langgar_dibuat', date('m'))->groupBy('langgar_lap_no');

            $hitungNomor = $database->query()->select('nolap.jml_lap')->fromSub($hitungLaporan, 'nolap')->sum('jml_lap');

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

            $validasi = $app->validator->make(
                $dataMap,
                [
                    '*.langgar_no_absen' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                    '*.langgar_lap_no' => ['required', 'string', 'max:20', 'unique:pelanggaransdms,langgar_lap_no'],
                    '*.langgar_pelapor' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                    '*.langgar_tanggal' => ['required', 'date'],
                    '*.langgar_status' => ['required', 'in:DIPROSES,DIBATALKAN'],
                    '*.langgar_isi' => ['required', 'string'],
                    '*.langgar_keterangan' => ['sometimes', 'nullable', 'string'],
                    '*.langgar_id_pembuat' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                ],
                [
                    '*.langgar_no_absen.*' => 'No Absen Terlapor urutan ke-:position maksimal 10 karakter dan terdaftar di data SDM.',
                    '*.langgar_lap_no.*' => 'No Laporan urutan ke-:position maksimal 20 karakter atau sudah pernah dipakai sebelumnya.',
                    '*.langgar_pelapor.*' => 'No Absen Pelapor maksimal 10 karakter dan terdaftar di data SDM.',
                    '*.langgar_tanggal.*' => 'Tanggal Laporan tidak valid.',
                    '*.langgar_status.*' => 'Status Laporan tidak sesuai.',
                    '*.langgar_isi.*' => 'Isi Laporan wajib berupa karakter.',
                    '*.langgar_keterangan.*' => 'Keterangan wajib berupa karakter.',
                    '*.langgar_id_pembuat.*' => 'ID Pembuat maksimal 10 karakter dan terdaftar di data SDM.',
                ]
            );

            $validasi->validate();

            $validasiBerkas = $app->validator->make(
                $reqs->only('berkas_laporan'),
                [
                    'berkas_laporan' => ['sometimes', 'file', 'mimetypes:application/pdf'],
                ],
                [],
                [
                    'berkas_laporan' => 'Berkas Laporan',
                ]
            );

            $validasiBerkas->validate();

            $database->table('pelanggaransdms')->insert($validasi->validated());

            $berkas = $validasiBerkas->validated()['berkas_laporan'] ?? false;

            if ($berkas) {

                $valid = $validasi->safe()->only(['*.langgar_lap_no'])['*']['langgar_lap_no'];

                array_walk($valid, function ($x, $y) use ($berkas) {
                    $berkas->storeAs('sdm/pelanggaran/berkas', $x . '.pdf');
                });
            }

            $fungsiStatis->hapusCacheSDMUmum();
            $redirect = $app->redirect;
            $perujuk = $reqs->session()->get('tautan_perujuk');
            $pesan = $fungsiStatis->statusBerhasil();

            return $perujuk ? $redirect->to($perujuk)->with('pesan', $pesan) : $redirect->route('sdm.pelanggaran.data')->with('pesan', $pesan);
        }

        $sdms = $fungsiStatis->ambilCacheSDM();
        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $data = [
            'sdms' => $sdms->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('penempatan_lokasi', $lingkupIjin);
            }),
        ];

        $HtmlPenuh = $app->view->make('sdm.pelanggaran.tambah-ubah', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }

    public function ubah(FungsiStatis $fungsiStatis, $uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $database = $app->db;

        $kontrak = $database->query()->select('penempatan_uuid', 'penempatan_no_absen', 'penempatan_posisi', 'penempatan_lokasi', 'penempatan_kontrak', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_keterangan')
            ->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
                $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
            });

        $langgar = $this->dataDasar()
            ->leftJoinSub($kontrak, 'kontrak_t', function ($join) {
                $join->on('langgar_no_absen', '=', 'kontrak_t.penempatan_no_absen');
            })
            ->leftJoinSub($kontrak, 'kontrak_p', function ($join) {
                $join->on('langgar_pelapor', '=', 'kontrak_p.penempatan_no_absen');
            })
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->whereIn('kontrak_t.penempatan_lokasi', $lingkupIjin)
                        ->orWhereIn('kontrak_p.penempatan_lokasi', $lingkupIjin);
                });
            })
            ->where('langgar_uuid', $uuid)->first();

        abort_unless($langgar, 404, 'Data Laporan Pelanggaran tidak ditemukan.');

        if ($reqs->isMethod('post')) {

            $reqs->merge(['langgar_id_pengubah' => $pengguna->sdm_no_absen]);

            $aturan = [
                'langgar_no_absen' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                'langgar_pelapor' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                'langgar_tanggal' => ['required', 'date'],
                'langgar_status' => ['required', 'in:DIPROSES,DIBATALKAN'],
                'langgar_isi' => ['required', 'string'],
                'langgar_keterangan' => ['sometimes', 'nullable', 'string'],
                'langgar_id_pengubah' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                'berkas_laporan' => ['sometimes', 'file', 'mimetypes:application/pdf'],
            ];

            if ($reqs->header('X-Minta-Javascript', false)) {
                $aturan = Arr::only($aturan, ['langgar_status', 'langgar_id_pengubah']);
            }

            $reqs->merge(['langgar_id_pengubah' => $pengguna->sdm_no_absen]);

            $validasi = $app->validator->make(
                $reqs->all(),
                $aturan,
                [
                    'langgar_no_absen' => 'Terlapor wajib maksimal 10 karakter dan terdaftar sebagai SDM.',
                    'langgar_pelapor' => 'Pelapor wajib maksimal 10 karakter dan terdaftar sebagai SDM.',
                    'langgar_tanggal' => 'Tanggal Laporan wajib berupa tanggal.',
                    'langgar_status' => 'Status Laporan wajib sesuai daftar.',
                    'langgar_isi' => 'Isi Laporan wajib berupa karakter.',
                    'langgar_keterangan' => 'Keterangan Laporan perlu berupa karakter.',
                    'langgar_id_pengubah' => 'ID Pengubah wajib maksimal 10 karakter dan terdaftar sebagai SDM.',
                    'berkas_laporan' => 'Berkas laporan wajib berupa berupa berkas format PDF.',
                ]
            );

            $validasi->validate();

            $data = $validasi->safe()->except('berkas_laporan');

            $app->db->table('pelanggaransdms')->where('langgar_uuid', $uuid)->update($data);

            $fungsiStatis->hapusCacheSDMUmum();
            $pesan = $fungsiStatis->statusBerhasil();
            $session = $reqs->session();

            if ($reqs->header('X-Minta-Javascript', false)) {
                $session->now('pesan', $pesan);
                return view('pemberitahuan');
            }

            $nomorLaporan = $langgar->langgar_lap_no;

            $berkas = $validasi->safe()->only('berkas_laporan')['berkas_laporan'] ?? false;

            if ($berkas) {
                $berkas->storeAs('sdm/pelanggaran/berkas', $nomorLaporan . '.pdf');
            }

            $perujuk = $session->get('tautan_perujuk');
            $redirect = $app->redirect;

            return $perujuk ? $redirect->to($perujuk)->with('pesan', $pesan) : $redirect->route('sdm.pelanggaran.data')->with('pesan', $pesan);
        }

        $sdms = $fungsiStatis->ambilCacheSDM();

        $data = [
            'langgar' => $langgar,
            'sdms' => $sdms->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                return $query->whereIn('penempatan_lokasi', $lingkupIjin);
            }),
        ];

        $HtmlPenuh = $app->view->make('sdm.pelanggaran.tambah-ubah', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }
}
