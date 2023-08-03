<?php

namespace App\Interaksi\SDM;

use App\Interaksi\Rangka;
use App\Tambahan\ChunkReadFilter;

class SDMImporExcel
{
    public static function imporExcelStream(
        $reader,
        $fileexcel,
        $validasiImpor,
        $databaseImpor,
        $cacheImpor,
        $rute,
        $kolomPengunggah,
        $waktuUnggah,
        $chunkSize = 25
    ) {
        extract(Rangka::obyekPermintaanRangka(true));

        abort_unless($reqs->pjax() && $pengguna, 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');

        $spreadsheetInfo = $reader->listWorksheetInfo($fileexcel);
        $chunkFilter = new ChunkReadFilter();
        $reader->setReadFilter($chunkFilter);
        $reader->setReadDataOnly(true);
        $totalRows = $spreadsheetInfo[1]['totalRows'];
        $idPengunggah = $pengguna?->sdm_no_absen;

        for ($startRow = 2; $startRow <= $totalRows; $startRow += $chunkSize) {
            $chunkFilter->setRows($startRow, $chunkSize);
            $spreadsheet = $reader->load($fileexcel);
            $worksheet = $spreadsheet->getSheet(1);
            $barisTertinggi = $worksheet->getHighestRow();
            $kolomTertinggi = $worksheet->getHighestColumn();

            $pesanbaca = '<p>Status : Membaca excel baris ' . ($startRow) . ' sampai baris ' . $barisTertinggi . '.</p>';
            $pesansimpan = '<p>Status : Berhasil menyimpan data excel baris ' . ($startRow) . ' sampai baris ' . $barisTertinggi . '.</p>';

            echo $pesanbaca;

            $headingArray = $worksheet->rangeToArray('A1:' . $kolomTertinggi . '1', NULL, FALSE, TRUE, FALSE);
            $dataArray = $worksheet->rangeToArray('A' . $startRow . ':' . $kolomTertinggi . $barisTertinggi, NULL, FALSE, TRUE, FALSE);

            $tabel = array_merge($headingArray, $dataArray);
            $isitabel = array_shift($tabel);

            $datas = array_map(function ($x) use ($isitabel) {
                return array_combine($isitabel, $x);
            }, $tabel);

            $dataexcel = array_map(function ($x) use ($idPengunggah, $kolomPengunggah, $waktuUnggah) {
                return $x
                    + [$kolomPengunggah => $idPengunggah]
                    + [$waktuUnggah => date('Y-m-d H:i:s')];
            }, $datas);

            $data = array_combine(range(($startRow - 1), count($dataexcel) + ($startRow - 2)), array_values($dataexcel));

            $validasi = SDMValidasi::$validasiImpor($data);

            $validasi->validate();

            SDMDBQuery::$databaseImpor($validasi->validated());

            SDMCache::$cacheImpor();

            echo $pesansimpan;
        }

        $spreadsheet->disconnectWorksheets();

        unset($spreadsheet);

        $app->filesystem->delete($fileexcel);

        return $app->redirect->route($rute)->with('pesan', Rangka::statusBerhasil());

        exit();
    }
}
