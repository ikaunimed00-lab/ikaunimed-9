<?php

namespace App\Http\Controllers\Dashboard\Subscriber;

use App\Http\Controllers\Controller;
use App\Models\ScholarshipApplicant;
use Inertia\Inertia;

class ScholarshipApplicationController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $applications = ScholarshipApplicant::with(['scholarship:id,title,slug'])
            ->where('user_id', $userId)
            ->latest('updated_at')
            ->paginate(10);

        return Inertia::render('Dashboard/Subscriber/Applications/Index', [
            'applications' => $applications,
        ]);
    }
}
