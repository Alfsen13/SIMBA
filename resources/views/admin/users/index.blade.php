@extends('layouts.app')

@section('title', __('operator.title_users') . ' - SIMBA')

@section('content')
    <x-page-header
        :title="__('operator.users_page_title')"
        :subtitle="__('operator.users_subtitle')"
    />

    <x-card :padding="false">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <form
                method="GET"
                class="relative w-full sm:w-72"
            >
                <x-form-input
                    name="search"
                    type="text"
                    :label="__('operator.users_search_placeholder')"
                    :placeholder="__('operator.users_search_placeholder')"
                    :value="request('search')"
                    icon='<svg class="w-4.5 h-4.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>'
                />
            </form>

                    <div class="flex gap-1 bg-slate-100 dark:bg-slate-800 rounded-lg p-1 text-sm">
                <a
                    href="{{ route('operator.users.index') }}"
                    class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ !request('status') ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-sm' : 'text-slate-600 hover:text-slate-900 dark:hover:text-white' }}"
                >
                    {{ __('common.all') }}
                </a>
                <a
                    href="{{ route('operator.users.index', ['status' => 'pending']) }}"
                    class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ request('status') === 'pending' ? 'bg-white dark:bg-slate-900 text-amber-700 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}"
                >
                    {{ __('operator.filter_pending') }}
                </a>
                <a
                    href="{{ route('operator.users.index', ['status' => 'verified']) }}"
                    class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ request('status') === 'verified' ? 'bg-white dark:bg-slate-900 text-green-700 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}"
                >
                    {{ __('operator.filter_verified') }}
                </a>
                <a
                    href="{{ route('operator.users.index', ['status' => 'rejected']) }}"
                    class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ request('status') === 'rejected' ? 'bg-white dark:bg-slate-900 text-red-700 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}"
                >
                    {{ __('operator.filter_rejected') }}
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-900/30">
                        <x-th align="left">{{ __('operator.th_user') }}</x-th>
                        <x-th align="left">{{ __('operator.th_email') }}</x-th>
                        <x-th align="left">{{ __('operator.th_role') }}</x-th>
                        <x-th align="left">{{ __('common.status') }}</x-th>
                        <x-th align="right">{{ __('common.actions') }}</x-th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    @foreach($users as $user)
                        <x-tr>
                            <x-td>
                                <a
                                    href="{{ route('operator.users.show', $user) }}"
                                    class="flex items-center gap-3 group"
                                >
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-emerald-100 to-emerald-200 flex items-center justify-center text-sm font-semibold text-emerald-700 dark:text-emerald-300 shrink-0">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-900 dark:text-slate-100 group-hover:text-emerald-700 transition-colors">{{ $user->name }}</p>
                                        @if($user->nip || $user->nisn)
                                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $user->nip ?? $user->nisn }}</p>
                                        @endif
                                    </div>
                                </a>
                            </x-td>
                            <x-td>{{ $user->email }}</x-td>
                            <x-td>
                                <x-badge
                                    :variant="$user->role"
                                    :dot="true"
                                    :label="ucfirst($user->role)"
                                />
                            </x-td>
                            <x-td>
                                <x-badge
                                    :variant="$user->status"
                                    :dot="true"
                                    :label="ucfirst($user->status)"
                                />
                            </x-td>
                            <x-td align="right">
                                <div class="flex items-center justify-end gap-1">
                                    <a
                                        href="{{ route('operator.users.show', $user) }}"
                                        class="p-2 text-slate-500 dark:text-slate-400 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-600/10 rounded-lg transition-colors"
                                        title="{{ __('common.detail') }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    @if($user->status === 'pending')
                                        <form
                                            action="{{ route('operator.users.verify', $user) }}"
                                            method="POST"
                                            class="inline"
                                        >
                                            @csrf
                                            <button
                                                class="p-2 text-slate-500 dark:text-slate-400 hover:text-green-700 hover:bg-green-50 rounded-lg transition-colors"
                                                title="{{ __('operator.title_verify') }}"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                        </form>
                                        <form
                                            action="{{ route('operator.users.reject', $user) }}"
                                            method="POST"
                                            class="inline"
                                        >
                                            @csrf
                                            <button
                                                class="p-2 text-slate-500 dark:text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors"
                                                title="{{ __('operator.title_reject') }}"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if($user->id !== auth()->id())
                                        <form
                                            action="{{ route('operator.users.destroy', $user) }}"
                                            method="POST"
                                            class="inline"
                                            onsubmit="return confirm('Yakin ingin menghapus akun {{ addslashes($user->name) }}?');"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                class="p-2 text-slate-500 dark:text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors"
                                                title="Hapus Akun"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </x-td>
                        </x-tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-4 py-4 border-t border-slate-100 dark:border-slate-800">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @endif
    </x-card>
@endsection
