<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\ResourceRepositoryInterface;
use Illuminate\View\View;

class GameController extends Controller
{
    protected ResourceRepositoryInterface $resourceRepository;

    public function __construct(ResourceRepositoryInterface $resourceRepository)
    {
        $this->resourceRepository = $resourceRepository;
    }

    public function index(): View
    {
        $games = $this->resourceRepository->getUniqueValues('game');
        return view('games.index', compact('games'));
    }
}