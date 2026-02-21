<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    /**
     * Redirect user to Google OAuth
     */
    public function googleRedirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function googleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Find or create user
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // User exists, just update OAuth info if needed
                Auth::login($user);
            } else {
                // Create new user from Google data
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => bcrypt(\Str::random(24)), // Random password since OAuth doesn't use password
                    'role' => 'alumni',
                    'oauth_id' => $googleUser->getId(),
                    'oauth_provider' => 'google',
                ]);

                $user->assignRole('subscriber');

                Auth::login($user);
            }

            // Redirect to profile edit (Fortify will handle the redirect)
            return redirect()->route('profile.edit');

        } catch (\Exception $e) {
            // Log error and redirect back to login
            \Log::error('Google OAuth Error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Gagal login dengan Google. Silakan coba lagi.');
        }
    }
}
