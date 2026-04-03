<header class="navbar navbar-expand-lg d-print-none">
    <div class="container-fluid">

        <div class="d-flex align-items-center gap-2">
            {{-- Hamburger Menu (tablet dan mobile) --}}
            <button class="navbar-toggler p-0 border-0 d-lg-none" type="button" id="mobile-menu-toggle" style="background: transparent; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                <i class="ti ti-menu-2" style="font-size: 24px;"></i>
            </button>

            {{-- Page Title --}}
            {{-- Breadcrumb (Desktop) dan Module Title (Mobile) --}}
            @php
                $segments = Request::segments();
                $currentTitle = $__env->yieldContent('page-title') ?: $__env->yieldContent('title') ?: 'Dashboard';
            @endphp
            
            <nav class="d-none d-md-flex" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-dots py-0 m-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" class="text-secondary d-flex align-items-center">
                            <i class="ti ti-home me-1"></i> Home
                        </a>
                    </li>
                    @php $link = ''; @endphp
                    @foreach($segments as $index => $segment)
                        @php $link .= '/' . $segment; @endphp
                        @if($index + 1 < count($segments))
                            <li class="breadcrumb-item text-secondary">
                                {{ ucfirst(str_replace('-', ' ', $segment)) }}
                            </li>
                        @else
                            <li class="breadcrumb-item active fw-bold" aria-current="page">
                                <span>{{ $currentTitle }}</span>
                            </li>
                        @endif
                    @endforeach
                </ol>
            </nav>

            <div class="d-md-none">
                <span class="fw-bold text-body">{{ $currentTitle }}</span>
            </div>
        </div>

        <div class="navbar-nav flex-row order-lg-last">

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
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" id="nav-user-menu">
                    @php
                        $avatarUrl = auth()->user()->getFirstMediaUrl('avatars', 'thumb');
                    @endphp
                    @if($avatarUrl)
                        <span class="avatar avatar-sm rounded" style="background-image: url('{{ $avatarUrl }}')"></span>
                    @else
                        <span class="avatar avatar-sm rounded"
                              style="background: linear-gradient(135deg, #0d9488, #14b8a6);">
                            <span class="fw-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                        </span>
                    @endif
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
