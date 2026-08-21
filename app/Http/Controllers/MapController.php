<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\User;
use App\Repositories\Contracts\ResourceRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MapController extends Controller
{
    protected ResourceRepositoryInterface $resourceRepository;

    public function __construct(ResourceRepositoryInterface $resourceRepository)
    {
        $this->resourceRepository = $resourceRepository;
    }

    public function create(Request $request): View
    {
        $userId = Auth::id();
        $userResources = $userId ? $this->resourceRepository->getUserResourcesByType($userId, 'map') : collect();
        
        $editingResource = null;
        $isClone = false;

        if ($request->has('edit_id')) {
            $editingResource = $this->resourceRepository->findById((int) $request->get('edit_id'));
        } elseif ($request->has('clone_id')) {
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

        return view('maps.form', compact('userResources', 'editingResource', 'isClone', 'games', 'campaigns', 'authors', 'allTags'));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'privacy' => ['required', 'in:public,private'],
            'game' => ['nullable', 'string', 'max:100'],
            'campaign' => ['nullable', 'string', 'max:100'],
            'author' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'string'],
            'map_image_url' => ['required', 'url'],
            'markers_json' => ['required', 'string'],
            'resource_id' => ['nullable', 'integer'],
        ]);

        $markers = json_decode($validated['markers_json'], true) ?? [];
        $tags = !empty($validated['tags']) ? array_map('trim', explode(',', $validated['tags'])) : [];
        $validated['tags'] = $tags;

        if (!empty($validated['resource_id'])) {
            /** @var Resource|null $resource */
            $resource = $this->resourceRepository->findById((int) $validated['resource_id']);
            if (!$resource || $resource->user_id !== $user->id) {
                abort(403);
            }
            $resource = $this->resourceRepository->updateMapResource($resource, $validated, $validated['map_image_url'], $markers);
            $msg = '¡Mapa interactivo actualizado!';
        } else {
            $resource = $this->resourceRepository->createMapResource($validated, $validated['map_image_url'], $markers);
            $msg = '¡Mapa interactivo creado con éxito!';
        }

        return redirect()->route('resources.show', $resource->id)->with('success', $msg);
    }
}