<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class RoleVisibility extends Model
{
    protected $table = 'role_visibility';

    protected $fillable = [
        'watcher_role_id',
        'watched_role_id',
        'created_by',
        'updated_by',
    ];

    // ─── Relationships ───

    public function watcherRole()
    {
        return $this->belongsTo(Role::class, 'watcher_role_id');
    }

    public function watchedRole()
    {
        return $this->belongsTo(Role::class, 'watched_role_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ─── Helper Methods ───

    /**
     * Ambil semua user_id yang boleh dilihat oleh user tertentu.
     * - Admin → semua user
     * - Non-admin → user milik role sendiri + role yang di-watch
     *
     * @return Collection<int> — kumpulan user_id yang visible
     */
    public static function getVisibleUserIds(User $user): Collection
    {
        // Admin bypass — bisa lihat semua data
        if ($user->hasRole('admin')) {
            return User::pluck('id');
        }

        $myRole = $user->roles->first();

        // Jika user belum punya role, hanya bisa lihat data sendiri
        if (!$myRole) {
            return collect([$user->id]);
        }

        // Ambil role_id yang boleh di-watch
        $watchedRoleIds = static::where('watcher_role_id', $myRole->id)
            ->pluck('watched_role_id');

        // Gabungkan dengan role sendiri
        $allVisibleRoleIds = $watchedRoleIds->push($myRole->id)->unique();

        // Ambil semua user_id dari role-role tersebut + diri sendiri
        return User::role($allVisibleRoleIds->toArray())
            ->pluck('id')
            ->push($user->id)
            ->unique();
    }

    /**
     * Ambil watched_role_ids untuk role tertentu.
     *
     * @return Collection<int> — kumpulan watched_role_id
     */
    public static function getWatchedRoleIds(int $roleId): Collection
    {
        return static::where('watcher_role_id', $roleId)
            ->pluck('watched_role_id');
    }

    /**
     * Sync visibility untuk sebuah role.
     * Hapus yang lama, insert yang baru.
     */
    public static function syncForRole(int $watcherRoleId, array $watchedRoleIds, ?int $userId = null): void
    {
        // Hapus semua visibility lama
        static::where('watcher_role_id', $watcherRoleId)->delete();

        // Insert yang baru
        $records = [];
        foreach ($watchedRoleIds as $watchedId) {
            $records[] = [
                'watcher_role_id' => $watcherRoleId,
                'watched_role_id' => $watchedId,
                'created_by' => $userId,
                'updated_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($records)) {
            static::insert($records);
        }
    }
}
