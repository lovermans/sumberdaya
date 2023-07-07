<?php

namespace App\Http\Controllers\SDM;

use App\Tambahan\FungsiStatis;
use Illuminate\Validation\Rule;
use App\Http\Controllers\SDM\Berkas;

class Penilaian
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
                'nilaisdm_tahun.*' => ['sometimes', 'nullable', 'date_format:Y'],
                'nilaisdm_periode.*' => ['sometimes', 'nullable', 'string'],
                'nilaisdm_kontrak.*' =>  ['sometimes', 'nullable', 'string'],
                'nilaisdm_penempatan.*' => ['sometimes', 'nullable', 'string'],
                'unduh' => ['sometimes', 'nullable', 'string', $rule->in(['excel'])],
                'bph' => ['sometimes', 'nullable', 'numeric', $rule->in([25, 50, 75, 100])],
                'urut.*' => ['sometimes', 'nullable', 'string']
            ],
            [
                'kata_kunci.*' => 'Kata Kunci Pencarian harus berupa karakter.',
                'nilaisdm_tahun.*' => 'Tahun Penilaian #:position wajib berupa nilai tahun yang valid.',
                'nilaisdm_periode.*' => 'TPeriode Penilaian #:position wajib berupa karakter.',
                'nilaisdm_kontrak.*' => 'Jenis Kontrak #:position wajib berupa karakter.',
                'sanksi_penempatan.*' => 'Lokasi #:position wajib berupa karakter.',
                'unduh.*' => 'Format ekspor tidak dikenali.',
                'bph.*' => 'Baris Per halaman tidak sesuai daftar.',
                'urut.*.string' => 'Butir Pengaturan urutan #:position wajib berupa karakter.',
            ]
        );

        if ($validator->fails()) {
            return $app->redirect->route('sdm.penilaian.data')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $lingkupIjin = array_filter(explode(',', $pengguna?->sdm_ijin_akses));

        $cacheAtur = $fungsiStatis->ambilCacheAtur();

        $lokasis = $cacheAtur->where('atur_jenis', 'PENEMPATAN')->when($lingkupIjin, function ($query) use ($lingkupIjin) {
            return $query->whereIn('atur_butir', $lingkupIjin)->sortBy(['atur_butir', 'asc']);
        });

        $urutArray = $reqs->urut;

        $uruts = $urutArray ? implode(',', array_filter($urutArray)) : null;

        $kataKunci = $reqs->kata_kunci;

        $database = $app->db;

        $kontrak = $this->dataKontrak($database);

        $cari = $this->dataDasar($database)
            ->addSelect('sdm_uuid', 'sdm_nama', 'sdm_tgl_berhenti', 'penempatan_posisi', 'penempatan_lokasi', 'penempatan_kontrak', $database->raw('(IFNULL(nilaisdm_bobot_hadir, 0) + IFNULL(nilaisdm_bobot_sikap, 0) + IFNULL(nilaisdm_bobot_target, 0)) as nilaisdm_total'))
            ->leftJoin('sdms', 'nilaisdm_no_absen', '=', 'sdm_no_absen')
            ->leftJoinSub($kontrak, 'kontrak', function ($join) {
                $join->on('nilaisdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->when($reqs->nilaisdm_penempatan, function ($query) use ($reqs) {
                $query->where(function ($group) use ($reqs) {
                    $group->whereIn('kontrak.penempatan_lokasi', (array) $reqs->nilaisdm_penempatan);
                });
            })
            ->when($reqs->nilaisdm_kontrak, function ($query) use ($reqs) {
                $query->where(function ($group) use ($reqs) {
                    $group->whereIn('kontrak.penempatan_kontrak', (array) $reqs->nilaisdm_kontrak);
                });
            })
            ->when($reqs->nilaisdm_tahun, function ($query) use ($reqs) {
                $query->where(function ($group) use ($reqs) {
                    $group->whereIn('nilaisdm_tahun', (array) $reqs->nilaisdm_tahun);
                });
            })
            ->when($reqs->nilaisdm_periode, function ($query) use ($reqs) {
                $query->where(function ($group) use ($reqs) {
                    $group->whereIn('nilaisdm_periode', (array) $reqs->nilaisdm_periode);
                });
            })
            ->when($kataKunci, function ($query, $kataKunci) {
                $query->where(function ($group) use ($kataKunci) {
                    $group->where('nilaisdm_no_absen', 'like', '%' . $kataKunci . '%')
                        ->orWhere('sdm_nama', 'like', '%' . $kataKunci . '%')
                        ->orWhere('nilaisdm_tindak_lanjut', 'like', '%' . $kataKunci . '%')
                        ->orWhere('nilaisdm_keterangan', 'like', '%' . $kataKunci . '%');
                });
            })
            ->when($reqs->tgl_sanksi_mulai && $reqs->tgl_sanksi_sampai, function ($query) use ($reqs) {
                $query->whereBetween('sanksi_mulai', [$reqs->tgl_sanksi_mulai, $reqs->tgl_sanksi_sampai]);
            })
            ->when($uuid, function ($query) use ($uuid) {
                $query->where('sdm_uuid', $uuid);
            })
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->whereIn('kontrak.penempatan_lokasi', $lingkupIjin);
                });
            })
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->orderBy('penilaiansdms.id', 'desc');
                }
            );

        if ($reqs->unduh == 'excel') {
            return $berkas->unduhIndexPenilaianSDM($cari, $app);
        }

        $jumlahOS = $cari->clone()->whereNotNull('kontrak.penempatan_kontrak')->where('kontrak.penempatan_kontrak', 'like', 'OS-%')->count();
        $jumlahOrganik = $cari->clone()->whereNotNull('kontrak.penempatan_kontrak')->where('kontrak.penempatan_kontrak', 'not like', 'OS-%')->count();

        $tabels = $cari->clone()->paginate($reqs->bph ?: 25)->withQueryString()->appends(['fragment' => 'nilai-sdm_tabels', 'uuid' => $uuid ?? '']);

        $kunciUrut = array_filter((array) $urutArray);

        $urutTahun = $str->contains($uruts, 'nilaisdm_tahun');
        $indexTahun = (head(array_keys($kunciUrut, 'nilaisdm_tahun ASC')) + head(array_keys($kunciUrut, 'nilaisdm_tahun DESC')) + 1);
        $urutPeriode = $str->contains($uruts, 'nilaisdm_periode');
        $indexPeriode = (head(array_keys($kunciUrut, 'nilaisdm_periode ASC')) + head(array_keys($kunciUrut, 'nilaisdm_periode DESC')) + 1);
        $urutNilai = $str->contains($uruts, 'nilaisdm_total');
        $indexNilai = (head(array_keys($kunciUrut, 'nilaisdm_total ASC')) + head(array_keys($kunciUrut, 'nilaisdm_total DESC')) + 1);

        $data = [
            'tabels' => $tabels,
            'lokasis' => $lokasis,
            'statusSDMs' => $cacheAtur->where('atur_jenis', 'STATUS KONTRAK')->sortBy(['atur_jenis', 'asc'], ['atur_butir', 'asc']),
            'urutTahun' => $urutTahun,
            'indexTahun' => $indexTahun,
            'urutPeriode' => $urutPeriode,
            'indexPeriode' => $indexPeriode,
            'urutNilai' => $urutNilai,
            'indexNilai' => $indexNilai,
            'halamanAkun' => $uuid ?? '',
            'jumlahOS' => $jumlahOS,
            'jumlahOrganik' => $jumlahOrganik
        ];

        if (!isset($uuid)) {
            $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);
        }

        $HtmlPenuh = $app->view->make('sdm.penilaian.data', $data);
        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
            ? $tanggapan->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $tanggapan->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function atributInput()
    {
        return [
            'nilaisdm_tahun' => 'Tahun Penilaian SDM',
            'nilaisdm_periode' => 'Periode Penilaian Berkala SDM',
            'nilaisdm_bobot_hadir' => 'Bobot Nilai Kehadiran',
            'nilaisdm_bobot_sikap' => 'Bobot Nilai Sikap Kerja',
            'nilaisdm_bobot_target' => 'Bobot Nilai Target Kerja',
            'nilaisdm_tindak_lanjut' => 'Tindak Lanjut Penilaian SDM',
            'nilaisdm_keterangan' => 'Keterangan Penilaian SDM',
            'nilai_berkas' => 'Berkas Unggah Penilaian',
            'nilaisdm_id_pengunggah' => 'ID Pengunggah',
            'nilaisdm_id_pembuat' => 'ID Pembuat',
            'nilaisdm_id_pengubah' => 'ID Pengubah',
        ];
    }

    public function dataDasar($database)
    {
        return $database->query()->select('nilaisdm_uuid', 'nilaisdm_no_absen', 'nilaisdm_tahun', 'nilaisdm_periode', 'nilaisdm_bobot_hadir', 'nilaisdm_bobot_sikap', 'nilaisdm_bobot_target', 'nilaisdm_tindak_lanjut', 'nilaisdm_keterangan')->from('penilaiansdms');
    }

    public function dataKontrak($database)
    {
        return $database->query()->select('penempatan_uuid', 'penempatan_no_absen', 'penempatan_posisi', 'penempatan_lokasi', 'penempatan_kontrak', 'penempatan_mulai', 'penempatan_selesai', 'penempatan_ke', 'penempatan_keterangan')
            ->from('penempatans as p1')->where('penempatan_mulai', '=', function ($query) use ($database) {
                $query->select($database->raw('MAX(penempatan_mulai)'))->from('penempatans as p2')->whereColumn('p1.penempatan_no_absen', 'p2.penempatan_no_absen');
            });
    }

    public function dasarValidasi()
    {
        return [
            'nilaisdm_tahun' => ['required', 'date_format:Y'],
            'nilaisdm_periode' => ['required', 'string'],
            'nilaisdm_bobot_hadir' => ['sometimes', 'nullable', 'numeric'],
            'nilaisdm_bobot_sikap' => ['sometimes', 'nullable', 'numeric'],
            'nilaisdm_bobot_target' => ['sometimes', 'nullable', 'numeric'],
            'nilaisdm_tindak_lanjut' => ['sometimes', 'nullable', 'string'],
            'nilaisdm_keterangan' => ['sometimes', 'nullable', 'string'],
            'nilai_berkas' => ['sometimes', 'file', 'mimetypes:application/pdf'],
        ];
    }

    public function lihat($uuid = null)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $uuid && $str->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        $database = $app->db;

        $kontrak = $this->dataKontrak($database);

        $nilai = $this->dataDasar($database)
            ->addSelect('sdm_uuid', 'sdm_nama', 'sdm_tgl_berhenti', 'penempatan_posisi', 'penempatan_lokasi', 'penempatan_kontrak', $database->raw('(IFNULL(nilaisdm_bobot_hadir, 0) + IFNULL(nilaisdm_bobot_sikap, 0) + IFNULL(nilaisdm_bobot_target, 0)) as nilaisdm_total'))
            ->leftJoin('sdms', 'nilaisdm_no_absen', '=', 'sdm_no_absen')
            ->leftJoinSub($kontrak, 'kontrak', function ($join) {
                $join->on('nilaisdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->whereIn('kontrak.penempatan_lokasi', $lingkupIjin);
                });
            })
            ->where('nilaisdm_uuid', $uuid)->first();

        abort_unless($nilai, 404, 'Data Penialain SDM tidak ditemukan.');

        $data = [
            'nilai' => $nilai
        ];

        $HtmlPenuh = $app->view->make('sdm.penilaian.lihat', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }

    public function tambah(FungsiStatis $fungsiStatis)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        $lingkupIjin = array_filter(explode(',', $pengguna->sdm_ijin_akses));

        if ($reqs->isMethod('post')) {

            $reqs->merge(['nilaisdm_id_pembuat' => $pengguna->sdm_no_absen]);

            $validasi = $app->validator->make(
                $reqs->all(),
                [
                    'nilaisdm_id_pembuat' => ['sometimes', 'nullable', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                    'nilaisdm_no_absen' => ['required', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                    ...$this->dasarValidasi()
                ],
                [],
                $this->atributInput()
            );

            $validasi->validate();

            $redirect = $app->redirect;
            $perujuk = $reqs->session()->get('tautan_perujuk');

            $data = $validasi->safe()->except('nilai_berkas');

            $app->db->table('penilaiansdms')->insert($data);

            $berkas = $validasi->safe()->only('nilai_berkas')['nilai_berkas'] ?? false;

            if ($berkas) {
                $berkas->storeAs('sdm/penilaian/berkas', $validasi->safe()->only('nilaisdm_no_absen')['nilaisdm_no_absen'] . ' - '  . $validasi->safe()->only('nilaisdm_tahun')['nilaisdm_tahun'] . ' - ' . $validasi->safe()->only('nilaisdm_periode')['nilaisdm_periode'] . '.pdf');
            }

            $fungsiStatis->hapusCacheNilaiSDM();

            $pesan = $fungsiStatis->statusBerhasil();

            return $perujuk ? $redirect->to($perujuk)->with('pesan', $pesan) : $redirect->route('sdm.penilaian.data')->with('pesan', $pesan);
        }

        $sdms = $fungsiStatis->ambilCacheSDM()->when($lingkupIjin, function ($c) use ($lingkupIjin) {
            return $c->whereIn('penempatan_lokasi', [null, ...$lingkupIjin]);
        });

        $data = [
            'sdms' => $sdms,
        ];

        $HtmlPenuh = $app->view->make('sdm.penilaian.tambah-ubah', $data);
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

        $kontrak = $this->dataKontrak($database);

        $nilai = $this->dataDasar($database)
            ->leftJoinSub($kontrak, 'kontrak', function ($join) {
                $join->on('nilaisdm_no_absen', '=', 'kontrak.penempatan_no_absen');
            })
            ->when($lingkupIjin, function ($query) use ($lingkupIjin) {
                $query->where(function ($group) use ($lingkupIjin) {
                    $group->whereIn('kontrak.penempatan_lokasi', $lingkupIjin);
                });
            })
            ->where('nilaisdm_uuid', $uuid)->first();

        abort_unless($nilai, 404, 'Data Penilaian tidak ditemukan.');

        if ($reqs->isMethod('post')) {

            $reqs->merge(['nilaisdm_id_pengubah' => $pengguna->sdm_no_absen]);

            $validasi = $app->validator->make(
                $reqs->all(),
                [
                    'nilaisdm_id_pengubah' => ['sometimes', 'nullable', 'string', 'max:10', 'exists:sdms,sdm_no_absen'],
                    ...$this->dasarValidasi()
                ],
                [],
                $this->atributInput()
            );

            $validasi->validate();

            $redirect = $app->redirect;
            $perujuk = $reqs->session()->get('tautan_perujuk');

            $data = $validasi->safe()->except('nilai_berkas');

            $database->table('penilaiansdms')->where('nilaisdm_uuid', $uuid)->update($data);

            $berkas = $validasi->safe()->only('nilai_berkas')['nilai_berkas'] ?? false;

            if ($berkas) {
                $berkas->storeAs('sdm/penilaian/berkas', $nilai->nilaisdm_no_absen . ' - '  . $validasi->safe()->only('nilaisdm_tahun')['nilaisdm_tahun'] . ' - ' . $validasi->safe()->only('nilaisdm_periode')['nilaisdm_periode'] . '.pdf');
            }

            $fungsiStatis->hapusCacheNilaiSDM();

            $pesan = $fungsiStatis->statusBerhasil();

            return $perujuk ? $redirect->to($perujuk)->with('pesan', $pesan) : $redirect->route('sdm.penilaian.data')->with('pesan', $pesan);
        }

        $data = [
            'nilai' => $nilai,
        ];

        $HtmlPenuh = $app->view->make('sdm.penilaian.tambah-ubah', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }
}
