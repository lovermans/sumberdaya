<?php

namespace App\Interaksi;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as ExcelWriter;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;

trait EksporExcel
{
    public function eksporExcelStream(
        $namaBerkas,
        $dataEkspor,
        $pengecualian,
        $pesanData,
        $binder,
        $spreadsheet,
        $worksheet,
        $chunk = 100,
        $tabelStart = null,
        $namaTabel = null,
        $spreadsheet2 = null
    ) {
        extract(Rangka::obyekPermintaanRangka());

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');

        $filename = $namaBerkas . date('YmdHis') . '.xlsx';
        Cell::setValueBinder($binder);
        $x = 1;

        $dataEkspor->chunk($chunk, function ($hasil) use (&$x, $worksheet, $pengecualian, $pesanData) {
            if ($x == 1) {
                $list = $hasil->map(function ($x) use ($pengecualian) {
                    return collect($x)->except($pengecualian);
                })->toArray();
                array_unshift($list, array_keys($list[0]));
                $worksheet->fromArray($list, NULL, 'A' . $x);
                $x++;
            } else {
                $list = $hasil->map(function ($x) use ($pengecualian) {
                    return collect($x)->except($pengecualian);
                })->toArray();
                $worksheet->fromArray($list, NULL, 'A' . $x);
            };
            $x += count($hasil);
            echo '<p>Status : Memproses ' . ($x - 2) . $pesanData . '.</p>';
        });

        if ($tabelStart && $namaTabel && $spreadsheet2) {
            echo '<p>Status : Menyiapkan tabel excel.</p>';

            $tabel = new Table($tabelStart . $worksheet->getHighestColumn() . $worksheet->getHighestRow(), $namaTabel);

            $spreadsheet->getActiveSheet()->addTable($tabel);

            echo '<p>Status : Menyiapkan sheet Perhitungan.</p>';

            $clonedWorksheet = clone $spreadsheet2->getSheetByName('Sum');
            $spreadsheet->addExternalSheet($clonedWorksheet);
        }

        $writer = new ExcelWriter($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->save($app->storagePath("app/unduh/{$filename}"));
        $spreadsheet->disconnectWorksheets();

        unset($spreadsheet);

        echo '<p>Status : Menyiapkan berkas excel.</p>';

        return $app->redirect->to($app->filesystem->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)));

        exit();
    }
}
