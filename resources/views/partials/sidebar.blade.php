<aside class="navbar navbar-vertical navbar-expand-lg">
    <div class="container-fluid px-0">
        <div>
            <a href="{{ route('dashboard') }}" class="navbar-brand d-flex align-items-center gap-2 text-body text-decoration-none">
                @php
                    $logo = \App\Models\Setting::get('app_logo');
                    $media = $logo ? \Spatie\MediaLibrary\MediaCollections\Models\Media::where('file_name', $logo)->first() : null;
                @endphp
                @if($media)
                    <img src="{{ $media->getUrl() }}" alt="Logo" class="navbar-brand-image" style="height: 2rem; max-width: 2rem; object-fit: contain;">
                @else
                    <i class="ti ti-wallet fs-2 text-primary"></i>
                @endif
                <span class="fw-bold" style="font-size: 1rem; line-height: 1.2">
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
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs(collect($item['children'])->pluck('route')->toArray()) ? 'active' : '' }}"
                               href="#sidebar-{{ Str::slug($item['label']) }}"
                               data-bs-toggle="collapse"
                               role="button"
                               aria-expanded="{{ request()->routeIs(collect($item['children'])->pluck('route')->toArray()) ? 'true' : 'false' }}">
                                <span class="nav-link-icon">
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
