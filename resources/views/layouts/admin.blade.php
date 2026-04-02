<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Sistem Keuangan Keluarga')) - {{ config('app.name') }}</title>

    {{-- Tabler via npm (bundled by Vite) --}}
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])

    @stack('styles')
</head>
<body class="antialiased h-full">
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
                                    &copy; {{ date('Y') }} <strong>{{ config('app.name', 'Sistem Keuangan Keluarga') }}</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>

        </div>
    </div>

    {{-- Tabler JS is bundled by Vite via admin.js --}}

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const html = document.documentElement;
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');

        function setTheme(theme) {
            html.setAttribute('data-bs-theme', theme);
            localStorage.setItem('theme', theme);
            if (themeIcon) {
                themeIcon.className = theme === 'dark' ? 'ti ti-sun' : 'ti ti-moon';
            }
        }

        const savedTheme = localStorage.getItem('theme');
        const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        if (savedTheme) {
            setTheme(savedTheme);
        } else {
            setTheme(systemDark ? 'dark' : 'light');
        }

        themeToggle?.addEventListener('click', function(e) {
            e.preventDefault();
            const currentTheme = html.getAttribute('data-bs-theme');
            setTheme(currentTheme === 'dark' ? 'light' : 'dark');
        });
    });
    </script>

    @stack('scripts')
</body>
</html>
