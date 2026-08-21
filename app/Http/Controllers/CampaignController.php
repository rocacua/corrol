<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\ResourceRepositoryInterface;
use Illuminate\View\View;

class CampaignController extends Controller
{
    protected ResourceRepositoryInterface $resourceRepository;

    public function __construct(ResourceRepositoryInterface $resourceRepository)
    {
        $this->resourceRepository = $resourceRepository;
    }

    public function index(): View
    {
        $campaigns = $this->resourceRepository->getUniqueValues('campaign');
        return view('campaigns.index', compact('campaigns'));
    }
}