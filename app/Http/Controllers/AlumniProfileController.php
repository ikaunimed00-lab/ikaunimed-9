<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Models\Notification;

class AlumniProfileController extends Controller
{
    /**
     * Tampilkan form edit profil alumni
     */
    public function edit()
    {
        $user = auth()->user();
        $user->load('educations');

        // Get notifications for sidebar
        try {
            $notifications = Notification::unreadForUser($user->id);
        } catch (\Exception $e) {
            $notifications = [];
        }

        return Inertia::render('Alumni/Profile/Edit', [
            'user' => $user,
            'notifications' => $notifications,
            'stats' => [],
        ]);
    }

    /**
     * Update profil alumni
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            // Identitas Pribadi
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'gender' => ['nullable', 'string', 'in:L,P'],
            'wa' => ['required', 'string', 'regex:/^(\+62|62|0)[0-9]{9,12}$/'],
            'nik' => ['required', 'string', 'size:16', 'regex:/^[0-9]{16}$/'],
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'alamat_lengkap' => ['required', 'string', 'max:500'],
            'domicile' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],

            // Pendidikan S1 (Legacy / Wajib)
            's1_fakultas' => ['nullable', 'string', 'max:150'],
            's1_prodi' => ['nullable', 'string', 'max:150'],
            's1_tahun_masuk' => ['nullable', 'integer', 'min:1990', 'max:' . now()->year],
            's1_tahun_tamat' => ['nullable', 'integer', 'min:1990', 'max:' . now()->year],

            // Pendidikan S2 (Opsional)
            's2_prodi' => ['nullable', 'string', 'max:150'],
            's2_tahun_masuk' => ['nullable', 'integer', 'min:1990', 'max:' . now()->year],
            's2_tahun_tamat' => ['nullable', 'integer', 'min:1990', 'max:' . now()->year],

            // Pendidikan S3 (Opsional)
            's3_prodi' => ['nullable', 'string', 'max:150'],
            's3_tahun_masuk' => ['nullable', 'integer', 'min:1990', 'max:' . now()->year],
            's3_tahun_tamat' => ['nullable', 'integer', 'min:1990', 'max:' . now()->year],

            // Riwayat Pendidikan (D1-S3)
            'educations' => ['nullable', 'array'],
            'educations.*.level' => ['required', 'string'],
            'educations.*.university' => ['required', 'string'],
            'educations.*.faculty' => ['nullable', 'string'],
            'educations.*.major' => ['required', 'string'],
            'educations.*.admission_year' => ['nullable', 'integer', 'min:1990', 'max:' . now()->year],
            'educations.*.graduation_year' => ['nullable', 'integer', 'min:1990', 'max:' . (now()->year + 10)],

        ], [
            'wa.regex' => 'Nomor WhatsApp tidak valid. Gunakan format: 0812xxxxxx atau +62812xxxxxx',
            'nik.size' => 'NIK harus terdiri dari 16 digit',
            'nik.regex' => 'NIK hanya boleh berisi angka',
        ]);

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

        $user->update(collect($validated)->except('educations')->toArray());

        if ($request->has('educations')) {
            $user->educations()->delete();
            $user->educations()->createMany($request->educations);
        }

        $user->recalculateProfileMeta();

        return redirect()->route('dashboard')
            ->with('success', 'Profil alumni berhasil diperbarui! Selamat datang di portal IKA UNIMED.');
    }
}
