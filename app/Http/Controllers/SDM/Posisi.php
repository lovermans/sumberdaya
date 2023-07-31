<?php

namespace App\Http\Controllers\SDM;

use App\Interaksi\Rangka;
use App\Tambahan\FungsiStatis;
use Illuminate\Validation\Rule;
use App\Interaksi\SDM\SDMValidasi;
use App\Interaksi\Cache;
use App\Interaksi\SDM\SDMDBQuery;
use App\Interaksi\SDM\SDMExcel;

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

        $ijin_akses = $pengguna?->sdm_ijin_akses;
        $lingkupIjin = array_filter(explode(',', $ijin_akses));
        $lingkup_lokasi = collect($reqs->lokasi);
        $lingkup_akses = $lingkup_lokasi->intersect($lingkupIjin)->count();
        $maks_akses = collect($lingkupIjin)->count();
        $permin_akses = $lingkup_lokasi->count();

        abort_unless(blank($ijin_akses) || ($lingkup_akses <= $maks_akses && $maks_akses >= $permin_akses), 403, 'Akses lokasi lain dibatasi.');

        $cariSub = SDMDBQuery::ambilKeluarMasukPosisiSDM()
            ->when($reqs->lokasi, function ($query) use ($reqs) {
                $query->whereIn('penempatan_lokasi', $reqs->lokasi);
            })
            ->when($reqs->kontrak, function ($query) use ($reqs) {
                $query->whereIn('penempatan_kontrak', $reqs->kontrak);
            });

        $cari = $app->db->query()
            ->addSelect(
                'tsdm.*',
                $app->db->raw('IF((jml_aktif + jml_nonaktif) > 0, (jml_nonaktif / (jml_nonaktif + jml_aktif)) * 100, 0) as pergantian')
            )
            ->fromSub($cariSub, 'tsdm')
            ->when($reqs->posisi_status, function ($query) use ($reqs) {
                $query->where('posisi_status', $reqs->posisi_status);
            })
            ->when($kataKunci, function ($query, $kataKunci) {
                $query->where(function ($group) use ($kataKunci) {
                    $group->where('posisi_nama', 'like', '%' . $kataKunci . '%')
                        ->orWhere('posisi_atasan', 'like', '%' . $kataKunci . '%')
                        ->orWhere('posisi_wlkp', 'like', '%' . $kataKunci . '%')
                        ->orWhere('posisi_keterangan', 'like', '%' . $kataKunci . '%');
                });
            })
            ->when(
                $uruts,
                function ($query, $uruts) {
                    $query->orderByRaw($uruts);
                },
                function ($query) {
                    $query->latest('posisi_dibuat');
                }
            );

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

    public function atributInput()
    {
        return [
            'posisi_nama' => 'Jabatan',
            'posisi_wlkp' => 'Kode Jabatan WLKP',
            'posisi_atasan' => 'Jabatan Atasan',
            'posisi_keterangan' => 'Keterangan',
            'posisi_status' => 'Status Jabatan',
            'posisi_id_pengunggah' => 'ID Pengunggah',
            'posisi_id_pembuat' => 'ID Pembuat',
            'posisi_id_pengubah' => 'ID Pengubah',
        ];
    }

    public function dasarValidasi()
    {
        return [
            'posisi_wlkp' => ['nullable', 'string', 'max:40'],
            'posisi_keterangan' => ['nullable', 'string', 'max:40'],
            'posisi_status' => ['required', 'string', Rule::in(['AKTIF', 'NON-AKTIF'])],
        ];
    }

    public function lihat($uuid = null)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && $uuid && str()->contains($pengguna?->sdm_hak_akses, ['SDM-PENGURUS', 'SDM-MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku SDM.');

        $pos = SDMDBQuery::ambilDBPosisiSDM()->addSelect('posisi_uuid')->where('posisi_uuid', $uuid)->first();

        abort_unless($pos, 404);

        $HtmlPenuh = $app->view->make('sdm.posisi.lihat', compact('pos'));
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    public function tambah(FungsiStatis $fungsiStatis)
    {
        $app = app();
        $reqs = $app->request;
        $pengguna = $reqs->user();
        $str = str();

        abort_unless($pengguna && $str->contains($pengguna?->sdm_hak_akses, 'SDM-PENGURUS'), 403, 'Akses dibatasi hanya untuk Pengurus SDM.');

        if ($reqs->isMethod('post')) {

            $reqs->merge(['posisi_id_pembuat' => $pengguna->sdm_no_absen]);

            $validasi = $app->validator->make(
                $reqs->all(),
                [
                    'posisi_nama' => ['required', 'string', 'unique:posisis,posisi_nama'],
                    'posisi_atasan' => ['nullable', 'string', 'max:40', 'different:posisi_nama'],
                    'posisi_id_pembuat' => ['sometimes', 'nullable', 'string'],
                    ...$this->dasarValidasi()
                ],
                [],
                $this->atributInput()
            );

            $validasi->validate();

            $data = $validasi->safe()->all();

            $app->db->table('posisis')->insert($data);

            $fungsiStatis->hapusCacheSDMUmum();
            $perujuk = $reqs->session()->get('tautan_perujuk');
            $pesan = $fungsiStatis->statusBerhasil();
            $redirect = $app->redirect;

            return $perujuk ? $redirect->to($perujuk)->with('pesan', $pesan) : $redirect->route('sdm.posisi.data')->with('pesan', $pesan);
        }

        $data = [
            'posisis' => $fungsiStatis->ambilCachePosisiSDM(),
        ];

        $HtmlPenuh = $app->view->make('sdm.posisi.tambah-ubah', $data);
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

        $pos = SDMDBQuery::ambilDBPosisiSDM()->addSelect('posisi_uuid')->where('posisi_uuid', $uuid)->first();

        abort_unless($pos, 404, 'Data Jabatan tidak ditemukan.');

        if ($reqs->isMethod('post')) {

            $reqs->merge(['posisi_id_pengubah' => $pengguna->sdm_no_absen]);

            $validasi = $app->validator->make(
                $reqs->all(),
                [
                    'posisi_nama' => ['required', 'string', 'max:40', Rule::unique('posisis')->where(fn ($query) => $query->whereNot('posisi_uuid', $uuid))],
                    'posisi_atasan' => ['nullable', 'string', 'max:40', 'different:posisi_nama'],
                    'posisi_id_pengunggah' => ['sometimes', 'nullable', 'string'],
                    ...$this->dasarValidasi()
                ],
                [],
                $this->atributInput()
            );

            $validasi->validate();

            $data = $validasi->safe()->all();

            $app->db->table('posisis')->where('posisi_uuid', $uuid)->update($data);

            $fungsiStatis->hapusCacheSDMUmum();
            $perujuk = $reqs->session()->get('tautan_perujuk');
            $pesan = $fungsiStatis->statusBerhasil();
            $redirect = $app->redirect;

            return $perujuk ? $redirect->to($perujuk)->with('pesan', $pesan) : $redirect->route('sdm.posisi.data')->with('pesan', $pesan);
        }

        $data = [
            'posisis' => $fungsiStatis->ambilCachePosisiSDM(),
            'pos' => $pos
        ];

        $HtmlPenuh = $app->view->make('sdm.posisi.tambah-ubah', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        return $reqs->pjax() ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept']) : $HtmlPenuh;
    }
}
