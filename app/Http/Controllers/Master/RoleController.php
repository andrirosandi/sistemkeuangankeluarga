<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\RoleVisibility;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Tampilkan daftar role beserta jumlah user dan permission.
     */
    public function index()
    {
        $roles = Role::withCount(['users', 'permissions'])->orderBy('name')->get();
        
        // Untuk dropdown "Salin Dari" di modal add
        $roleOptions = Role::orderBy('name')->get();
        
        // Ambil semua permissions dikelompokkan untuk modal edit
        $allPermissions = Permission::orderBy('name')->get()->groupBy(function($item) {
            return explode('.', $item->name)[0]; // Group by module (e.g. 'in', 'out', 'user')
        });

        // Ambil visibility mapping per role: [role_id => [watched_role_ids]]
        $visibilityMap = RoleVisibility::all()
            ->groupBy('watcher_role_id')
            ->map(fn($items) => $items->pluck('watched_role_id')->toArray());

        // Semua role untuk checkbox visibilitas (kecuali admin)
        $allRoles = Role::where('name', '!=', 'admin')->orderBy('name')->get();

        return view('master.role.index', compact(
            'roles', 'roleOptions', 'allPermissions', 'visibilityMap', 'allRoles'
        ));
    }

    /**
     * Simpan role baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'copy_from_id' => 'nullable|exists:roles,id'
        ]);

        try {
            $role = Role::create([
                'name' => strtolower($request->name),
                'guard_name' => 'web'
            ]);

            // Jika ada preset, salin seluruh permissionnya + visibility
            if ($request->copy_from_id) {
                $sourceRole = Role::findById($request->copy_from_id);
                $role->syncPermissions($sourceRole->permissions);

                // Salin visibility dari source role
                $sourceVisibility = RoleVisibility::getWatchedRoleIds($sourceRole->id)->toArray();
                if (!empty($sourceVisibility)) {
                    RoleVisibility::syncForRole($role->id, $sourceVisibility, auth()->id());
                }
            }

            return redirect()->back()->with('success', "Role '{$role->name}' berhasil dibuat.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with([
                'error' => 'Gagal membuat role! Silakan coba lagi.',
                'modal' => 'add'
            ]);
        }
    }

    /**
     * Update role dan sinkronisasi permission + visibility.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
            'visibility' => 'nullable|array',
            'visibility.*' => 'integer|exists:roles,id'
        ]);

        try {
            // Update nama (jangan allow ganti name role 'admin' jika mau lebih strict)
            if ($role->name !== 'admin') {
                $role->update(['name' => strtolower($request->name)]);
            }

            // Sync Permissions
            $role->syncPermissions($request->permissions ?? []);

            // Sync Visibility (hanya untuk non-admin)
            if ($role->name !== 'admin') {
                RoleVisibility::syncForRole(
                    $role->id,
                    $request->visibility ?? [],
                    auth()->id()
                );
            }

            return redirect()->back()->with('success', "Konfigurasi role '{$role->name}' diperbarui.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with([
                'error' => 'Gagal memperbarui role! ' . $e->getMessage(),
                'modal' => 'edit'
            ]);
        }
    }

    /**
     * Hapus role.
     */
    public function destroy(Role $role)
    {
        if ($role->name === 'admin') {
            return redirect()->back()->with('error', 'Role [admin] tidak boleh dihapus demi keamanan sistem.');
        }

        try {
            $role->delete(); // CASCADE akan hapus role_visibility otomatis
            return redirect()->back()->with('success', 'Role berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus role! Mungkin masih digunakan oleh user.');
        }
    }

    /**
     * Hapus banyak role.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:roles,id'
        ]);

        $rolesToDelete = Role::whereIn('id', $request->ids)->get();

        // Cek jika ada admin di dalam list
        if ($rolesToDelete->contains('name', 'admin')) {
            return redirect()->back()->with('error', 'Operasi dibatalkan! Salah satu role yang dipilih adalah [admin].');
        }

        try {
            Role::whereIn('id', $request->ids)->delete(); // CASCADE visibility
            return redirect()->back()->with('success', count($request->ids) . ' role berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus beberapa role! Silakan periksa keterkaitan data.');
        }
    }
}
