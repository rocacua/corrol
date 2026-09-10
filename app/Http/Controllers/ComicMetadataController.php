<?php
namespace App\Http\Controllers;

use App\Models\Resource;
use App\Services\ComicMetadataService;
use Illuminate\Http\Request;
use App\Services\ResourceUploadService;
use Illuminate\Support\Facades\Http;

class ComicMetadataController extends Controller
{
    public function __construct(
        private ComicMetadataService $comicService,
        private ResourceUploadService $uploadService
    ) {}

    public function edit(Resource $resource)
    {
        // Validar que sea un PDF mediante tu Strategy o MimeType
        return view('comic.mapper', compact('resource'));
    }

    public function update(Request $request, Resource $resource)
    {
        $this->comicService->saveMetadata($resource->id, $request->input('metadata'));
        return response()->json(['success' => true]);
    }

    public function destroy(Resource $resource)
    {
        $this->comicService->removeMetadata($resource->id);
        return redirect()->route('resources.edit', $resource)->with('status', 'Convertido a PDF normal.');
    }

    public function streamComic(Resource $resource)
    {
        if ($resource->privacy === 'private' && $resource->user_id !== auth()->id()) {
            abort(403);
        }

        $resourceFile = $resource->resourceable;
        if (!$resourceFile) {
            abort(404);
        }

        // Si es URL externa, servimos el contenido directamente desde Laravel (Proxy anti-CORS)
        if ($resourceFile->is_external) {
            $response = Http::withoutVerifying()->get($resourceFile->file_path_or_url);

            if (!$response->successful()) {
                abort(404, 'No se pudo obtener el PDF externo.');
            }

            return response($response->body(), 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="comic.pdf"',
            ]);
        }

        // Si está en Storage / Backblaze B2, usamos la transmisión existente
        return $this->uploadService->streamFile($resource);
    }
}