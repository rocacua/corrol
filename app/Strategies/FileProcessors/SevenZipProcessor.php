<?php

namespace App\Strategies\FileProcessors;

use App\Strategies\FileProcessorInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Process;

class SevenZipProcessor implements FileProcessorInterface
{
    public function supports(string $extension): bool
    {
        return strtolower($extension) === '7z';
    }

    public function process(UploadedFile $file): array
    {
        $fileTree = $this->getContentsTree($file->getRealPath());

        return [
            'file_type'      => 'zip',
            'file_count'     => count($fileTree),
            'structure'      => $fileTree,
            'archive_format' => '7z',
        ];
    }

    public function getContentsTree(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $command = sprintf('7z l -slt %s 2>/dev/null', escapeshellarg($filePath));
        $result = Process::run($command);

        if (!$result->successful()) {
            return [];
        }

        $tree = [];
        $blocks = explode("\n\n", str_replace("\r\n", "\n", $result->output()));

        foreach ($blocks as $block) {
            if (!str_contains($block, 'Path = ') || str_contains($block, 'Listing archive:')) {
                continue;
            }

            $lines = explode("\n", trim($block));
            $data = [];
            foreach ($lines as $line) {
                if (str_contains($line, ' = ')) {
                    [$key, $value] = explode(' = ', $line, 2);
                    $data[trim($key)] = trim($value);
                }
            }

            if (
                empty($data['Path']) ||
                basename($data['Path']) === basename($filePath)
            ) {
                continue;
            }

            if (!empty($data['Path'])) {
                // $isDir = ($data['Folder'] ?? '0') === '+' || ($data['Attributes'] ?? '') === 'D';
                // $tree[] = [
                //     'name'   => basename($data['Path']),
                //     'path'   => $data['Path'],
                //     'is_dir' => $isDir,
                //     'size'   => $isDir ? 0 : (int) ($data['Size'] ?? 0),
                // ];
                $folder = trim($data['Folder'] ?? '');
                $attributes = strtoupper(trim($data['Attributes'] ?? ''));
                $path = trim($data['Path']);

                $isDir = $folder === '+'
                    || str_starts_with($attributes, 'D')
                    || str_ends_with($path, '/');
                
                $tree[] = [
                    'name'   => basename(rtrim($path, '/')),
                    'path'   => $path,
                    'is_dir' => $isDir,
                    'size'   => $isDir ? 0 : (int) ($data['Size'] ?? 0),
                ];
            }
        }

        usort($tree, static function (array $left, array $right): int {
            return strnatcasecmp($left['path'], $right['path']);
        });
        return $tree;
    }
}