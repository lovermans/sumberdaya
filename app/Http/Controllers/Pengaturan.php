<?php

namespace App\Http\Controllers;

use App\Interaksi\Cache;
use App\Interaksi\Excel;
use App\Interaksi\Berkas;
use App\Interaksi\Rangka;
use App\Interaksi\DBQuery;
use App\Interaksi\Validasi;

class Pengaturan
{
    public function index()
    {
        extract(Rangka::obyekPermintaanRangka(true));
        $str = str();

        abort_unless($pengguna && $str->contains($pengguna->sdm_hak_akses, ['PENGURUS', 'MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku Aplikasi.');

        $cacheAturs = Cache::ambilCacheAtur();

        $validator = Validasi::validasiPermintaanDataPengaturan([$reqs->all()]);

        if ($validator->fails()) {
            return $app->redirect->route('atur.data')->withErrors($validator)->withInput()->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi']);
        };

        $urutArray = $reqs->urut;

        $uruts = $urutArray ? implode(',', array_filter($urutArray)) : null;

        $statuses = $cacheAturs->unique('atur_status')->pluck('atur_status')->merge($reqs->atur_status)->unique()->sort();

        $jenises = $cacheAturs
            ->when($reqs->filled('atur_status'), function ($cacheAturs) use ($reqs) {
                return $cacheAturs->whereIn('atur_status', $reqs->atur_status);
            })->unique('atur_jenis')->pluck('atur_jenis')->merge($reqs->atur_jenis)->unique()->sort();

        $butirs = $cacheAturs
            ->when($reqs->filled('atur_status'), function ($cacheAturs) use ($reqs) {
                return $cacheAturs->whereIn('atur_status', $reqs->atur_status);
            })
            ->when($reqs->filled('atur_jenis'), function ($cacheAturs) use ($reqs) {
                return $cacheAturs->whereIn('atur_jenis', $reqs->atur_jenis);
            })->unique('atur_butir')->pluck('atur_butir')->merge($reqs->atur_butir)->unique()->sort();

        $kunciUrut = array_filter((array) $urutArray);

        $urutJenis = $str->contains($uruts, 'atur_jenis');
        $indexJenis = (head(array_keys($kunciUrut, 'atur_jenis ASC')) + head(array_keys($kunciUrut, 'atur_jenis DESC')) + 1);
        $urutButir = $str->contains($uruts, 'atur_butir');
        $indexButir = (head(array_keys($kunciUrut, 'atur_butir ASC')) + head(array_keys($kunciUrut, 'atur_butir DESC')) + 1);
        $urutStatus = $str->contains($uruts, 'atur_status');
        $indexStatus = (head(array_keys($kunciUrut, 'atur_status ASC')) + head(array_keys($kunciUrut, 'atur_status DESC')) + 1);


        $cari = DBQuery::saringDatabasePengaturan($reqs, $uruts);

        if ($reqs->unduh == 'excel') {
            return Excel::eksporExcelDatabasePengaturan($cari);
        }

        $tabels = $cari->clone()->paginate($reqs->bph ?: 25)->withQueryString()->appends(['fragment' => 'atur_tabels']);

        $data = [
            'tabels' => $tabels,
            'statuses' => $statuses,
            'jenises' => $jenises,
            'butirs' => $butirs,
            'urutJenis' => $urutJenis,
            'indexJenis' => $indexJenis,
            'urutButir' => $urutButir,
            'indexButir' => $indexButir,
            'urutStatus' => $urutStatus,
            'indexStatus' => $indexStatus,
        ];

        $reqs->session()->put(['tautan_perujuk' => $reqs->fullUrlWithoutQuery('fragment')]);

        $HtmlPenuh = $app->view->make('pengaturan.data', $data);
        $respon = $app->make('Illuminate\Contracts\Routing\ResponseFactory');

        return $reqs->pjax() && (!$reqs->filled('fragment') || !$reqs->header('X-Frag', false))
            ? $respon->make(implode('', $HtmlPenuh->renderSections()))->withHeaders(['Vary' => 'Accept', 'X-Tujuan' => 'isi'])
            : $respon->make($HtmlPenuh->fragmentIf($reqs->filled('fragment') && $reqs->pjax() && $reqs->header('X-Frag', false), $reqs->fragment))->withHeaders(['Vary' => 'Accept']);
    }

    public function contohUnggah()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna->sdm_hak_akses, ['PENGURUS', 'MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku Aplikasi.');

        abort_unless($app->filesystem->exists("contoh/unggah-umum.xlsx"), 404, 'Berkas Contoh Ekspor Tidak Ditemukan.');

        return Excel::eksporExcelContohUnggahPengaturan(DBQuery::ambilDatabasePengaturan()->orderBy('aturs.id', 'desc'));
    }

    public function lihat($uuid)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        abort_unless($pengguna && str()->contains($pengguna->sdm_hak_akses, ['PENGURUS', 'MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku Aplikasi.');

        $atur = DBQuery::ambilDatabasePengaturan()->addSelect('atur_uuid')->where('atur_uuid', $uuid)->first();

        abort_unless($atur, 404, 'Data Pengaturan tidak ditemukan.');

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make(implode('', $app->view->make('pengaturan.lihat', compact('atur'))->renderSections()))->withHeaders(['Vary' => 'Accept']);
    }

    public function tambah()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        abort_unless($pengguna && str()->contains($pengguna->sdm_hak_akses, ['PENGURUS']), 403, 'Akses dibatasi hanya untuk Pengurus Aplikasi.');

        if ($reqs->isMethod('post')) {

            $reqs->merge(['atur_id_pembuat' => $pengguna->sdm_no_absen]);

            $validasi = Validasi::validasiTambahDataPengaturan([$reqs->all()]);

            $validasi->validate();

            $data = $validasi->safe()->all();

            DBQuery::tambahDatabasePengaturan($data[0]);

            Cache::hapusCacheAtur();

            return $app->redirect->route('atur.data')->with('pesan', Rangka::statusBerhasil());
        }

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make(implode('', $app->view->make('pengaturan.tambah-ubah')->renderSections()))->withHeaders(['Vary' => 'Accept']);
    }

    public function ubah($uuid)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna->sdm_hak_akses, ['PENGURUS']), 403, 'Akses dibatasi hanya untuk Pengurus Aplikasi.');

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $atur = DBQuery::ambilDatabasePengaturan()->addSelect('atur_uuid')->where('atur_uuid', $uuid)->first();

        abort_unless($atur, 404, 'Data Pengaturan tidak ditemukan.');

        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        $halaman = $app->view;

        if ($reqs->isMethod('post')) {

            $reqs->merge(['atur_id_pengubah' => $pengguna->sdm_no_absen]);

            $validasi = Validasi::validasiUbahDataPengaturan($uuid, [$reqs->all()]);

            $validasi->validate();

            $data = $validasi->safe()->all();

            DBQuery::ubahDatabasePengaturan($uuid, $data[0]);

            Cache::hapusCacheAtur();

            $pesan = Rangka::statusBerhasil();
            $session = $reqs->session();
            $redirect = $app->redirect;

            if ($reqs->header('X-Minta-Javascript', false)) {

                $session->now('pesan', $pesan);

                return $tanggapan->make($halaman->make('pemberitahuan'))->withHeaders([
                    'Vary' => 'Accept',
                    'X-Tujuan' => 'sematan_javascript',
                    'X-Kode-Javascript' => true
                ]);
            }

            $perujuk = $session->get('tautan_perujuk');

            return $perujuk
                ? $redirect->to($perujuk)->with('pesan', $pesan)
                : $redirect->route('atur.data')->with('pesan', $pesan);
        }

        $data = [
            'atur' => $atur,
        ];

        return $tanggapan->make(implode('', $halaman->make('pengaturan.tambah-ubah', $data)->renderSections()))->withHeaders(['Vary' => 'Accept']);
    }

    public function unggah()
    {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($pengguna && str()->contains($pengguna->sdm_hak_akses, ['PENGURUS']), 403, 'Akses dibatasi hanya untuk Pengurus Aplikasi.');

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        if ($reqs->isMethod('post')) {
            $validasifile = Validasi::validasiBerkasImporDataPengaturan($reqs->all());

            $validasifile->validate();

            $file = $validasifile->safe()->only('atur_unggah')['atur_unggah'];

            $namafile = 'unggahpengaturan-' . date('YmdHis') . '.xlsx';

            Berkas::simpanBerkasImporExcelSementara($file, $namafile);

            $fileexcel = Berkas::ambilBerkasImporExcelSementara($namafile);

            return Excel::imporExcelDataPengaturan($fileexcel);
        };

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make(implode('', $app->view->make('pengaturan.unggah')->renderSections()))->withHeaders(['Vary' => 'Accept']);
    }
}
