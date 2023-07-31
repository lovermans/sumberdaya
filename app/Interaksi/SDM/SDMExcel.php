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
        $argumen = [
            'reader' => new ExcelReader(),
            'fileexcel' => $fileexcel,
            'validasiImpor' => 'validasiImporDataSDM',
            'databaseImpor' => 'imporDatabaseSDM',
            'cacheImpor' => 'hapusCacheSDMUmum',
            'rute' => 'sdm.mulai',
            'kolomPengunggah' => 'sdm_id_pengunggah',
            'waktuUnggah' => 'sdm_diunggah'
        ];

        return SDMImporExcel::imporExcelStream(...$argumen);
    }

    public static function eksporExcelContohUnggahSDM($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $reader = new ExcelReader();
        $spreadsheet = $reader->load($app->storagePath('app/contoh/unggah-umum.xlsx'));
        $binder = new StringValueBinder();
        $worksheet = $spreadsheet->getSheet(1);

        $argumen = [
            'namaBerkas' => 'unggahprofilsdm-',
            'dataEkspor' => $data,
            'pengecualian' => ['penempatan_lokasi'],
            'pesanData' =>  ' data profil SDM',
            'binder' => $binder,
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }

    public static function eksporExcelPencarianPosisiSDM($data)
    {
        $spreadsheet = new Spreadsheet();
        $binder = new CustomValueBinder();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporjabatansdm-',
            'dataEkspor' => $data,
            'pengecualian' => ['posisi_uuid'],
            'pesanData' =>  ' data jabatan SDM',
            'binder' => $binder,
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }
}
