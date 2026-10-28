@extends('layouts.guest')

@section('title', __('auth.login_button') . ' - SIMBA')

@section('content')
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
        <div class="text-center mb-5">
            <h1 class="text-lg font-semibold text-slate-900 dark:text-white">
                {{ __('auth.login_title') }}
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                {{ __('auth.login_subtitle') }}
            </p>
        </div>

        <form
            action="{{ route('login') }}"
            method="POST"
            class="space-y-4"
        >
            @csrf

            <x-form-input
                name="email"
                type="email"
                label="Email"
                value="{{ old('email') }}"
                placeholder="nama@email.com"
                required
                icon='<svg class="w-4.5 h-4.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>'
            />

            <x-form-password
                name="password"
                :label="__('auth.password_label')"
                :placeholder="__('auth.password_placeholder')"
                required
            />

            <label class="flex items-center gap-2 cursor-pointer w-fit">
                <input
                    type="checkbox"
                    name="remember"
                    class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-emerald-700 dark:text-emerald-400 focus:ring-emerald-500/20 dark:bg-slate-800"
                >
                <span class="text-sm text-slate-600 dark:text-slate-400">{{ __('auth.remember') }}</span>
            </label>

            <x-btn-primary type="submit" class="w-full">
                {{ __('auth.login_button') }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
            </x-btn-primary>
        </form>

        <div class="border-t border-slate-200 dark:border-slate-800 my-5"></div>

        <p class="text-center text-sm text-slate-500 dark:text-slate-400">
            {{ __('auth.no_account') }}
            <a
                href="{{ route('register') }}"
                class="font-semibold text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 transition"
            >
                {{ __('auth.register_link') }}
            </a>
        </p>
    </div>
@endsection
