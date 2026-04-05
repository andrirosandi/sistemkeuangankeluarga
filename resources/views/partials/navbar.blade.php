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

            {{-- Notification Bell Dropdown --}}
            <div class="nav-item me-2 dropdown" x-data="{ open: false }" @click.outside="open = false">
                @php 
                    $unreadCount = auth()->user()->notifications()->where('is_read', false)->count(); 
                    $recentNotifications = auth()->user()->notifications()->orderBy('created_at', 'desc')->take(5)->get();
                @endphp
                <a href="#" class="nav-link px-0 position-relative" title="Notifikasi" @click.prevent="open = !open">
                    <i class="ti ti-bell fs-3"></i>
                    @if($unreadCount > 0)
                        <span class="badge bg-red badge-notification badge-blink">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card shadow-lg" 
                     :class="{ 'show': open }" 
                     x-show="open" 
                     x-transition 
                     style="display: none; position: absolute; right: 0; top: 100%; width: 320px; z-index: 1050;">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                            <h3 class="card-title mb-0 fs-5">Notifikasi</h3>
                            @if($unreadCount > 0)
                                <form action="{{ route('notification.readAll') }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-link link-primary p-0 fs-5 text-decoration-none" title="Tandai semua dibaca">BACA SEMUA</button>
                                </form>
                            @endif
                        </div>
                        <div class="list-group list-group-flush list-group-hoverable" style="max-height: 350px; overflow-y: auto;">
                            @forelse($recentNotifications as $notif)
                                <a href="{{ $notif->getRedirectUrl() }}" class="list-group-item list-group-item-action text-decoration-none">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            @if(!$notif->is_read)
                                                <span class="status-indicator status-blue status-indicator-animated">
                                                    <span class="status-indicator-circle"></span>
                                                    <span class="status-indicator-circle"></span>
                                                    <span class="status-indicator-circle"></span>
                                                </span>
                                            @else
                                                <span class="status-indicator status-green"></span>
                                            @endif
                                        </div>
                                        <div class="col text-truncate">
                                            <div class="text-wrap {{ !$notif->is_read ? 'fw-bold text-body' : 'text-muted' }}" style="font-size: 13px;">
                                                {!! $notif->message !!}
                                            </div>
                                            <div class="text-secondary mt-1" style="font-size: 11px;">{{ $notif->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="list-group-item text-center py-4 text-muted">
                                    Belum ada notifikasi baru
                                </div>
                            @endforelse
                        </div>
                        <div class="card-footer py-2 text-center">
                            <a href="{{ route('notification.index') }}" class="btn btn-link link-secondary fs-5 text-decoration-none">Lihat Semua Notifikasi</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Theme Toggle --}}
            <div class="nav-item me-2">
                <a href="#" class="nav-link px-0" title="Ganti Tema" id="theme-toggle">
                    <i class="ti ti-moon fs-3" id="theme-icon"></i>
                </a>
            </div>

            {{-- User Dropdown --}}
            <div class="nav-item dropdown" x-data="{ open: false }" @click.away="open = false">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" id="nav-user-menu" @click.prevent="open = !open">
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
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow" 
                     :class="{ 'show': open }"
                     x-show="open"
                     style="position: absolute; right: 0; top: 100%; margin-top: 10px; left: auto;"
                     @click.away="open = false">
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
