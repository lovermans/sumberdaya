<?php

namespace App\Http\Controllers;

use App\Interaksi\Berkas;
use App\Interaksi\Umum;
use App\Interaksi\Cache;
use App\Interaksi\Excel;
use App\Interaksi\DBQuery;
use App\Interaksi\Validasi;

class Pengaturan
{
    use Umum, Cache, Validasi, DBQuery, Excel, Berkas;

    public function index()
    {
        extract($this->obyekPermintaanUmum());
        $str = str();

        abort_unless($pengguna && $str->contains($pengguna->sdm_hak_akses, ['PENGURUS', 'MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku Aplikasi.');

        $cacheAturs = $this->ambilCacheAtur();

        $validator = $this->validasiPermintaanDataPengaturan([$reqs->all()]);

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


        $cari = $this->saringDatabasePengaturan($reqs, $uruts);

        if ($reqs->unduh == 'excel') {
            return $this->eksporExcelDatabasePengaturan($cari);
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
        extract($this->obyekPermintaanUmum());

        abort_unless($pengguna && str()->contains($pengguna->sdm_hak_akses, ['PENGURUS', 'MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku Aplikasi.');

        abort_unless($app->filesystem->exists("contoh/unggah-umum.xlsx"), 404, 'Berkas Contoh Ekspor Tidak Ditemukan.');

        return $this->eksporExcelContohUnggahPengaturan($this->ambilDatabasePengaturan()->orderBy('aturs.id', 'desc'));
    }

    public function lihat($uuid)
    {
        extract($this->obyekPermintaanUmum());

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        abort_unless($pengguna && str()->contains($pengguna->sdm_hak_akses, ['PENGURUS', 'MANAJEMEN']), 403, 'Akses dibatasi hanya untuk Pemangku Aplikasi.');

        $atur = $this->ambilDatabasePengaturan()->addSelect('atur_uuid')->where('atur_uuid', $uuid)->first();

        abort_unless($atur, 404, 'Data Pengaturan tidak ditemukan.');

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make(implode('', $app->view->make('pengaturan.lihat', compact('atur'))->renderSections()))->withHeaders(['Vary' => 'Accept']);
    }

    public function tambah()
    {
        extract($this->obyekPermintaanUmum());

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        abort_unless($pengguna && str()->contains($pengguna->sdm_hak_akses, ['PENGURUS']), 403, 'Akses dibatasi hanya untuk Pengurus Aplikasi.');

        if ($reqs->isMethod('post')) {

            $reqs->merge(['atur_id_pembuat' => $pengguna->sdm_no_absen]);

            $validasi = $this->validasiTambahDataPengaturan([$reqs->all()]);

            $validasi->validate();

            $data = $validasi->safe()->all();

            $this->tambahDatabasePengaturan($data[0]);

            $this->hapusCacheAtur();

            return $app->redirect->route('atur.data')->with('pesan', $this->statusBerhasil());
        }

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make(implode('', $app->view->make('pengaturan.tambah-ubah')->renderSections()))->withHeaders(['Vary' => 'Accept']);
    }

    public function ubah($uuid)
    {
        extract($this->obyekPermintaanUmum());

        abort_unless($pengguna && str()->contains($pengguna->sdm_hak_akses, ['PENGURUS']), 403, 'Akses dibatasi hanya untuk Pengurus Aplikasi.');

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        $atur = $this->ambilDatabasePengaturan()->addSelect('atur_uuid')->where('atur_uuid', $uuid)->first();

        abort_unless($atur, 404, 'Data Pengaturan tidak ditemukan.');

        $tanggapan = $app->make('Illuminate\Contracts\Routing\ResponseFactory');
        $halaman = $app->view;

        if ($reqs->isMethod('post')) {

            $reqs->merge(['atur_id_pengubah' => $pengguna->sdm_no_absen]);

            $validasi = $this->validasiUbahDataPengaturan($uuid, [$reqs->all()]);

            $validasi->validate();

            $data = $validasi->safe()->all();

            $this->ubahDatabasePengaturan($uuid, $data[0]);

            $this->hapusCacheAtur();

            $pesan = $this->statusBerhasil();
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

            return $perujuk ? $redirect->to($perujuk)->with('pesan', $pesan) : $redirect->route('atur.data')->with('pesan', $pesan);
        }

        $data = [
            'atur' => $atur,
        ];

        return $tanggapan->make(implode('', $halaman->make('pengaturan.tambah-ubah', $data)->renderSections()))->withHeaders(['Vary' => 'Accept']);
    }

    public function unggah()
    {
        extract($this->obyekPermintaanUmum());

        abort_unless($pengguna && str()->contains($pengguna->sdm_hak_akses, ['PENGURUS']), 403, 'Akses dibatasi hanya untuk Pengurus Aplikasi.');

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        if ($reqs->isMethod('post')) {

            $validasifile = $this->validasiBerkasImporDataPengaturan($reqs->all());

            $validasifile->validate();

            $file = $validasifile->safe()->only('atur_unggah')['atur_unggah'];

            $namafile = 'unggahpengaturan-' . date('YmdHis') . '.xlsx';

            $this->simpanBerkasImporExcelSementara($file, $namafile);

            $fileexcel = $this->ambilBerkasImporExcelSementara($namafile);

            return $this->imporExcelDataPengaturan($fileexcel);
        };

        return $app->make('Illuminate\Contracts\Routing\ResponseFactory')->make(implode('', $app->view->make('pengaturan.unggah')->renderSections()))->withHeaders(['Vary' => 'Accept']);
    }
}
