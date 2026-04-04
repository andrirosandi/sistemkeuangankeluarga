<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\BulkDeletable;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use BulkDeletable;

    protected function bulkDeleteConfig(): array
    {
        return [
            'model' => User::class,
            'table' => 'users',
            'label' => 'anggota keluarga',
        ];
    }

    protected function beforeBulkDelete(Request $request): ?\Illuminate\Http\RedirectResponse
    {
        if (in_array(auth()->id(), $request->ids)) {
            return redirect()->back()->with('error', 'Operasi dibatalkan! Salah satu akun adalah akun Anda sendiri.');
        }
        return null;
    }
    /**
     * Tampilkan daftar pengguna / anggota keluarga.
     */
    public function index()
    {
        $users = User::with('roles')->orderBy('name')->get();
        $roles = Role::where('guard_name', 'web')->get();
        return view('master.user.index', compact('users', 'roles'));
    }

    /**
     * Simpan pengguna baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|exists:roles,name'
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole($request->role);

            return redirect()->back()->with('success', 'Anggota keluarga baru berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with([
                'error' => 'Gagal menambahkan anggota baru! Silakan coba lagi.',
                'modal' => 'add'
            ]);
        }
    }

    /**
     * Update data pengguna.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,name'
        ]);

        try {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            $user->syncRoles($request->role);

            return redirect()->back()->with('success', 'Data anggota keluarga berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with([
                'error' => 'Gagal memperbarui data! Pastikan email belum digunakan.',
                'modal' => 'edit'
            ]);
        }
    }

    /**
     * Reset password pengguna.
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return redirect()->back()->with('success', 'Password user ' . $user->name . ' berhasil direset.');
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'error' => 'Gagal meriset password! Silakan coba lagi.',
                'modal' => 'reset'
            ]);
        }
    }

    /**
     * Hapus pengguna.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        try {
            $user->delete();
            return redirect()->back()->with('success', 'Anggota keluarga berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus! Akun ini mungkin sudah memiliki data terkait.');
        }
    }

}
