<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIMBA')</title>
    @include('components.theme-script')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    x-data="{ sidebarOpen: window.innerWidth >= 1024 }"
    class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 min-h-screen antialiased"
>
    @include('components.sidebar')

    <main class="min-h-screen flex flex-col transition-all duration-300" :class="sidebarOpen ? 'lg:ml-64' : ''">
        @include('components.topbar')

        <div class="flex-1 p-3 lg:p-6">
            @include('components.alert')
            @yield('content')
        </div>

        <footer class="text-center text-xs text-slate-500 dark:text-slate-400 py-3 border-t border-slate-100 dark:border-slate-800 mt-auto">
            &copy; {{ date('Y') }} {{ config('app.name', 'SIMBA') }}. {{ __('common.all_rights_reserved') }}
        </footer>
    </main>

    @stack('scripts')
</body>
</html>
