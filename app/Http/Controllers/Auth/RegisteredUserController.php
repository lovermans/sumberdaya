<?php

namespace App\Http\Controllers\Auth;

use App\Interaksi\Cache;
use App\Interaksi\Rangka;
use Illuminate\Support\Arr;
use App\Interaksi\SDM\SDMCache;
use App\Interaksi\SDM\SDMBerkas;
use App\Interaksi\SDM\SDMDBQuery;
use App\Interaksi\SDM\SDMValidasi;

class RegisteredUserController
{
    /**, 
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str($pengguna?->sdm_hak_akses)->contains('SDM-PENGURUS'), 403);

        $aturs = Cache::ambilCacheAtur();
        $permintaanSdms = SDMCache::ambilCachePermintaanTambahSDM();
        $atasan = SDMCache::ambilCacheSDM();

        $data = [
            'permintaanSdms' => $permintaanSdms,
            'atasans' => $atasan,
            'negaras' => $aturs->where('atur_jenis', 'NEGARA')->sortBy(['atur_butir', 'asc']),
            'kelamins' => $aturs->where('atur_jenis', 'KELAMIN')->sortBy(['atur_butir', 'asc']),
            'gdarahs' => $aturs->where('atur_jenis', 'GOLONGAN DARAH')->sortBy(['atur_butir', 'asc']),
            'agamas' => $aturs->where('atur_jenis', 'AGAMA')->sortBy(['atur_butir', 'asc']),
            'kawins' => $aturs->where('atur_jenis', 'STATUS MENIKAH')->sortBy(['atur_butir', 'asc']),
            'pendidikans' => $aturs->where('atur_jenis', 'PENDIDIKAN')->sortBy(['atur_butir', 'asc']),
            'disabilitas' => $aturs->where('atur_jenis', 'DISABILITAS')->sortBy(['atur_butir', 'asc']),
            'banks' => $aturs->where('atur_jenis', 'BANK')->sortBy(['atur_butir', 'asc']),
            'seragams' => $aturs->where('atur_jenis', 'UKURAN SERAGAM')->sortBy(['atur_butir', 'asc']),
            'phks' => $aturs->where('atur_jenis', 'JENIS BERHENTI')->sortBy(['atur_butir', 'asc']),
            'perans' => $aturs->where('atur_jenis', 'PERAN')->sortBy(['atur_butir', 'asc']),
            'penempatans' => $aturs->where('atur_jenis', 'PENEMPATAN')->sortBy(['atur_butir', 'asc']),
        ];

        $HtmlPenuh = $app->view->make('tambah-ubah-akun', $data);
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());

        return $reqs->pjax()
            ? $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make($HtmlIsi)->withHeaders(['Vary' => 'Accept'])
            : $HtmlPenuh;
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str($pengguna?->sdm_hak_akses)->contains('SDM-PENGURUS'), 403);

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $reqs->merge(['sdm_id_pembuat' => $pengguna->sdm_no_absen, 'password' => $app->hash->make($reqs->sdm_no_ktp) ?? null]);

        $validasi = SDMValidasi::validasiTambahDataSDM([$reqs->all()]);

        $validasi->validate();

        $valid = $validasi->safe()->all()[0];

        $data = Arr::except($valid, ['foto_profil', 'sdm_berkas']);

        SDMDBQuery::tambahDataSDM($data);

        $foto = Arr::only($valid, ['foto_profil'])['foto_profil'] ?? false;
        $berkas = Arr::only($valid, ['sdm_berkas'])['sdm_berkas'] ?? false;
        $no_absen = Arr::only($valid, ['sdm_no_absen'])['sdm_no_absen'];

        if ($foto) {
            SDMBerkas::simpanFotoSDM($foto, $no_absen);
        }

        if ($berkas) {
            SDMBerkas::simpanBerkasSDM($berkas, $no_absen);
        }

        SDMCache::hapusCacheSDMUmum();

        $pesan = Rangka::statusBerhasil();

        $perujuk = $reqs->session()->get('tautan_perujuk');

        $redirect = $app->redirect;

        return $perujuk
            ? $redirect->to($perujuk)->with('pesan', $pesan)
            : $redirect->route('sdm.mulai')->with('pesan', $pesan);
    }
}
