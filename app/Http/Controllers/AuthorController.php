<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\ResourceRepositoryInterface;
use Illuminate\View\View;

class AuthorController extends Controller
{
    protected ResourceRepositoryInterface $resourceRepository;

    public function __construct(ResourceRepositoryInterface $resourceRepository)
    {
        $this->resourceRepository = $resourceRepository;
    }

    public function index(): View
    {
        $authors = $this->resourceRepository->getUniqueValues('author');
        return view('authors.index', compact('authors'));
    }
}