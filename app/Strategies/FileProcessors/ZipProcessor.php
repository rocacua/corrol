<?php

namespace App\Strategies\FileProcessors;

use App\Strategies\FileProcessorInterface;
use Illuminate\Http\UploadedFile;
use ZipArchive;

class ZipProcessor implements FileProcessorInterface
{
    protected array $supportedExtensions = ['zip', 'rar', '7z', 'gz', 'tar', 'tgz', 'tar.gz'];

    public function supports(string $extension): bool
    {
        return in_array(strtolower($extension), $this->supportedExtensions);
    }

    public function process(UploadedFile $file): array
    {
        $fileTree = $this->getContentsTree($file->getRealPath());

        return [
            'file_type' => 'zip',
            'file_count' => count($fileTree),
            'structure' => $fileTree,
            'archive_format' => strtolower($file->getClientOriginalExtension()),
        ];
    }

    public function getContentsTree(string $filePath): array
    {
        if (!class_exists('ZipArchive') || !file_exists($filePath)) {
            return [];
        }

        $tree = [];
        $zip = new ZipArchive();

        if ($zip->open($filePath) === true) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (!$stat) {
                    continue;
                }

                $tree[] = [
                    'name'   => basename($stat['name']),
                    'path'   => $stat['name'],
                    'is_dir' => str_ends_with($stat['name'], '/'),
                    'size'   => $stat['size'],
                ];
            }
            $zip->close();
        }

        return $tree;
    }
}