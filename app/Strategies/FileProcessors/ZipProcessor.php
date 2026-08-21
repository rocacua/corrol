<?php

namespace App\Strategies\FileProcessors;

use App\Strategies\FileProcessorInterface;
use Illuminate\Http\UploadedFile;
use ZipArchive;

class ZipProcessor implements FileProcessorInterface
{
    public function supports(string $extension): bool
    {
        return in_array(strtolower($extension), ['zip']);
    }

    public function process(UploadedFile $file): array
    {
        $zip = new ZipArchive();
        $fileTree = [];

        if ($zip->open($file->getRealPath()) === true) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if ($stat) {
                    $fileTree[] = [
                        'name' => $stat['name'],
                        'size' => $stat['size'],
                        'is_dir' => str_ends_with($stat['name'], '/'),
                    ];
                }
            }
            $zip->close();
        }

        return [
            'file_type' => 'zip',
            'file_count' => count($fileTree),
            'structure' => $fileTree,
        ];
    }
}