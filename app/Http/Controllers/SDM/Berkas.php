<?php

namespace App\Http\Controllers\SDM;

use App\Tambahan\CustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ExcelReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as ExcelWriter;

class Berkas
{
    public function eksporExcelStream($namaBerkas, $dataEkspor, $pengecualian, $pesanData, $app, $binder, $spreadsheet, $worksheet, $chunk = 100, $tabelStart = null, $namaTabel = null, $spreadsheet2 = null)
    {
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

    public function isiFormulir($app, $contoh, $data, $filename)
    {
        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');

        echo '<p>Memeriksa formulir.</p>';

        $storage = $app->filesystem;

        abort_unless($storage->exists("contoh/{$contoh}"), 404, 'Berkas Contoh Formulir Tidak Ditemukan.');

        $templateProcessor = new TemplateProcessor($app->storagePath("app/contoh/{$contoh}"));

        echo '<p>Menyiapkan formulir.</p>';

        $templateProcessor->setValues($data);

        $templateProcessor->saveAs($app->storagePath("app/unduh/{$filename}"));

        return $app->redirect->to($storage->disk('local')->temporaryUrl("unduh/{$filename}", $app->date->now()->addMinutes(5)));

        exit();
    }

    public function rekamHapusDataSDM($app, $dataHapus)
    {
        $reader = new ExcelReader();
        $spreadsheet = $reader->load($app->storagePath('app/contoh/data-dihapus.xlsx'));
        Cell::setValueBinder(new CustomValueBinder());
        $worksheet = $spreadsheet->getActiveSheet();
        $barisAkhir = $worksheet->getHighestRow() + 1;
        $worksheet->fromArray($dataHapus, NULL, 'A' . $barisAkhir);

        $writer = new ExcelWriter($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->save($app->storagePath('app/contoh/data-dihapus.xlsx'));
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }
}
