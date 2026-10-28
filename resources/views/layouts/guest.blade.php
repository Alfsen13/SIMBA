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
    class="min-h-screen flex items-center justify-center p-4 sm:p-6 bg-slate-100 dark:bg-slate-950 antialiased"
>
    {{-- Aksen brand statis di tepi atas halaman publik (login/register/pending). --}}
    <div class="fixed top-0 inset-x-0 h-1 bg-emerald-700 dark:bg-emerald-500 z-30 pointer-events-none" aria-hidden="true"></div>

    <div class="fixed top-4 right-4 z-20 flex items-center gap-1">
        <x-theme-toggle />
        <x-locale-switcher />
    </div>

    @php $containerWidth = trim($__env->yieldContent('container_width')); @endphp
    <div class="relative w-full {{ $containerWidth !== '' ? $containerWidth : 'max-w-md' }} py-8">
        {{-- Logo / Brand --}}
        <div class="text-center mb-6">
            <a
                href="/"
                class="inline-flex items-center gap-2.5"
            >
                <div class="w-10 h-10 rounded-lg bg-emerald-700 dark:bg-emerald-600 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                    S
                </div>
                <span class="font-bold text-xl text-slate-900 dark:text-white">
                    {{ config('app.name', 'SIMBA') }}
                </span>
            </a>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">
                {{ __('common.app_tagline') }}
            </p>
        </div>

        @include('components.alert')

        @yield('content')
    </div>

    @stack('scripts')
</body>
