<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

use App\Models\Education;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user()->load('educations');
        
        return Inertia::render('Dashboard/Subscriber/Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'educations' => $user->educations,
        ]);
    }

    /**
     * Update the user's profile settings.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (isset($validated['educations'])) {
            foreach ($validated['educations'] as $index => $education) {
                $admissionYear = $education['admission_year'] ?? null;
                $graduationYear = $education['graduation_year'] ?? null;

                if ($admissionYear && $graduationYear && (int) $admissionYear > (int) $graduationYear) {
                    throw ValidationException::withMessages([
                        "educations.$index.graduation_year" => 'Tahun lulus harus lebih besar atau sama dengan tahun masuk.',
                    ]);
                }
            }
        }
        
        // Update user basic info
        $request->user()->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        $request->user()->recalculateProfileMeta();

        if (isset($validated['educations'])) {
            // Kita hapus semua pendidikan lama dan insert yang baru (strategy simple sync)
            // Atau update if exist. Karena structure fix D1-S3, kita bisa update or create.
            // Tapi simple-nya: delete existing for this user, insert new non-empty ones.
            
            $user = $request->user();
            
            // Hapus yang lama
            $user->educations()->delete();
            
            // Insert yang baru (filter yang kosong)
            foreach ($validated['educations'] as $edu) {
                if (!empty($edu['university']) || !empty($edu['major'])) {
                    $user->educations()->create($edu);
                }
            }
        }

        return to_route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
