<?php

namespace App\Interaksi;

use App\Tambahan\CustomValueBinder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ExcelReader;

trait Excel
{
    use EksporExcel, ImporExcel;

    public function eksporExcelDatabasePengaturan($data)
    {
        $spreadsheet = new Spreadsheet();
        $binder = new CustomValueBinder();
        $worksheet = $spreadsheet->getActiveSheet();

        $argumen = [
            'namaBerkas' => 'eksporpengaturan-',
            'dataEkspor' => $data,
            'pengecualian' => ['atur_uuid'],
            'pesanData' =>  ' data pengaturan aplikasi',
            'binder' => $binder,
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet
        ];

        return $this->eksporExcelStream(...$argumen);
    }

    public function eksporExcelContohUnggahPengaturan($data)
    {
        extract(Rangka::obyekPermintaanRangka());

        $reader = new ExcelReader();
        $spreadsheet = $reader->load($app->storagePath('app/contoh/unggah-umum.xlsx'));
        $binder = new CustomValueBinder();
        $worksheet = $spreadsheet->getSheet(1);

        $argumen = [
            'namaBerkas' => 'unggahpengaturan-',
            'dataEkspor' => $data,
            'pengecualian' => ['atur_uuid'],
            'pesanData' =>  ' data pengaturan aplikasi',
            'binder' => $binder,
            'spreadsheet' => $spreadsheet,
            'worksheet' => $worksheet
        ];

        return $this->eksporExcelStream(...$argumen);
    }

    public function imporExcelDataPengaturan($fileexcel)
    {
        extract(Rangka::obyekPermintaanRangka());

        $reader = new ExcelReader();

        $argumen = [
            'reader' => $reader,
            'fileexcel' => $fileexcel
        ];

        return $this->imporExcelStream(...$argumen);
    }
}
