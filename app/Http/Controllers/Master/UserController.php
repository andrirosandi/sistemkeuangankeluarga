<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\BulkDeletable;
use App\Http\Requests\Master\StoreUserRequest;
use App\Http\Requests\Master\UpdateUserRequest;
use App\Http\Requests\Master\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
    public function store(StoreUserRequest $request)
    {

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
    public function update(UpdateUserRequest $request, User $user)
    {

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
    public function resetPassword(ResetPasswordRequest $request, User $user)
    {

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
