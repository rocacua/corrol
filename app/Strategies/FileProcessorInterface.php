<?php

namespace App\Strategies;

use Illuminate\Http\UploadedFile;

interface FileProcessorInterface
{
    /**
     * Comprueba si la estrategia soporta el tipo de archivo según su extensión.
     */
    public function supports(string $extension): bool;

    /**
     * Procesa el archivo y devuelve un array con sus metadatos específicos.
     */
    public function process(UploadedFile $file): array;
}
