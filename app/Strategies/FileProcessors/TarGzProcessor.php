<?php

namespace App\Strategies\FileProcessors;

use App\Strategies\FileProcessorInterface;
use Illuminate\Http\UploadedFile;
use PharData;
use Exception;
use RecursiveIteratorIterator;

class TarGzProcessor implements FileProcessorInterface
{
    public function supports(string $extension): bool
    {
        return in_array(strtolower($extension), ['tar.gz', 'tgz', 'gz', 'tar']);
    }

    public function process(UploadedFile $file): array
    {
        $fileTree = $this->getContentsTree($file->getRealPath());

        return [
            'file_type' => 'zip',
            'file_count' => count($fileTree),
            'structure' => $fileTree,
            'archive_format' => 'tar.gz',
        ];
    }

    public function getContentsTree(string $filePath): array
    {
        if (!class_exists('PharData') || !file_exists($filePath)) {
            return [];
        }

        $tree = [];

        try {
            $phar = new PharData($filePath);
            $realPath = realpath($filePath) ?: $filePath;
            $pharPrefix = 'phar://' . str_replace('\\', '/', $realPath) . '/';

            // SELF_FIRST garantiza incluir directorios e iterar en orden jerárquico
            $iterator = new RecursiveIteratorIterator($phar, RecursiveIteratorIterator::SELF_FIRST);

            /** @var \PharFileInfo $file */
            foreach ($iterator as $file) {
                $fullPath = str_replace('\\', '/', $file->getPathname());
                $relativePath = str_replace($pharPrefix, '', $fullPath);

                $tree[] = [
                    'name'   => $file->getFilename(),
                    'path'   => rtrim($relativePath, '/'),
                    'is_dir' => $file->isDir(),
                    'size'   => $file->isDir() ? 0 : $file->getSize(),
                ];
            }
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error procesando TAR.GZ: " . $e->getMessage());
            return [];
        }

        return $tree;
    }
}