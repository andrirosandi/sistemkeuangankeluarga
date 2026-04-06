<aside class="navbar navbar-vertical navbar-expand-lg">
    <div class="container-fluid px-0">
        <div>
            <a href="{{ route('dashboard') }}" class="navbar-brand text-body text-decoration-none" style="padding-left: 1rem !important; margin: 0 !important; display: flex; align-items: center;">
                <span class="nav-link-icon d-flex align-items-center justify-content-center" style="margin-right: 0.5rem;">
                    @php
                        $media = \App\Models\Setting::where('key', 'app_logo')->first()?->getFirstMedia('app_logo');
                    @endphp
                    @if($media)
                        <img src="{{ $media->getUrl() }}" alt="Logo" style="max-height: 1.25rem; max-width: 1.25rem; object-fit: contain;">
                    @else
                        <i class="ti ti-wallet text-primary" style="font-size: 1.25rem;"></i>
                    @endif
                </span>
                <span class="nav-link-title fw-bold" style="font-size: 0.95rem; line-height: 1.2">
                    {{ \App\Models\Setting::get('app_name', config('app.name')) }}
                </span>
            </a>
        </div>

        {{-- Menu Items --}}
        <div class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav pt-3">
                @foreach(config('menu.sidebar') as $item)
                    @if(isset($item['children']))
                        {{-- Check if user has permission to any child --}}
                        @php
                            $hasAccess = collect($item['children'])->some(
                                fn($child) => auth()->user()->can($child['permission'])
                            );
                        @endphp

                        @if($hasAccess)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs(collect($item['children'])->pluck('route')->toArray()) ? 'active' : '' }}"
                               href="#"
                               data-bs-toggle="collapse"
                               data-bs-target="#sidebar-{{ Str::slug($item['label']) }}"
                               hx-boost="false"
                               role="button"
                               aria-expanded="{{ request()->routeIs(collect($item['children'])->pluck('route')->toArray()) ? 'true' : 'false' }}">
                                <span class="nav-link-icon">
                                    <i class="ti ti-{{ $item['icon'] }}"></i>
                                </span>
                                <span class="nav-link-title">{{ $item['label'] }}</span>
                            </a>
                            <div class="collapse {{ request()->routeIs(collect($item['children'])->pluck('route')->toArray()) ? 'show' : '' }} ms-3"
                                 id="sidebar-{{ Str::slug($item['label']) }}"
                                 data-bs-parent="#sidebar-menu">
                                <ul class="nav nav-sm flex-column">
                                    @foreach($item['children'] as $child)
                                        @can($child['permission'])
                                        <li class="nav-item">
                                            <a href="{{ \Illuminate\Support\Facades\Route::has($child['route']) ? route($child['route']) : '#' }}"
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

                    @else
                        @can($item['permission'])
                        <li class="nav-item">
                            <a href="{{ \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#' }}"
                               class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                                <span class="nav-link-icon">
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
