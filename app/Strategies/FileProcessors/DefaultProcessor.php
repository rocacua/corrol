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
        $ext = strtolower($file->getClientOriginalExtension());
        $type = 'document';

        if (in_array($ext, $this->audioExtensions)) {
            $type = 'audio';
        } elseif (in_array($ext, $this->videoExtensions)) {
            $type = 'video';
        } elseif (in_array($ext, $this->docExtensions)) {
            $type = 'document';
        }

        return [
            'file_type' => $type,
            'mime_type' => $file->getClientMimeType(),
        ];
    }
}