<?php

namespace App\Services;

use App\Strategies\FileProcessorInterface;
use App\Strategies\FileProcessors\ZipProcessor;
use App\Strategies\FileProcessors\TarGzProcessor;
use App\Strategies\FileProcessors\RarProcessor;
use App\Strategies\FileProcessors\SevenZipProcessor;
use InvalidArgumentException;

class FileProcessorFactory
{
    public static function make(string $extension): FileProcessorInterface
    {
        return match (strtolower($extension)) {
            'zip' => app(ZipProcessor::class),
            'tar.gz', 'tgz', 'gz', 'tar' => app(TarGzProcessor::class),
            '7z' => app(SevenZipProcessor::class),
            'rar' => app(RarProcessor::class),
            default => throw new InvalidArgumentException("Formato no soportado: {$extension}"),
        };
    }
}