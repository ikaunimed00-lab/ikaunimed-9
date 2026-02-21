<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    private function ensureAdmin()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak punya akses.');
        }
    }

    public function index()
    {
        $this->ensureAdmin();

        $users = User::with('organization:id,name,type')
            ->select('id', 'name', 'email', 'role', 'organization_id')
            ->orderBy('name')
            ->get();

        return Inertia::render('Dashboard/Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function edit(User $user)
    {
        $this->ensureAdmin();

        return Inertia::render('Dashboard/Admin/Users/Edit', [
            'user' => $user->load('organization'),
            'organizations' => Organization::select('id', 'name', 'type')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'role' => 'required|in:subscriber,writer,editor,admin',
            'organization_id' => 'nullable|exists:organizations,id',
        ]);

        $user->update($validated);
        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->ensureAdmin();

        // Prevent deleting the currently logged-in user
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:users,id',
        ]);

        // Prevent deleting the currently logged-in user
        $ids = array_filter($request->ids, fn ($id) => $id !== auth()->id());

        if (empty($ids)) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        User::whereIn('id', $ids)->delete();
        
        return back()->with('success', count($ids) . ' user berhasil dihapus.');
    }}
