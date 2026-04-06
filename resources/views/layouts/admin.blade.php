<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php 
        $appName = \App\Models\Setting::get('app_name', config('app.name')); 
        $faviconUrl = \App\Models\Setting::where('key', 'app_favicon')->first()?->getFirstMediaUrl('app_favicon');
    @endphp
    <title>@yield('title', $appName) - {{ $appName }}</title>

    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}" type="image/webp">
    @endif

    {{-- Tabler via npm (bundled by Vite) --}}
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])

    {{-- Disable HTMX Boost cache to prevent stale HTML on deployed server --}}
    <meta name="htmx-config" content='{"useCache": false}'>

    <style>
        /* HTMX Loading Indicator */
        .htmx-indicator {
            opacity: 0;
            transition: opacity 200ms ease-in;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: #206bc4; /* Tabler primary color */
            z-index: 9999;
            pointer-events: none;
        }
        .htmx-request .htmx-indicator,
        .htmx-request.htmx-indicator {
            opacity: 1;
            /* Simple animated loading bar effect */
            animation: htmx-loading 2s linear infinite;
        }
        @keyframes htmx-loading {
            0% { width: 0%; opacity: 1; }
            50% { width: 50%; opacity: 1; }
            100% { width: 100%; opacity: 0; }
        }
    </style>

    @stack('styles')
</head>
<body class="antialiased h-full">
    <div class="htmx-indicator"></div>
    <div class="wrapper">

        {{-- Sidebar --}}
        @include('partials.sidebar')

        <div class="page-wrapper">

            {{-- Navbar --}}
            @include('partials.navbar')

            {{-- Page Content --}}
            <div class="page-body">
                <div class="container-fluid">

                    {{-- Page Header --}}
                    @hasSection('page-header')
                    <div class="page-header d-print-none">
                        <div class="row align-items-center">
                            <div class="col">
                                @yield('page-header')
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Flash Messages --}}
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <div class="d-flex">
                            <div><i class="ti ti-circle-check me-2"></i></div>
                            <div>{{ session('success') }}</div>
                        </div>
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <div class="d-flex">
                            <div><i class="ti ti-alert-circle me-2"></i></div>
                            <div>{{ session('error') }}</div>
                        </div>
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                    @endif

                    {{-- Main Content --}}
                    @yield('content')

                </div>
            </div>

            {{-- Footer --}}
            <footer class="footer footer-transparent d-print-none">
                <div class="container-fluid">
                    <div class="row text-center align-items-center flex-row-reverse">
                        <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                            <ul class="list-inline list-inline-dots mb-0">
                                <li class="list-inline-item">
                                    &copy; {{ date('Y') }} <strong>{{ \App\Models\Setting::get('app_name', config('app.name')) }}</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>

        </div>
    </div>

    {{-- Tabler JS is bundled by Vite via admin.js --}}

    @stack('scripts')
</body>
</html>
