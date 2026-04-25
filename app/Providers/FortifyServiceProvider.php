<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureResponses();
        $this->configureRateLimiting();
    }

    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    private function configureViews(): void
    {
        Fortify::loginView(fn (Request $request) => Inertia::render('auth/login', [
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
            'canRegister' => Features::enabled(Features::registration()),
            'status' => $request->session()->get('status'),
        ]));

        Fortify::registerView(fn () => Inertia::render('auth/register'));

        Fortify::resetPasswordView(fn (Request $request) => Inertia::render('auth/reset-password', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]));

        Fortify::requestPasswordResetLinkView(fn (Request $request) => Inertia::render('auth/forgot-password', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::verifyEmailView(fn (Request $request) => Inertia::render('auth/verify-email', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::twoFactorChallengeView(fn () => Inertia::render('auth/two-factor-challenge'));

        Fortify::confirmPasswordView(fn () => Inertia::render('auth/confirm-password'));
    }

    private function configureResponses(): void
    {
        Fortify::redirects('register', function () {
            return route('profile.edit');
        });

        $this->app->singleton(LoginResponseContract::class, function () {
            return new class implements LoginResponseContract {
                public function toResponse($request)
                {
                    $user = $request->user();

                    $fallback = route('home');

                    if (! $user) {
                        return redirect()->intended($fallback);
                    }

                    if ($user->hasSystemRole(['super_admin', 'admin', 'editor', 'writer'])) {
                        $target = '/admin';
                    } else {
                        $requiredFields = [
                            'wa',
                            'nik',
                            'tempat_lahir',
                            'tanggal_lahir',
                            'alamat_lengkap',
                            's1_fakultas',
                            's1_prodi',
                            's1_tahun_masuk',
                            's1_tahun_tamat',
                        ];

                        $profileIncomplete = false;
                        foreach ($requiredFields as $field) {
                            if (empty($user->{$field})) {
                                $profileIncomplete = true;
                                break;
                            }
                        }

                        $target = $profileIncomplete ? route('profile.edit') : route('dashboard.subscriber');
                    }

                    $intended = $request->session()->get('url.intended');
                    $destination = $intended ?: $target;
                    $destinationPath = parse_url($destination, PHP_URL_PATH) ?: '';

                    if ($request->header('X-Inertia') && str_starts_with($destinationPath, '/admin')) {
                        return Inertia::location($destination);
                    }

                    return redirect()->intended($target);
                }
            };
        });
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::lower($request->input(Fortify::username())).'|'.$request->ip();
            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
