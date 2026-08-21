<?php

namespace App\Strategies\FileProcessors;

use App\Strategies\FileProcessorInterface;
use Illuminate\Http\UploadedFile;

class ImageProcessor implements FileProcessorInterface
{
    public function supports(string $extension): bool
    {
        return in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
    }

    public function process(UploadedFile $file): array
    {
        $dimensions = @getimagesize($file->getRealPath());

        return [
            'file_type' => 'image',
            'width' => $dimensions[0] ?? null,
            'height' => $dimensions[1] ?? null,
            'aspect_ratio' => ($dimensions && $dimensions[1] > 0) ? round($dimensions[0] / $dimensions[1], 2) : null,
            'mime_type' => $file->getClientMimeType(),
        ];
    }

    public function getContentsTree(string $filePath): array
    {
        return [];
    }
}