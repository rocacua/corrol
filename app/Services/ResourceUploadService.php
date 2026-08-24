<?php

namespace App\Services;

use App\Models\Resource;
use App\Models\ResourceFile;
use App\Repositories\Contracts\ResourceRepositoryInterface;
use App\Strategies\FileProcessorInterface;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Services\FileProcessorFactory;
use Illuminate\Support\Facades\Log;

class ResourceUploadService
{
    protected ResourceRepositoryInterface $resourceRepository;
    protected iterable $processors;

    public function __construct(ResourceRepositoryInterface $resourceRepository, iterable $processors)
    {
        $this->resourceRepository = $resourceRepository;
        $this->processors = $processors;
    }

    /**
     * Procesa la subida de un archivo a Backblaze B2 y lo registra en BD.
     */
    public function uploadFile(UploadedFile $file, array $data): Resource
    {
        $extension = $file->getClientOriginalExtension();
        $metadata = [];

        foreach ($this->processors as $processor) {
            if ($processor instanceof FileProcessorInterface && $processor->supports($extension)) {
                $metadata = $processor->process($file);
                break;
            }
        }

        $fileName = uniqid() . '_' . time() . '.' . $extension;
        
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('b2');
        //$mimeType = $file->getMimeType() ?: $file->getClientMimeType();
        $extension = strtolower($file->getClientOriginalExtension());

        $mimeType = match ($extension) {
            'ogv' => 'video/ogg',
            'ogg' => 'audio/ogg',
            'webm' => 'video/webm',
            'mp4' => 'video/mp4',
            default => $file->getMimeType()
                ?: $file->getClientMimeType()
                ?: 'application/octet-stream',
        };
        $metadata['mime_type'] = $mimeType;

        $path = $disk->putFileAs(
            'resources',
            $file,
            $fileName,
            [
                'ContentType' => $mimeType,
            ]
        );

        $data['size_in_bytes'] = $file->getSize();
        $data['is_external'] = false;

        return $this->resourceRepository->createFileResource($data, (string) $path, $metadata);
    }

    /**
     * Registra un recurso que es simplemente una URL externa detectando su tipo.
     */
    public function registerExternalUrl(string $url, array $data): Resource
    {
        $data['is_external'] = true;
        
        $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH) ?? '');
        $ext = strtolower($pathInfo['extension'] ?? '');

        $fileType = 'document';
        if (in_array($ext, ['pdf'])) {
            $fileType = 'pdf';
        } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
            $fileType = 'image';
        } elseif (in_array($ext, ['mp3', 'wav', 'ogg'])) {
            $fileType = 'audio';
        } elseif (in_array($ext, ['mp4', 'webm', 'ogv'])) {
            $fileType = 'video';
        } elseif (in_array($ext, ['zip', 'rar', '7z', 'gz'])) {
            $fileType = 'zip';
        }

        $metadata = ['file_type' => $fileType];

        return $this->resourceRepository->createFileResource($data, $url, $metadata);
    }

    /**
     * Genera la URL de acceso/descarga (firmada si es necesaria).
     */
    public function getFileUrl(string $filePath, int $expirationMinutes = 15): string
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('b2');
        return $disk->temporaryUrl(
            $filePath, 
            now()->addMinutes($expirationMinutes)
        );
    }

    /**
     * Elimina un recurso y su archivo físico en Backblaze B2 si no es un enlace externo.
     */
    public function deleteResource(Resource $resource): bool
    {
        /** @var ResourceFile|null $file */
        $file = $resource->resourceable;

        if ($resource->type === 'file' && $file) {
            if (!$file->is_external && $file->file_path_or_url) {
                /** @var FilesystemAdapter $disk */
                $disk = Storage::disk('b2');
                $disk->delete($file->file_path_or_url);
            }

            $file->delete();
        }

        return (bool) $resource->delete();
    }

    /**
     * Reemplaza el archivo o URL de un recurso existente gestionando el borrado en Backblaze B2.
     */
    public function updateResourceFile(Resource $resource, ?UploadedFile $newFile, ?string $newUrl): void
    {
        /** @var ResourceFile|null $resourceFile */
        $resourceFile = $resource->resourceable;

        if (!$resourceFile) {
            return;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('b2');

        if (!empty($newUrl)) {
            if (!$resourceFile->is_external && $resourceFile->file_path_or_url) {
                $disk->delete($resourceFile->file_path_or_url);
            }

            $pathInfo = pathinfo(parse_url($newUrl, PHP_URL_PATH) ?? '');
            $ext = strtolower($pathInfo['extension'] ?? '');
            
            $fileType = 'document';
            if (in_array($ext, ['pdf'])) $fileType = 'pdf';
            elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) $fileType = 'image';
            elseif (in_array($ext, ['mp3', 'wav', 'ogg'])) $fileType = 'audio';
            elseif (in_array($ext, ['mp4', 'webm', 'ogv'])) $fileType = 'video';
            elseif (in_array($ext, ['zip', 'rar', '7z'])) $fileType = 'zip';

            $resourceFile->update([
                'file_path_or_url' => $newUrl,
                'is_external' => true,
                'file_type' => $fileType,
                'mime_type' => null,
                'size_in_bytes' => null,
                'metadata' => ['file_type' => $fileType],
            ]);
        } elseif ($newFile) {
            if (!$resourceFile->is_external && $resourceFile->file_path_or_url) {
                $disk->delete($resourceFile->file_path_or_url);
            }

            $extension = $newFile->getClientOriginalExtension();
            $metadata = [];

            foreach ($this->processors as $processor) {
                if ($processor instanceof FileProcessorInterface && $processor->supports($extension)) {
                    $metadata = $processor->process($newFile);
                    break;
                }
            }

            $fileName = uniqid() . '_' . time() . '.' . $extension;
            $path = $disk->putFileAs('resources', $newFile, $fileName);

            $resourceFile->update([
                'file_path_or_url' => $path,
                'is_external' => false,
                'file_type' => $metadata['file_type'] ?? 'document',
                'mime_type' => $metadata['mime_type']
                    ?? $newFile->getMimeType()
                    ?? $newFile->getClientMimeType(),
                'size_in_bytes' => $newFile->getSize(),
                'metadata' => $metadata,
            ]);
        }
    }

    /**
     * Comprueba si una URL externa es accesible mediante una petición HTTP.
     */
    public function isUrlAccessible(string $url): bool
    {
        try {
            $response = Http::timeout(5)->withoutVerifying()->head($url);
            if ($response->successful() || $response->redirect()) {
                return true;
            }

            $response = Http::timeout(5)->withoutVerifying()->get($url);
            return $response->successful() || $response->redirect();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Devuelve una respuesta en Streaming para visualizar PDFs o multimedia de forma segura.
     */
    public function streamFile(Resource $resource): mixed
    {
        /** @var ResourceFile|null $resourceFile */
        $resourceFile = $resource->resourceable;

        if (!$resourceFile) {
            abort(404);
        }

        if ($resourceFile->is_external) {
            return redirect()->away($resourceFile->file_path_or_url);
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('b2');
        $filePath = $resourceFile->file_path_or_url;

        if (!$disk->exists($filePath)) {
            abort(404);
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $mimeType = match ($extension) {
            'ogv'   => 'video/ogg',
            'ogg'   => ($resourceFile->file_type === 'video') ? 'video/ogg' : 'audio/ogg',
            'webm'  => 'video/webm',
            'mp4'   => 'video/mp4',
            'pdf'   => 'application/pdf',
            default => $resourceFile->mime_type ?: 'application/octet-stream',
        };

        return $disk->response($filePath, null, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
            'Accept-Ranges'       => 'bytes',
        ]);
    }
    /**
     * Obtiene el árbol de archivos/directorios descargando temporalmente el archivo de B2.
     */
    public function getFileTree(string $filePath): array
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        if (str_ends_with(strtolower($filePath), '.tar.gz') || str_ends_with(strtolower($filePath), '.tgz')) {
            $extension = 'tar.gz';
        }

        if (!in_array($extension, ['zip', 'tar.gz', 'tgz', 'gz', 'tar', 'rar', '7z'])) {
            return [];
        }

        $tempPath = sys_get_temp_dir() . '/' . uniqid('res_tree_') . '.' . $extension;

        try {
            /** @var FilesystemAdapter $disk */
            $disk = Storage::disk('b2');

            if (!$disk->exists($filePath)) {
                return [];
            }

            $fileContent = $disk->get($filePath);
            file_put_contents($tempPath, $fileContent);

            $processor = FileProcessorFactory::make($extension);
            $tree = $processor->getContentsTree($tempPath);

            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }

            return $tree;
        } catch (\Throwable $e) {
            Log::error("Error en getFileTree: " . $e->getMessage());
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
            return [];
        }
    }

        /**
     * Obtiene el contenido de un archivo de texto de forma segura con un límite de tiempo.
     * 
     * @param string $fileUrl URL pública, firmada de Backblaze B2 o ruta externa.
     * @return string
     */
    public function getTextFileContent(string $fileUrl): string
    {
        try {
            // Contexto HTTP con timeout de 3 segundos para evitar bloquear el servidor
            $context = stream_context_create([
                'http' => [
                    'timeout' => 3,
                    'follow_location' => true
                ]
            ]);

            $content = @file_get_contents($fileUrl, false, $context);

            if ($content === false) {
                return 'No se pudo leer el contenido del archivo (Error de red o archivo vacío).';
            }

            return $content;
        } catch (\Exception $e) {
            // Aquí puedes registrar el log si lo consideras necesario: Log::error($e->getMessage());
            return 'Ocurrió un error inesperado al procesar el documento de texto.';
        }
    }
}
