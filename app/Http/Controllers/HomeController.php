<?php

namespace App\Http\Controllers;

use App\Services\SiteManagementService;
use Inertia\Inertia;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $siteService;

    public function __construct(SiteManagementService $siteService)
    {
        $this->siteService = $siteService;
    }

    public function index()
    {
        return Inertia::render('Index', [
            'settings' => $this->siteService->getSettings(),
            'sections' => $this->siteService->getHomepageSections(),
        ]);
    }
}
