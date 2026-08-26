<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\User;
use App\Repositories\Contracts\ResourceRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SheetController extends Controller
{
    protected ResourceRepositoryInterface $resourceRepository;

    public function __construct(ResourceRepositoryInterface $resourceRepository)
    {
        $this->resourceRepository = $resourceRepository;
    }

    public function createSheet(Request $request): View
    {
        return $this->createForm($request, 'sheet');
    }

    public function createDiary(Request $request): View
    {
        return $this->createForm($request, 'diary');
    }

    public function createCampaign(Request $request): View
    {
        return $this->createForm($request, 'campaign');
    }

    public function create(Request $request, string $type = 'sheet'): View
    {
        return $this->createForm($request, $type);
    }

    protected function createForm(Request $request, string $type): View
    {
        $userId = Auth::id();
        $userResources = $userId ? $this->resourceRepository->getUserResourcesByType($userId, $type) : collect();
        
        $editingResource = null;
        $isClone = false;

        // Caso 1: Edición de un recurso propio
        if ($request->has('edit_id')) {
            $editingResource = $this->resourceRepository->findById((int) $request->get('edit_id'));
        }
        // Caso 2: Clonación de un recurso existente (propio o público)
        elseif ($request->has('clone_id')) {
            $sourceResource = $this->resourceRepository->findById((int) $request->get('clone_id'));
            if ($sourceResource && ($sourceResource->privacy === 'public' || $sourceResource->user_id === $userId)) {
                $editingResource = $sourceResource;
                $isClone = true;
            }
        }

        $games = $this->resourceRepository->getUniqueValues('game');
        $campaigns = $this->resourceRepository->getUniqueValues('campaign');
        $authors = $this->resourceRepository->getUniqueValues('author');
        $allTags = $this->resourceRepository->getUniqueTags();

        return view('sheets.form', compact('type', 'userResources', 'editingResource', 'isClone', 'games', 'campaigns', 'authors', 'allTags'));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'resource_id' => ['nullable', 'integer', 'exists:resources,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'privacy' => ['required', 'in:public,private'],
            'type' => ['required', 'in:sheet,diary,campaign'],
            'game' => ['nullable', 'string', 'max:100'],
            'campaign' => ['nullable', 'string', 'max:100'],
            'author' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'string'],
            'content_json' => ['required', 'string'],
            'resource_id' => ['nullable', 'integer'],
        ]);

        $validated['tags'] = !empty($validated['tags'])
            ? array_filter(array_map('trim', explode(',', $validated['tags'])))
            : [];

        $content = !empty($validated['content_json']) ? json_decode($validated['content_json'], true) : [];

        if (!empty($validated['resource_id'])) {
            /** @var Resource|null $resource */
            $resource = $this->resourceRepository->findById((int) $validated['resource_id']);
            if (!$resource || $resource->user_id !== $user->id) {
                abort(403);
            }
            $resource = $this->resourceRepository->updateSheetResource($resource, $validated, $content);
            $msg = '¡Recurso actualizado con éxito!';
        } else {
            $resource = $this->resourceRepository->createSheetResource($validated, $content);
            $msg = '¡Recurso creado con éxito!';
        }

        return redirect()->route('resources.show', $resource->id)->with('success', $msg);
    }
}