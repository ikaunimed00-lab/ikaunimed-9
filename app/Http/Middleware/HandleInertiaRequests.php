<?php

namespace App\Http\Middleware;

use App\Models\CartItem;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),

            'name' => config('app.name'),

            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],

            'quote' => [
                'message' => trim($message),
                'author' => trim($author),
            ],

            'auth' => [
                'user' => $request->user()
                    ? [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                        'email' => $request->user()->email,
                        'role' => $request->user()->systemRoleLabel(),
                        'roles' => $request->user()->getRoleNames(),
                        'permissions' => $request->user()->getAllPermissions()->pluck('name'),
                    ]
                    : null,
                'dashboard_route' => $this->dashboardRoute($request),
            ],

            'cart' => [
                'item_count' => $this->cartItemCount($request),
            ],

            'sidebarOpen' =>
                ! $request->hasCookie('sidebar_state')
                || $request->cookie('sidebar_state') === 'true',
        ];
    }

    private function cartItemCount(Request $request): int
    {
        $user = $request->user();

        if (! $user) {
            return 0;
        }

        return CartItem::query()
            ->whereHas('cart', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->where('status', 'active');
            })
            ->sum('quantity');
    }

    /**
     * SINGLE SOURCE OF TRUTH: Dashboard per role
     */
    private function dashboardRoute(Request $request): string
    {
        $user = $request->user();

        if (! $user) {
            return route('home');
        }

        if ($user->hasAnyRole(['admin', 'editor', 'writer'])) {
            return '/admin';
        }

        if ($user->hasRole('subscriber')) {
            return route('dashboard.subscriber');
        }

        return route('home');
    }
}
