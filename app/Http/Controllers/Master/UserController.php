<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Tampilkan daftar anggota keluarga.
     */
    public function index()
    {
        // Ambil semua user kecuali dirinya sendiri (Admin saat ini)
        $users = User::where('id', '!=', auth()->id())->get();
        return view('master.user.index', compact('users'));
    }

    /**
     * Simpan user baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Default role untuk user baru via Master ini adalah 'user'
        $user->assignRole('user');

        return redirect()->back()->with('success', 'Anggota keluarga baru berhasil didaftarkan.');
    }

    /**
     * Update data user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        // Jika password diisi, maka update password
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->back()->with('success', 'Data anggota keluarga berhasil diperbarui.');
    }

    /**
     * Hapus user.
     */
    public function destroy(User $user)
    {
        // Proteksi jangan hapus diri sendiri (sudah dicek di index, tapi untuk keamanan)
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        // Cek jika user sudah punya data transaksi (Opsional, tapi aman)
        // Di tugas.md: User memiliki data pengeluaran.
        // Jika hapus user, data pengeluaran akan error jika tidak di-restrict.
        try {
            $user->delete();
            return redirect()->back()->with('success', 'Akun anggota keluarga telah dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus! User ini memiliki riwayat data di sistem.');
        }
    }
}
