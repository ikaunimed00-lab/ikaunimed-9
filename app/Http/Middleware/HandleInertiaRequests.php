<?php

namespace App\Http\Middleware;

use App\Models\CartItem;
use App\Models\HomepageSection;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Inertia\Middleware;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

            'topbar' => Cache::remember('global_topbar_content', 3600, function () {
                $content = HomepageSection::where('slug', 'topbar')
                    ->where('is_active', true)
                    ->first()?->content;

                if (is_array($content) && is_array($content['links'] ?? null)) {
                    $content['links'] = array_values(array_filter(
                        $content['links'],
                        fn ($link): bool => is_array($link) && $this->isReachableLink((string) ($link['href'] ?? '')),
                    ));
                }

                return $content;
            }),

            'sidebarOpen' =>
                ! $request->hasCookie('sidebar_state')
                || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * CMS links pointing at a local path without a registered GET route are hidden.
     */
    private function isReachableLink(string $href): bool
    {
        if ($href === '' || ! str_starts_with($href, '/') || str_starts_with($href, '//')) {
            return $href !== '';
        }

        try {
            Route::getRoutes()->match(Request::create($href, 'GET'));
        } catch (NotFoundHttpException|MethodNotAllowedHttpException) {
            return false;
        }

        return true;
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

        if ($user->hasAnyRole(['super_admin', 'admin', 'editor', 'writer'])) {
            return '/admin';
        }

        if ($user->hasRole('subscriber')) {
            return route('dashboard.subscriber');
        }

        return route('home');
    }
}
