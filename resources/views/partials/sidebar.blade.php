<aside class="navbar navbar-vertical navbar-expand-lg bg-transparent">
    <div class="container-fluid">
        {{-- Logo / Brand --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <h1 class="navbar-brand navbar-brand-autodark">
            <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 text-body text-decoration-none">
                <i class="ti ti-wallet fs-2"></i>
                <span class="fw-bold" style="font-size: 1rem; line-height: 1.2">
                    Kas<br><small class="fw-normal text-secondary" style="font-size:0.75rem">Keluarga</small>
                </span>
            </a>
        </h1>

        {{-- User Info (mobile) --}}
        <div class="navbar-nav flex-row d-lg-none">
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                    <span class="avatar avatar-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">Profile</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">Logout</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Menu Items --}}
        <div class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav pt-lg-3">
                @foreach(config('menu.sidebar') as $item)
                    @if(isset($item['children']))
                        {{-- Check if user has permission to any child --}}
                        @php
                            $hasAccess = collect($item['children'])->some(
                                fn($child) => auth()->user()->can($child['permission'])
                            );
                        @endphp

                        @if($hasAccess)
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs(collect($item['children'])->pluck('route')->toArray()) ? 'active' : '' }}"
                               href="#sidebar-{{ Str::slug($item['label']) }}"
                               data-bs-toggle="collapse"
                               role="button"
                               aria-expanded="{{ request()->routeIs(collect($item['children'])->pluck('route')->toArray()) ? 'true' : 'false' }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="ti ti-{{ $item['icon'] }}"></i>
                                </span>
                                <span class="nav-link-title">{{ $item['label'] }}</span>
                            </a>
                            <div class="dropdown-menu {{ request()->routeIs(collect($item['children'])->pluck('route')->toArray()) ? 'show' : '' }}"
                                 id="sidebar-{{ Str::slug($item['label']) }}">
                                @foreach($item['children'] as $child)
                                    @can($child['permission'])
                                    <a href="{{ \Illuminate\Support\Facades\Route::has($child['route']) ? route($child['route']) : '#' }}"
                                       class="dropdown-item {{ request()->routeIs($child['route']) ? 'active' : '' }}">
                                        {{ $child['label'] }}
                                    </a>
                                    @endcan
                                @endforeach
                            </div>
                        </li>
                        @endif

                    @else
                        @can($item['permission'])
                        <li class="nav-item">
                            <a href="{{ \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#' }}"
                               class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="ti ti-{{ $item['icon'] }}"></i>
                                </span>
                                <span class="nav-link-title">{{ $item['label'] }}</span>
                            </a>
                        </li>
                        @endcan
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
</aside>
