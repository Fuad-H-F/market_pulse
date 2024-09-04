<?php

namespace App\Utils;

use Illuminate\Support\Facades\File;

class PHPExportable
{
    /**
     * Summary of exportFromData
     * @param mixed $data
     * @param string $fileName
     * @return mixed
     */
    public function exportFromData($data = [], string $fileName = 'default', $header = [])
    {
        $dir = public_path('csv/');
        !is_dir($dir) && mkdir(
            $dir,
            0777,
            false
        );

        $files = File::glob(public_path('csv/*.csv'));
        if (count($files)) {
            foreach ($files as $file) {
                $file && unlink($file);
            }
        }

        $file_name = time() . '_' . $fileName . '.csv';
        $output    = fopen($dir . $file_name, 'w');
        $data      = json_decode(json_encode($data), true);
        $bom       = chr(0xEF) . chr(0xBB) . chr(0xBF);

        fputs($output, $bom);
        fputcsv($output, $header);

        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        fclose($output);

        return $file_name;
    }
}
