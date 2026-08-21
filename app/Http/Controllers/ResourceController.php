<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResourceRequest;
use App\Models\Resource;
use App\Models\User;
use App\Repositories\Contracts\ResourceRepositoryInterface;
use App\Services\ResourceUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ResourceController extends Controller
{
    protected ResourceUploadService $uploadService;
    protected ResourceRepositoryInterface $resourceRepository;

    public function __construct(
        ResourceUploadService $uploadService,
        ResourceRepositoryInterface $resourceRepository
    ) {
        $this->uploadService = $uploadService;
        $this->resourceRepository = $resourceRepository;
    }

    public function create(): View
    {
        $games = $this->resourceRepository->getUniqueValues('game');
        $campaigns = $this->resourceRepository->getUniqueValues('campaign');
        $authors = $this->resourceRepository->getUniqueValues('author');
        $allTags = $this->resourceRepository->getUniqueTags();
        
        $totalLimitBytes = 10 * 1024 * 1024 * 1024; // 10 GB
        $usedBytes = $this->resourceRepository->getTotalUsedStorageInBytes();
        $remainingBytes = max(0, $totalLimitBytes - $usedBytes);

        return view('resources.create', compact(
            'games', 'campaigns', 'authors', 'allTags',
            'usedBytes', 'remainingBytes', 'totalLimitBytes'
        ));
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'game', 'campaign', 'author', 'type', 'tag', 'user_id']);
        $resources = $this->resourceRepository->searchResources($filters);

        $games = $this->resourceRepository->getUniqueValues('game');
        $campaigns = $this->resourceRepository->getUniqueValues('campaign');
        $authors = $this->resourceRepository->getUniqueValues('author');
        $allTags = $this->resourceRepository->getUniqueTags();

        return view('resources.index', compact('resources', 'games', 'campaigns', 'authors', 'allTags', 'filters'));
    }

    public function show(int $id): View
    {
        /** @var Resource|null $resource */
        $resource = $this->resourceRepository->findById($id);

        if (!$resource) {
            abort(404, 'Recurso no encontrado.');
        }

        if ($resource->privacy === 'private' && $resource->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para ver este recurso privado.');
        }

        $fileUrl = null;
        if ($resource->type === 'file' && $resource->resourceable) {
            $resourceFile = $resource->resourceable;

            if ($resourceFile->is_external) {
                $fileUrl = $resourceFile->file_path_or_url;
            } else {
                $fileUrl = $this->uploadService->getFileUrl($resourceFile->file_path_or_url, 15);
            }
        }

        return view('resources.show', compact('resource', 'fileUrl'));
    }

    public function edit(int $id): View
    {
        /** @var Resource|null $resource */
        $resource = $this->resourceRepository->findById($id);

        if (!$resource || ($resource->user_id !== null && $resource->user_id !== Auth::id())) {
            abort(403, 'No tienes permiso para editar este recurso.');
        }

        $games = $this->resourceRepository->getUniqueValues('game');
        $campaigns = $this->resourceRepository->getUniqueValues('campaign');
        $authors = $this->resourceRepository->getUniqueValues('author');
        $allTags = $this->resourceRepository->getUniqueTags();

        return view('resources.edit', compact('resource', 'games', 'campaigns', 'authors', 'allTags'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        /** @var Resource|null $resource */
        $resource = $this->resourceRepository->findById($id);

        if (!$resource || ($resource->user_id !== null && $resource->user_id !== Auth::id())) {
            abort(403, 'No tienes permiso para actualizar este recurso.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'privacy' => ['required', 'in:public,private'],
            'game' => ['nullable', 'string', 'max:100'],
            'campaign' => ['nullable', 'string', 'max:100'],
            'author' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:102400'],
            'external_url' => ['nullable', 'url', 'max:2048'],
        ]);

        $validated['tags'] = !empty($validated['tags'])
            ? array_map('trim', explode(',', $validated['tags']))
            : [];

        if (!empty($validated['external_url'])) {
            if (!$this->uploadService->isUrlAccessible($validated['external_url'])) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['external_url' => 'La nueva URL introducida no es accesible. Comprueba que responda correctamente antes de guardar.']);
            }
        }
        
        $uploadedFile = $request->file('file');
        if ($uploadedFile instanceof UploadedFile || !empty($validated['external_url'])) {
            $this->uploadService->updateResourceFile(
                $resource, 
                $uploadedFile instanceof UploadedFile ? $uploadedFile : null, 
                $validated['external_url'] ?? null
            );
        }

        $this->resourceRepository->updateResource($resource, $validated);

        return redirect()->route('resources.show', $resource->id)
            ->with('success', '¡Recurso actualizado y asignado a tu cuenta con éxito!');
    }

    public function destroy(int $id): RedirectResponse
    {
        /** @var Resource|null $resource */
        $resource = $this->resourceRepository->findById($id);

        if (!$resource || ($resource->user_id !== null && $resource->user_id !== Auth::id())) {
            abort(403, 'No tienes permiso para eliminar este recurso.');
        }

        $this->uploadService->deleteResource($resource);

        return redirect()->route('resources.index')
            ->with('success', 'Recurso y archivo eliminados correctamente.');
    }

    public function store(StoreResourceRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['tags'] = !empty($validated['tags'])
            ? array_map('trim', explode(',', $validated['tags']))
            : [];

        if (!empty($validated['external_url'])) {
            if (!$this->uploadService->isUrlAccessible($validated['external_url'])) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['external_url' => 'La URL introducida no está accesible o no existe. Comprueba que esté bien escrita y sea pública.']);
            }

            $existing = $this->resourceRepository->findByUrl($validated['external_url']);
            if ($existing) {
                return redirect()->back()
                    ->withInput()
                    ->with('warning', "Este recurso ya está registrado como \"{$existing->title}\".")
                    ->with('existing_resource_id', $existing->id)
                    ->with('existing_resource_title', $existing->title);
            }
            
            $resource = $this->uploadService->registerExternalUrl($validated['external_url'], $validated);
        } else {
            /** @var UploadedFile $file */
            $file = $request->file('file');
            $resource = $this->uploadService->uploadFile($file, $validated);
        }

        return redirect()->route('resources.create')
            ->with('success', '¡Recurso registrado con éxito!')
            ->with('resource_id', $resource->id)
            ->with('resource_title', $resource->title);
    }

    public function streamPdf(int $id): mixed
    {
        /** @var Resource|null $resource */
        $resource = $this->resourceRepository->findById($id);

        if (!$resource) {
            abort(404);
        }

        if ($resource->privacy === 'private' && $resource->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para visualizar este recurso.');
        }

        return $this->uploadService->streamFile($resource);
    }
}
