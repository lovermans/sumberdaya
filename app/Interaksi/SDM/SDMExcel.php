<?php

namespace App\Interaksi\SDM;

use App\Interaksi\Rangka;
use App\Interaksi\EksporExcel;
use App\Tambahan\CustomValueBinder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ExcelReader;

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
            'pesanSoket' => $pengguna?->sdm_nama . ' telah mengimpor data SDM pada ' . strtoupper($app->date->now()->translatedFormat('d F Y H:i:s'))
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
            'pesanSoket' => $pengguna?->sdm_nama . ' telah mengimpor data pengaturan Jabatan SDM pada ' . strtoupper($app->date->now()->translatedFormat('d F Y H:i:s'))
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
            'pesanSoket' => $pengguna?->sdm_nama . ' telah mengimpor data Sanksi SDM pada ' . strtoupper($app->date->now()->translatedFormat('d F Y H:i:s'))
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
            'pesanSoket' => $pengguna?->sdm_nama . ' telah mengimpor data Penilaian SDM pada ' . strtoupper($app->date->now()->translatedFormat('d F Y H:i:s'))
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
            'worksheet' => $worksheet
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
            'worksheet' => $worksheet
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
            'worksheet' => $worksheet
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
            'worksheet' => $worksheet
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
                'final_sanksi_uuid'
            ],
            'pesanData' => ' data laporan pelanggaran SDM',
            'binder' => new CustomValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet
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
                'langgar_psdm_uuid'
            ],
            'pesanData' => ' data sanksi SDM',
            'binder' => new CustomValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 100
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
            'chunk' => 100
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
            'chunk' => 100
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
            'chunk' => 500
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
                'penempatan_kontrak'
            ],
            'pesanData' => ' data penilaian SDM',
            'binder' => new CustomValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet,
            'chunk' => 100
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }
}
