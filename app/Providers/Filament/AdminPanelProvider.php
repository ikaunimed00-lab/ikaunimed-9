<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\Facades\Auth;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->favicon(asset('images/favicon_ikaunimed.png'))
            ->brandLogo(fn () => new HtmlString('
                <div style="display: flex; align-items: center; gap: 12px; height: 40px;">
                    <img src="' . asset('images/favicon_ikaunimed.png') . '" alt="IKA UNIMED" style="height: 40px; width: 40px; object-fit: contain;">
                    <div style="display: flex; flex-direction: column; line-height: 1.2;">
                        <div style="font-size: 1.125rem; font-weight: 700; letter-spacing: -0.025em;">
                            <span style="color:#FF7E00;">IKA</span>
                            <span style="color:#00A69D;">UNI</span>
                            <span style="color:#e9cf35;">MED</span>
                        </div>
                        <div style="font-size: 10px; font-weight: 500; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em;">
                            Portal Alumni
                        </div>
                    </div>
                </div>
            '))
            ->colors([
                'primary' => Color::Emerald,
                'gray' => Color::Slate,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->assets([
                Css::make('admin-custom', resource_path('css/filament/admin.css')),
            ])
            ->navigationGroups([
                'Dashboard',
                'CMS',
                'User Management',
                'LMS Governance',
                'LMS Application',
            ])
            ->navigationItems([
                NavigationItem::make('Buka Dashboard LMS')
                    ->url(function () {
                        $user = Auth::user();

                        if (! $user) {
                            return route('dashboard');
                        }

                        if ($user->can('elearning.participant.enroll')) {
                            return route('dashboard.elearning.learner.courses.index');
                        }

                        if ($user->can('elearning.course.view_any')) {
                            return route('dashboard.elearning.moderator.courses.index');
                        }

                        if ($user->can('elearning.course.view') || $user->can('elearning.course.view_own')) {
                            return route('dashboard.courses.index');
                        }

                        return route('dashboard');
                    })
                    ->icon('heroicon-o-academic-cap')
                    ->group('LMS Application')
                    ->sort(10)
                    ->visible(fn () => Auth::user()?->hasAnyPermission([
                        'elearning.participant.enroll',
                        'elearning.course.view',
                        'elearning.course.view_own',
                        'elearning.course.view_any',
                        'elearning.course.create',
                    ]) ?? false),
                NavigationItem::make('Kursus Saya (LMS)')
                    ->url(fn () => route('dashboard.elearning.learner.courses.index'))
                    ->icon('heroicon-o-academic-cap')
                    ->group('LMS Application')
                    ->sort(11)
                    ->visible(fn () => Auth::user()?->can('elearning.participant.enroll') ?? false),
                NavigationItem::make('Dashboard LMS (Moderator)')
                    ->url(fn () => route('dashboard.elearning.moderator.courses.index'))
                    ->icon('heroicon-o-academic-cap')
                    ->group('LMS Application')
                    ->sort(12)
                    ->visible(fn () => Auth::user()?->can('elearning.course.view_any') ?? false),
            ]);
    }
}
