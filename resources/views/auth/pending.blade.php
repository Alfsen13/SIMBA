@extends('layouts.guest')

@section('title', __('auth.pending_title') . ' - SIMBA')

@section('content')
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 text-center">
        <div class="w-14 h-14 rounded-lg bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center mx-auto mb-4">
            <svg
                class="w-8 h-8 text-amber-600 dark:text-amber-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                />
            </svg>
        </div>

        <h1 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
            {{ __('auth.pending_title') }}
        </h1>
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-4 leading-relaxed max-w-xs mx-auto">
            {{ __('auth.pending_message') }}
        </p>

        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3.5 mb-4 border border-amber-200/50 dark:border-amber-700/50">
            <div class="flex items-start gap-3 text-left">
                <svg
                    class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
                <div>
                    <p class="text-sm font-medium text-amber-800 dark:text-amber-300">
                        {{ __('auth.pending_verifying') }}
                    </p>
                    <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">
                        {{ __('auth.pending_contact') }}
                    </p>
                </div>
            </div>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <x-btn-secondary type="submit" color="gray">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                {{ __('common.logout') }}
            </x-btn-secondary>
        </form>
    </div>
@endsection
