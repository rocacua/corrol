<?php

namespace App\Strategies\FileProcessors;

use App\Strategies\FileProcessorInterface;
use Illuminate\Http\UploadedFile;

class DefaultProcessor implements FileProcessorInterface
{
    protected array $audioExtensions = ['mp3', 'wav', 'ogg', 'flac'];
    protected array $videoExtensions = ['mp4', 'webm', 'ogv', 'avi', 'mkv'];
    protected array $docExtensions = ['docx', 'xlsx', 'odt', 'ods', 'html', 'txt'];

    public function supports(string $extension): bool
    {
        return true; // Captura cualquier archivo no procesado previamente
    }

    
    public function process(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, $this->videoExtensions, true)) {
            return [
                'file_type' => 'video',
                'mime_type' => match ($extension) {
                    'ogv' => 'video/ogg',
                    'webm' => 'video/webm',
                    'mp4' => 'video/mp4',
                    default => $file->getMimeType() ?: 'application/octet-stream',
                },
            ];
        }

        if (in_array($extension, $this->audioExtensions, true)) {
            return [
                'file_type' => 'audio',
                'mime_type' => match ($extension) {
                    'ogg' => 'audio/ogg',
                    'mp3' => 'audio/mpeg',
                    'wav' => 'audio/wav',
                    default => $file->getMimeType() ?: 'application/octet-stream',
                },
            ];
        }

        return [
            'file_type' => 'document',
            'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
        ];
    }

    public function getContentsTree(string $filePath): array
    {
        return [];
    }
}