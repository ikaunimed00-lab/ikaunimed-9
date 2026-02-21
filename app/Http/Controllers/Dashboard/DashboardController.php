<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Legalization;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show subscriber dashboard
     */
    public function subscriberDashboard(Request $request)
    {
        $user = auth()->user();

        // Get legalization submissions with search
        $legalizations = $user->legalizations()
            ->with('files')
            ->when($request->search, function($query, $search) {
                $query->where('jenjang', 'like', "%{$search}%")
                      ->orWhere('tujuan', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Get unread notifications (safely handle table existence)
        try {
            $notifications = Notification::unreadForUser($user->id);
        } catch (\Exception $e) {
            $notifications = [];
        }

        // Get statistics
        $stats = [
            'total' => $user->legalizations()->count(),
            'pending' => $user->legalizations()->where('status', 'pending')->count(),
            'approved' => $user->legalizations()->where('status', 'approved')->count(),
            'rejected' => $user->legalizations()->where('status', 'rejected')->count(),
        ];

        return Inertia::render('Dashboard/Subscriber/Index', [
            'user' => $user,
            'legalizations' => $legalizations,
            'notifications' => $notifications,
            'stats' => $stats,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Determine user's dashboard redirect
     */
    public static function getDashboardRoute(User $user): string
    {
        if ($user->hasSystemRole(['admin', 'editor', 'writer'])) {
            return '/admin';
        }

        if ($user->hasSystemRole('subscriber')) {
            return route('dashboard.subscriber');
        }

        return route('home');
    }
}
