<?php

namespace App\Services;

use App\Models\RoleVisibility;
use App\Models\User;
use Illuminate\Support\Collection;

class ScopeService
{
    /**
     * Resolve scope berdasarkan permission user.
     * Dipakai oleh DashboardController dan ReportController.
     *
     * @param  User   $user
     * @param  string $requestedScope  'self', 'group', atau 'all'
     * @return array{Collection, string}  [userIds, resolvedScope]
     */
    public function resolveScope(User $user, string $requestedScope = 'self'): array
    {
        $scope = $requestedScope;

        // Enforce maximum allowed scope berdasarkan permission
        if ($scope === 'all' && !$user->can('dashboard.scope.all')) {
            $scope = $user->can('dashboard.scope.group') ? 'group' : 'self';
        }
        if ($scope === 'group' && !$user->can('dashboard.scope.group')) {
            $scope = 'self';
        }

        $userIds = match ($scope) {
            'all'   => User::pluck('id'),
            'group' => RoleVisibility::getVisibleUserIds($user),
            default => collect([$user->id]),
        };

        return [$userIds, $scope];
    }

    /**
     * Daftar scope yang tersedia untuk dropdown filter.
     * Bisa disesuaikan dengan context (dashboard vs report).
     *
     * @param  User   $user
     * @param  bool   $reportContext  Jika true, cek report.view permission juga
     * @return array
     */
    public function buildAvailableScopes(User $user, bool $reportContext = false): array
    {
        // Untuk report: jika user hanya punya report.view.self, paksa self saja
        if ($reportContext && !$user->can('report.view')) {
            return [['value' => 'self', 'label' => 'Diri Sendiri']];
        }

        $scopes = [];

        if ($user->can('dashboard.scope.self')) {
            $scopes[] = ['value' => 'self', 'label' => 'Diri Sendiri'];
        }
        if ($user->can('dashboard.scope.group')) {
            $scopes[] = ['value' => 'group', 'label' => 'Grup'];
        }
        if ($user->can('dashboard.scope.all')) {
            $scopes[] = ['value' => 'all', 'label' => 'Semua'];
        }

        return $scopes;
    }

    /**
     * Resolve scope khusus report — memperhitungkan report.view permission.
     *
     * @param  User   $user
     * @param  string $requestedScope
     * @return array{Collection, string}
     */
    public function resolveScopeForReport(User $user, string $requestedScope = 'self'): array
    {
        // Jika user hanya punya report.view.self, paksa scope self
        if (!$user->can('report.view') && $user->can('report.view.self')) {
            return [collect([$user->id]), 'self'];
        }

        return $this->resolveScope($user, $requestedScope);
    }
}
