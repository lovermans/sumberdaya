<?php

namespace App\Interaksi;

use App\Tambahan\ChunkReadFilter;

trait ImporExcel
{
    use Umum, Cache, Validasi, DBQuery;

    public function imporExcelStream($reader, $fileexcel)
    {
        extract(Rangka::obyekPermintaanRangka());

        abort_unless($reqs->pjax(), 404, 'Alamat hanya bisa dimuat dalam aktivitas aplikasi.');

        set_time_limit(0);
        ob_implicit_flush();
        ob_end_flush();
        header('X-Accel-Buffering: no');

        $spreadsheetInfo = $reader->listWorksheetInfo($fileexcel);
        $chunkSize = 25;
        $chunkFilter = new ChunkReadFilter();
        $reader->setReadFilter($chunkFilter);
        $reader->setReadDataOnly(true);
        $totalRows = $spreadsheetInfo[1]['totalRows'];
        $idPengunggah = $pengguna->sdm_no_absen;

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

            $dataexcel = array_map(function ($x) use ($idPengunggah) {
                return $x
                    + ['atur_id_pengunggah' => $idPengunggah]
                    + ['atur_id_pembuat' => $idPengunggah]
                    + ['atur_id_pengubah' => $idPengunggah]
                    + ['atur_diunggah' => date('Y-m-d H:i:s')];
            }, $datas);

            $data = array_combine(range(($startRow - 1), count($dataexcel) + ($startRow - 2)), array_values($dataexcel));

            $validasi = $this->validasiImporDataPengaturan($data);

            $validasi->validate();

            $this->imporDatabasePengaturan($validasi->validated());

            echo $pesansimpan;
        }

        $spreadsheet->disconnectWorksheets();

        unset($spreadsheet);

        $app->filesystem->delete($fileexcel);

        $this->hapusCacheAtur();

        return $app->redirect->route('atur.data')->with('pesan', $this->statusBerhasil());

        exit();
    }
}
