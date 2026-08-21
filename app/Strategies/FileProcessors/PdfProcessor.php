<?php

namespace App\Strategies\FileProcessors;

use App\Strategies\FileProcessorInterface;
use Illuminate\Http\UploadedFile;

class PdfProcessor implements FileProcessorInterface
{
    public function supports(string $extension): bool
    {
        return strtolower($extension) === 'pdf';
    }

    public function process(UploadedFile $file): array
    {
        return [
            'file_type' => 'pdf',
            'mime_type' => 'application/pdf',
        ];
    }

    public function getContentsTree(string $filePath): array
    {
        return [];
    }
}