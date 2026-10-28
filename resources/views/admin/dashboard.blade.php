@extends('layouts.app')

@section('title', __('operator.title_dashboard_admin') . ' - SIMBA')

@section('content')
<div>
    {{-- Welcome Banner --}}
    <div class="relative overflow-hidden rounded-lg bg-gradient-to-br from-emerald-600 via-emerald-700 to-teal-800 px-5 py-4 mb-4">
        <div class="absolute top-0 right-0 w-48 h-48 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
        <div class="relative">
            <p class="text-emerald-200 text-xs font-medium">{{ __('operator.dashboard_admin') }}</p>
            <h1 class="text-xl font-bold text-white mt-0.5">{{ __('operator.welcome', ['name' => auth()->user()->name]) }} 👋</h1>
            <p class="text-emerald-100/90 text-xs mt-1 max-w-2xl">
                {{ __('operator.dashboard_subtitle_admin') }}
            </p>
        </div>
    </div>

    {{-- 4 Main Management Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        {{-- 1. Kelola Kelas --}}
        <a href="{{ route('operator.kelas.index') }}" class="group relative overflow-hidden rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 hover:border-emerald-300 dark:hover:border-emerald-700 hover:shadow-md transition-all">
            <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 dark:bg-emerald-600/10 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:bg-emerald-100 dark:group-hover:bg-emerald-600/20 transition-colors"></div>
            <div class="relative">
                <div class="w-11 h-11 bg-emerald-50 dark:bg-emerald-600/20 rounded-lg flex items-center justify-center group-hover:bg-emerald-100 dark:group-hover:bg-emerald-600/30 transition-colors mb-3">
                    <svg class="w-5 h-5 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">{{ __('operator.card_classes_title') }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('operator.card_classes_desc', ['total' => $stats['total_kelas']]) }}</p>
                <div class="mt-3 flex items-center gap-1.5 text-xs font-medium text-emerald-700 dark:text-emerald-400">
                    <span>{{ __('operator.card_classes_cta') }}</span>
                    <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </div>
        </a>

        {{-- 2. Verifikasi Akun --}}
        <a href="{{ route('operator.users.index', ['status' => 'pending']) }}" class="group relative overflow-hidden rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 hover:border-amber-300 dark:hover:border-amber-700 hover:shadow-md transition-all">
            <div class="absolute top-0 right-0 w-24 h-24 bg-amber-50 dark:bg-amber-500/10 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:bg-amber-100 dark:group-hover:bg-amber-500/20 transition-colors"></div>
            <div class="relative">
                <div class="w-11 h-11 bg-amber-50 dark:bg-amber-500/20 rounded-lg flex items-center justify-center group-hover:bg-amber-100 dark:group-hover:bg-amber-500/30 transition-colors mb-3">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100 group-hover:text-amber-700 dark:group-hover:text-amber-400 transition-colors">{{ __('operator.card_verify_title') }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('operator.card_verify_desc', ['pending' => $stats['pending_users']]) }}</p>
                <div class="mt-3 flex items-center gap-1.5 text-xs font-medium text-amber-600 dark:text-amber-400">
                    <span>{{ __('operator.card_verify_cta') }}</span>
                    <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </div>
        </a>

        {{-- 3. Dokumen RAG --}}
        <a href="{{ route('operator.documents.index') }}" class="group relative overflow-hidden rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 hover:border-purple-300 dark:hover:border-purple-700 hover:shadow-md transition-all">
            <div class="absolute top-0 right-0 w-24 h-24 bg-purple-50 dark:bg-purple-500/10 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:bg-purple-100 dark:group-hover:bg-purple-500/20 transition-colors"></div>
            <div class="relative">
                <div class="w-11 h-11 bg-purple-50 dark:bg-purple-500/20 rounded-lg flex items-center justify-center group-hover:bg-purple-100 dark:group-hover:bg-purple-500/30 transition-colors mb-3">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100 group-hover:text-purple-700 dark:group-hover:text-purple-400 transition-colors">{{ __('operator.card_documents_title') }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('operator.card_documents_desc') }}</p>
                <div class="mt-3 flex items-center gap-1.5 text-xs font-medium text-purple-600 dark:text-purple-400">
                    <span>{{ __('operator.card_documents_cta') }}</span>
                    <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </div>
        </a>

        {{-- 4. Berita --}}
        <a href="{{ route('operator.berita.index') }}" class="group relative overflow-hidden rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 hover:border-emerald-300 dark:hover:border-emerald-700 hover:shadow-md transition-all">
            <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 dark:bg-emerald-600/10 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:bg-emerald-100 dark:group-hover:bg-emerald-600/20 transition-colors"></div>
            <div class="relative">
                <div class="w-11 h-11 bg-emerald-50 dark:bg-emerald-600/20 rounded-lg flex items-center justify-center group-hover:bg-emerald-100 dark:group-hover:bg-emerald-600/30 transition-colors mb-3">
                    <svg class="w-5 h-5 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                </div>
                <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">{{ __('operator.card_news_title') }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('operator.card_news_desc') }}</p>
                <div class="mt-3 flex items-center gap-1.5 text-xs font-medium text-emerald-700 dark:text-emerald-400">
                    <span>{{ __('operator.card_news_cta') }}</span>
                    <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </div>
        </a>
    </div>

    {{-- Stats ringkas --}}
    <div class="mt-4 grid grid-cols-2 lg:grid-cols-6 gap-3">
        <x-stat-card :value="$stats['total_users']" :label="__('operator.stat_total_users')" color="slate" />
        <x-stat-card :value="$stats['total_pengajar']" :label="__('operator.stat_pengajar')" color="emerald" />
        <x-stat-card :value="$stats['total_pelajar']" :label="__('operator.stat_pelajar')" color="blue" />
        <x-stat-card :value="$stats['pending_users']" :label="__('operator.stat_pending')" color="amber" />
        <x-stat-card :value="$stats['total_kelas']" :label="__('operator.stat_kelas')" color="purple" />
        <x-stat-card :value="$stats['berisiko_count']" :label="__('operator.stat_berisiko')" color="red" />
    </div>

    {{-- Activity Bottom --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
        <x-card>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ __('operator.recent_notifications') }}</h2>
                <a href="{{ route('operator.notifikasi') }}" class="text-xs text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 font-medium">{{ __('operator.view_all') }} &rarr;</a>
            </div>
            @if($recentNotifications->count())
                <div class="space-y-1">
                    @foreach($recentNotifications as $notif)
                        <div class="flex items-start gap-3 p-2.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                {{ $notif->tipe === 'info' ? 'bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400' : '' }}
                                {{ $notif->tipe === 'warning' ? 'bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400' : '' }}
                                {{ $notif->tipe === 'success' ? 'bg-emerald-100 dark:bg-emerald-600/20 text-emerald-700 dark:text-emerald-400' : '' }}
                                {{ $notif->tipe === 'danger' ? 'bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400' : '' }}
                                {{ !in_array($notif->tipe, ['info','warning','success','danger']) ? 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' : '' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">{{ $notif->judul }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $notif->pesan }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $notif->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6">
                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('operator.empty_notifications') }}</p>
                </div>
            @endif
        </x-card>

        <x-card>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ __('operator.recent_registrations') }}</h2>
                <a href="{{ route('operator.users.index') }}" class="text-xs text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 font-medium">{{ __('operator.view_all') }} &rarr;</a>
            </div>
            @if($recentUsers->count())
                <div class="space-y-1">
                    @foreach($recentUsers as $user)
                        <div class="flex items-center justify-between p-2.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-100 to-emerald-200 dark:from-emerald-500/30 dark:to-emerald-500/10 flex items-center justify-center text-sm font-semibold text-emerald-700 dark:text-emerald-300 shrink-0">{{ substr($user->name, 0, 1) }}</div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $user->email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0 ml-3">
                                <x-badge :variant="$user->role" :label="ucfirst($user->role)" />
                                <x-badge :variant="$user->status === 'verified' ? 'verified' : ($user->status === 'rejected' ? 'danger' : 'pending')" :label="$user->status === 'verified' ? __('operator.status_verified_label') : ucfirst($user->status)" />
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6">
                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('operator.empty_registrations') }}</p>
                </div>
            @endif
        </x-card>
    </div>
</div>
@endsection
