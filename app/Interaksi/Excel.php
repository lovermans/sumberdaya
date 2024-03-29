<?php

namespace App\Interaksi;

use App\Pendukung\Office\CustomValueBinder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ExcelReader;

class Excel
{
    public static function eksporExcelDatabasePengaturan($data)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporpengaturan-',
            'dataEkspor' => $data,
            'pengecualian' => ['atur_uuid'],
            'pesanData' =>  ' data pengaturan aplikasi',
            'binder' => new CustomValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }

    public static function eksporExcelContohUnggahPengaturan($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $reader = new ExcelReader();
        $spreadsheet = $reader->load($app->storagePath('app/contoh/unggah-umum.xlsx'));
        $worksheet = $spreadsheet->getSheet(1);

        $argumen = [
            'namaBerkas' => 'unggahpengaturan-',
            'dataEkspor' => $data,
            'pengecualian' => ['atur_uuid'],
            'pesanData' =>  ' data pengaturan aplikasi',
            'binder' => new CustomValueBinder(),
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet
        ];

        return EksporExcel::eksporExcelStream(...$argumen);
    }

    public static function imporExcelDataPengaturan($fileexcel)
    {
        $argumen = [
            'reader' => new ExcelReader(),
            'fileexcel' => $fileexcel,
            'validasiImpor' => 'validasiImporDataPengaturan',
            'databaseImpor' => 'imporDatabasePengaturan',
            'cacheImpor' => 'hapusCacheAtur',
            'rute' => 'atur.data',
            'kolomPengunggah' => 'atur_id_pengunggah',
            'waktuUnggah' => 'atur_diunggah'
        ];

        return ImporExcel::imporExcelStream(...$argumen);
    }

    public static function cadangkanPenghapusanDatabase($dataHapus)
    {
        extract(Rangka::obyekPermintaanRangka());

        $fileexcel = $app->storagePath('app/contoh/data-dihapus.xlsx');

        $reader = new ExcelReader();
        $spreadsheet = $reader->load($fileexcel);
        $binder = new CustomValueBinder();

        EksporExcel::simpanRiwayatHapusDatabase($spreadsheet, $dataHapus, $fileexcel, $binder);
    }
}
