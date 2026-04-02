<header class="navbar navbar-expand-md d-print-none">
    <div class="container-fluid">

        {{-- Mobile toggle --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        {{-- Page Title (from @section) --}}
        <div class="navbar-brand me-0 me-md-3 d-none d-md-flex align-items-center gap-2">
            <span class="fw-semibold text-body">@yield('page-title', 'Dashboard')</span>
        </div>

        <div class="navbar-nav flex-row order-md-last">

            {{-- Notification Bell --}}
            <div class="nav-item me-2">
                <a href="{{ route('notification.index') }}" class="nav-link px-0 position-relative" title="Notifikasi" id="nav-notification">
                    <i class="ti ti-bell fs-3"></i>
                    @php $unread = auth()->user()->notifications()->where('is_read', false)->count(); @endphp
                    @if($unread > 0)
                        <span class="badge bg-red badge-notification">{{ $unread > 9 ? '9+' : $unread }}</span>
                    @endif
                </a>
            </div>

            {{-- Theme Toggle --}}
            <div class="nav-item me-2">
                <a href="#" class="nav-link px-0" title="Ganti Tema" id="theme-toggle">
                    <i class="ti ti-moon fs-3" id="theme-icon"></i>
                </a>
            </div>

            {{-- User Dropdown --}}
            <div class="nav-item dropdown">
                <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" id="nav-user-menu">
                    <span class="avatar avatar-sm rounded"
                          style="background: linear-gradient(135deg, #0d9488, #14b8a6);">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <div class="dropdown-item-text py-2 px-3">
                        <div class="fw-medium" style="font-size:0.9rem">{{ auth()->user()->name }}</div>
                        <div class="text-secondary" style="font-size:0.75rem">
                            <span class="badge bg-teal-lt text-teal" style="font-size:0.65rem; padding: 2px 6px;">
                                {{ ucfirst(auth()->user()->getRoleNames()->first() ?? 'user') }}
                            </span>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('profile.edit') }}" class="dropdown-item" id="nav-profile-link">
                        <i class="ti ti-user me-2"></i> Profile Settings
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger" id="nav-logout-btn">
                            <i class="ti ti-logout me-2"></i> Logout
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</header>
