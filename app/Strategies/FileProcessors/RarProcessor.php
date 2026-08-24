<?php

namespace App\Strategies\FileProcessors;

use App\Strategies\FileProcessorInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Process;
use RarArchive;

class RarProcessor implements FileProcessorInterface
{
    public function supports(string $extension): bool
    {
        return strtolower($extension) === 'rar';
    }

    public function process(UploadedFile $file): array
    {
        $fileTree = $this->getContentsTree($file->getRealPath());

        return [
            'file_type'      => 'zip',
            'file_count'     => count($fileTree),
            'structure'      => $fileTree,
            'archive_format' => 'rar',
        ];
    }

    public function getContentsTree(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        // 1. Intentar mediante la extensión nativa de PHP (si está instalada)
        if (class_exists('RarArchive')) {
            $rar = @RarArchive::open($filePath);
            if ($rar !== false) {
                $tree = [];
                $entries = @$rar->getEntries();
                if ($entries) {
                    foreach ($entries as $entry) {
                        $tree[] = [
                            'name'   => basename($entry->getName()),
                            'path'   => $entry->getName(),
                            'is_dir' => $entry->isDirectory(),
                            'size'   => $entry->isDirectory() ? 0 : $entry->getUnpackedSize(),
                        ];
                    }
                }
                $rar->close();
                return $tree;
            }
        }

        // 2. Fallback CLI: Usar el comando 7z (p7zip)
        return $this->getTreeWith7Zip($filePath);
    }

    protected function getTreeWith7Zip(string $filePath): array
    {
        $command = sprintf('7z l -slt %s 2>/dev/null', escapeshellarg($filePath));
        $result = Process::run($command);

        if (!$result->successful()) {
            return [];
        }

        return $this->parse7zOutput($result->output(), $filePath);
    }

    protected function parse7zOutput(string $output, string $filePath): array
    {
        $tree = [];
        $blocks = explode("\n\n", str_replace("\r\n", "\n", $output));

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
                $isDir = ($data['Folder'] ?? '0') === '+' || ($data['Attributes'] ?? '') === 'D';
                $tree[] = [
                    'name'   => basename($data['Path']),
                    'path'   => $data['Path'],
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