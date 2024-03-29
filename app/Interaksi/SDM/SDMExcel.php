<?php

namespace App\Interaksi\SDM;

use App\Interaksi\EksporExcel;
use App\Interaksi\Rangka;
use App\Pendukung\Office\CustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ExcelReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SDMExcel
{
    public static function imporExcelDataSDM($fileexcel)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        $argumen = [
            'reader' => new ExcelReader(),
            'fileexcel' => $fileexcel,
            'validasiImpor' => 'validasiImporDataSDM',
            'databaseImpor' => 'imporDatabaseSDM',
            'cacheImpor' => 'hapusCacheSDMUmum',
            'rute' => 'sdm.mulai',
            'kolomPengunggah' => 'sdm_id_pengunggah',
            'waktuUnggah' => 'sdm_diunggah',
            'pesanSoket' => $pengguna?->sdm_nama.' telah mengimpor data SDM pada '.strtoupper($app->date->now()->translatedFormat('d F Y H:i:s')),
        ];

        return SDMImporExcel::imporExcelStream(...$argumen);
    }

    public static function imporExcelDataPosisiSDM($fileexcel)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        $argumen = [
            'reader' => new ExcelReader(),
            'fileexcel' => $fileexcel,
            'validasiImpor' => 'validasiImporDataPosisiSDM',
            'databaseImpor' => 'imporPosisiSDM',
            'cacheImpor' => 'hapusCacheSDMUmum',
            'rute' => 'sdm.posisi.data',
            'kolomPengunggah' => 'posisi_id_pengunggah',
            'waktuUnggah' => 'posisi_diunggah',
            'pesanSoket' => $pengguna?->sdm_nama.' telah mengimpor data pengaturan Jabatan SDM pada '.strtoupper($app->date->now()->translatedFormat('d F Y H:i:s')),
        ];

        return SDMImporExcel::imporExcelStream(...$argumen);
    }

    public static function imporExcelDataSanksiSDM($fileexcel)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        $argumen = [
            'reader' => new ExcelReader(),
            'fileexcel' => $fileexcel,
            'validasiImpor' => 'validasiImporDataSanksiSDM',
            'databaseImpor' => 'imporSanksiSDM',
            'cacheImpor' => 'hapusCachePelanggaran_SanksiSDM',
            'rute' => 'sdm.sanksi.data',
            'kolomPengunggah' => 'sanksi_id_pengunggah',
            'waktuUnggah' => 'sanksi_diunggah',
            'pesanSoket' => $pengguna?->sdm_nama.' telah mengimpor data Sanksi SDM pada '.strtoupper($app->date->now()->translatedFormat('d F Y H:i:s')),
        ];

        return SDMImporExcel::imporExcelStream(...$argumen);
    }

    public static function imporExcelDataNilaiSDM($fileexcel)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        $argumen = [
            'reader' => new ExcelReader(),
            'fileexcel' => $fileexcel,
            'validasiImpor' => 'validasiImporDataNilaiSDM',
            'databaseImpor' => 'imporNilaiSDM',
            'cacheImpor' => 'hapusCacheNilaiSDM',
            'rute' => 'sdm.penilaian.data',
            'kolomPengunggah' => 'nilaisdm_id_pengunggah',
            'waktuUnggah' => 'nilaisdm_diunggah',
            'pesanSoket' => $pengguna?->sdm_nama.' telah mengimpor data Penilaian SDM pada '.strtoupper($app->date->now()->translatedFormat('d F Y H:i:s')),
        ];

        return SDMImporExcel::imporExcelStream(...$argumen);
    }

    public static function imporExcelPenempatanSDM($fileexcel)
    {
        extract(Rangka::obyekPermintaanRangka(true));

        $argumen = [
            'reader' => new ExcelReader(),
            'fileexcel' => $fileexcel,
            'validasiImpor' => 'validasiImporPenempatanSDM',
            'databaseImpor' => 'imporPenempatanSDM',
            'cacheImpor' => 'hapusCacheSDMUmum',
            'rute' => 'sdm.penempatan.data-aktif',
            'kolomPengunggah' => 'penempatan_id_pengunggah',
            'waktuUnggah' => 'penempatan_diunggah',
            'pesanSoket' => $pengguna?->sdm_nama.' telah mengimpor data Penempatan SDM pada '.strtoupper($app->date->now()->translatedFormat('d F Y H:i:s')),
        ];

        return SDMImporExcel::imporExcelStream(...$argumen);
    }

    public static function eksporExcelContohUnggahSDM($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $reader = new ExcelReader();
        $spreadsheet = $reader->load($app->storagePath('app/contoh/unggah-umum.xlsx'));
        $worksheet = $spreadsheet->getSheet(1);

        $argumen = [
            'namaBerkas' => 'unggahprofilsdm-',
            'dataEkspor' => $data,
            'pengecualian' => ['penempatan_lokasi'],
            'pesanData' => ' data profil SDM',
            'binder' => new StringValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }

    public static function eksporExcelPencarianPosisiSDM($data)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporjabatansdm-',
            'dataEkspor' => $data,
            'pengecualian' => ['posisi_uuid'],
            'pesanData' => ' data jabatan SDM',
            'binder' => new CustomValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }

    public static function eksporExcelPencarianPermintaanTambahSDM($data)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporpermintaansdm-',
            'dataEkspor' => $data,
            'pengecualian' => ['sdm_uuid', 'tambahsdm_uuid'],
            'pesanData' => ' data permintaan tambah SDM',
            'binder' => new CustomValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }

    public static function eksporExcelContohUnggahPosisiSDM($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $reader = new ExcelReader();
        $spreadsheet = $reader->load($app->storagePath('app/contoh/unggah-umum.xlsx'));
        $worksheet = $spreadsheet->getSheet(1);

        $argumen = [
            'namaBerkas' => 'unggahjabatansdm-',
            'dataEkspor' => $data,
            'pengecualian' => ['posisi_uuid'],
            'pesanData' => ' data jabatan SDM',
            'binder' => new CustomValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }

    public static function eksporExcelPencarianLapPelanggaranSDM($data)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporpelanggaransdm-',
            'dataEkspor' => $data,
            'pengecualian' => [
                'langgar_uuid',
                'langgar_tsdm_uuid',
                'langgar_psdm_uuid',
                'final_sanksi_uuid',
            ],
            'pesanData' => ' data laporan pelanggaran SDM',
            'binder' => new CustomValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }

    public static function eksporExcelPencarianSanksiSDM($data)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporsanksisdm-',
            'dataEkspor' => $data,
            'pengecualian' => [
                'sanksi_uuid',
                'langgar_tsdm_uuid',
                'langgar_psdm_uuid',
            ],
            'pesanData' => ' data sanksi SDM',
            'binder' => new CustomValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 100,
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }

    public static function eksporExcelContohUnggahSanksiSDM($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $reader = new ExcelReader();
        $spreadsheet = $reader->load($app->storagePath('app/contoh/unggah-umum.xlsx'));
        $worksheet = $spreadsheet->getSheet(1);

        $argumen = [
            'namaBerkas' => 'unggahsanksisdm-',
            'dataEkspor' => $data,
            'pengecualian' => ['id', 'sanksi_lap_no', 'sanksi_uuid'],
            'pesanData' => ' data sanksi SDM',
            'binder' => new StringValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 100,
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }

    public static function eksporExcelPencarianNilaiSDM($data)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporpenilaiansdm-',
            'dataEkspor' => $data,
            'pengecualian' => ['nilaisdm_uuid', 'sdm_uuid'],
            'pesanData' => ' data penilaian SDM',
            'binder' => new CustomValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 100,
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }

    public static function eksporExcelSemuRiwayatPenempatanSDM($data)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporriwayatpenempatan-',
            'dataEkspor' => $data,
            'pengecualian' => ['id', 'sdm_uuid', 'penempatan_uuid'],
            'pesanData' => ' data riwayat penempatan SDM',
            'binder' => new CustomValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 500,
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }

    public static function eksporExcelMasaKerjaNyataSDM($data)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'ekspormasakerjanyata-',
            'dataEkspor' => $data,
            'pengecualian' => ['id', 'sdm_uuid', 'penempatan_uuid'],
            'pesanData' => ' data masa kerja nyata SDM',
            'binder' => new CustomValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 500,
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }

    public static function eksporExcelPenempatanAktifSDM($data)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporsdmaktif-',
            'dataEkspor' => $data,
            'pengecualian' => ['id', 'sdm_uuid', 'penempatan_uuid'],
            'pesanData' => ' data penempatan aktif SDM',
            'binder' => new CustomValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 500,
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }

    public static function eksporExcelPenempatanNonAktifSDM($data)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporsdmnonaktif-',
            'dataEkspor' => $data,
            'pengecualian' => ['id', 'sdm_uuid', 'penempatan_uuid'],
            'pesanData' => ' data penempatan SDM non-aktif',
            'binder' => new CustomValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 500,
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }

    public static function eksporExcelPenempatanSDMAkanHabis($data)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporsdmakanhabis-',
            'dataEkspor' => $data,
            'pengecualian' => ['id', 'sdm_uuid', 'penempatan_uuid'],
            'pesanData' => ' data PKWT SDM akan habis',
            'binder' => new CustomValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 500,
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }

    public static function eksporExcelPenempatanSDMKadaluarsa($data)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporsdmkadaluarsa-',
            'dataEkspor' => $data,
            'pengecualian' => ['id', 'sdm_uuid', 'penempatan_uuid'],
            'pesanData' => ' data PKWT SDM kadaluarsa',
            'binder' => new CustomValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 500,
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }

    public static function eksporExcelSDMAktifBelumDitempatkan($data)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporsdmbaru-',
            'dataEkspor' => $data,
            'pengecualian' => ['id', 'sdm_uuid', 'penempatan_uuid'],
            'pesanData' => ' data SDM belum ditempatkan',
            'binder' => new CustomValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 500,
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }

    public static function eksporExcelSDMBatalBergabung($data)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporsdmbatal-',
            'dataEkspor' => $data,
            'pengecualian' => ['id', 'sdm_uuid', 'penempatan_uuid'],
            'pesanData' => ' data SDM batal bergabung',
            'binder' => new CustomValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 500,
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }

    public static function eksporStatistikSDM($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $reader = new ExcelReader();
        $spreadsheet2 = $reader->load($app->storagePath('app/contoh/statistik-sdm.xlsx'));
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'statistik-sdm-',
            'dataEkspor' => $data,
            'pengecualian' => ['id', 'sdm_uuid', 'penempatan_uuid'],
            'pesanData' => ' data statistik penempatan SDM',
            'binder' => new CustomValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 500,
            'tabelStart' => 'A1:',
            'namaTabel' => 'Penempatan',
            'spreadsheet2' => $spreadsheet2,
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }

    public static function eksporExcelContohUnggahNilaiSDM($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $reader = new ExcelReader();
        $spreadsheet = $reader->load($app->storagePath('app/contoh/unggah-umum.xlsx'));
        $worksheet = $spreadsheet->getSheet(1);

        $argumen = [
            'namaBerkas' => 'unggahnilaisdm-',
            'dataEkspor' => $data,
            'pengecualian' => [
                'id',
                'nilaisdm_uuid',
                'sdm_uuid',
                'sdm_tgl_berhenti',
                'nilaisdm_total',
                'sdm_nama',
                'penempatan_posisi',
                'penempatan_lokasi',
                'penempatan_kontrak',
            ],
            'pesanData' => ' data penilaian SDM',
            'binder' => new CustomValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 100,
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }

    public static function eksporExcelContohUnggahPenempatanSDM($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $reader = new ExcelReader();
        $spreadsheet = $reader->load($app->storagePath('app/contoh/unggah-umum.xlsx'));
        $worksheet = $spreadsheet->getSheet(1);

        $argumen = [
            'namaBerkas' => 'unggahpenempatansdm-',
            'dataEkspor' => $data,
            'pengecualian' => ['id', 'penempatan_uuid'],
            'pesanData' => ' data penempatan SDM',
            'binder' => new StringValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 500,
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }

    public static function eksporExcelPencarianKepuasanSDM($data)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporpenilaiansdm-',
            'dataEkspor' => $data,
            'pengecualian' => ['surveysdm_uuid', 'sdm_uuid'],
            'pesanData' => ' data Kepuasan SDM',
            'binder' => new CustomValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 100,
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }
}
