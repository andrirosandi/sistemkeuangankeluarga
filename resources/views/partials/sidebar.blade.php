<aside class="navbar navbar-vertical navbar-expand-lg">
    <div class="container-fluid px-0">

        {{-- BRAND --}}
        <div>
            <a href="{{ route('dashboard') }}"
               class="navbar-brand text-body text-decoration-none d-flex align-items-center px-3 m-0">

                <span class="nav-link-icon me-2 d-flex align-items-center justify-content-center">
                    @php
                        $media = \App\Models\Setting::where('key', 'app_logo')->first()?->getFirstMedia('app_logo');
                    @endphp

                    @if($media)
                        <img src="{{ $media->getUrl() }}" alt="Logo" style="max-height: 1.25rem;">
                    @else
                        <i class="ti ti-wallet text-primary"></i>
                    @endif
                </span>

                <span class="nav-link-title fw-bold small">
                    {{ \App\Models\Setting::get('app_name', config('app.name')) }}
                </span>
            </a>
        </div>

        {{-- MENU --}}
        <div class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav pt-3">

                @foreach(config('menu.sidebar') as $item)

                    {{-- ================= PARENT WITH CHILD ================= --}}
                    @if(isset($item['children']))
                        @php
                            $routes = collect($item['children'])->pluck('route')->toArray();
                            $isActive = request()->routeIs($routes);

                            $hasAccess = collect($item['children'])->contains(
                                fn($child) => auth()->user()->can($child['permission'])
                            );
                        @endphp

                        @if($hasAccess)
                        <li class="nav-item">

                            {{-- PARENT TOGGLE --}}
                            <a class="nav-link {{ $isActive ? 'active' : '' }}"
                               href="#"
                               data-bs-toggle="collapse"
                               data-bs-target="#sidebar-{{ Str::slug($item['label']) }}"
                               role="button"
                               aria-expanded="{{ $isActive ? 'true' : 'false' }}">

                                <span class="nav-link-icon">
                                    <i class="ti ti-{{ $item['icon'] }}"></i>
                                </span>
                                <span class="nav-link-title">
                                    {{ $item['label'] }}
                                </span>
                            </a>

                            {{-- CHILD MENU --}}
                            <div class="collapse {{ $isActive ? 'show' : '' }}"
                                 id="sidebar-{{ Str::slug($item['label']) }}"
                                 data-bs-parent="#sidebar-menu">

                                <ul class="nav nav-sm flex-column">

                                    @foreach($item['children'] as $child)
                                        @can($child['permission'])
                                        <li class="nav-item" style="padding-left: 44px !important">
                                            <a href="{{ Route::has($child['route']) ? route($child['route']) : '#' }}"
                                               class="nav-link {{ request()->routeIs($child['route']) ? 'active' : '' }}">
                                                {{ $child['label'] }}
                                            </a>
                                        </li>
                                        @endcan
                                    @endforeach

                                </ul>
                            </div>

                        </li>
                        @endif

                    {{-- ================= SINGLE MENU ================= --}}
                    @else
                        @can($item['permission'])
                        <li class="nav-item">
                            <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                               class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">

                                <span class="nav-link-icon">
                                    <i class="ti ti-{{ $item['icon'] }}"></i>
                                </span>

                                <span class="nav-link-title">
                                    {{ $item['label'] }}
                                </span>
                            </a>
                        </li>
                        @endcan
                    @endif

                @endforeach

            </ul>
        </div>

    </div>
</aside>