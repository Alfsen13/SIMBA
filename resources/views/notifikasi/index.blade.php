@php
    $role = auth()->user()->role;
@endphp

@extends('layouts.app')

@section('title', __('navigation.notifications') . ' - SIMBA')

@section('content')
    <div class="py-6 max-w-4xl mx-auto">
        <x-page-header :title="__('navigation.notifications')">
            <x-slot:actions>
                @if($hasUnread)
                    <form action="{{ route($role.'.notifikasi.read-all') }}" method="POST">
                        @csrf
                        <x-btn-secondary
                            type="submit"
                            size="sm"
                            color="indigo"
                            class="min-h-11"
                        >
                            {{ __('publik.notifikasi.tandai_semua') }}
                        </x-btn-secondary>
                    </form>
                @endif
            </x-slot:actions>
        </x-page-header>

        @if($notifikasi->isEmpty())
            <x-alert type="warning">{{ __('publik.notifikasi.kosong') }}</x-alert>
        @else
            <div class="space-y-2">
                @foreach($notifikasi as $n)
                    <x-card :padding="false">
                        <div class="p-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between {{ $n->is_read ? '' : 'border-l-4 border-emerald-500' }}">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold {{ $n->is_read ? 'text-slate-700 dark:text-slate-300' : 'text-emerald-800' }}">
                                        {{ $n->judul }}
                                    </h3>
                                    @if(!$n->is_read)
                                        <x-badge variant="baru">{{ __('publik.notifikasi.baru') }}</x-badge>
                                    @endif
                                </div>
                                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ $n->pesan }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    {{ $n->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2 sm:shrink-0">
                                @if($n->link)
                                    <x-btn-secondary :href="$n->link" size="sm" class="min-h-11">
                                        {{ __('publik.notifikasi.lihat') }}
                                    </x-btn-secondary>
                                @endif
                                @if(!$n->is_read)
                                    <form action="{{ route($role.'.notifikasi.read', $n) }}" method="POST">
                                        @csrf
                                        <x-btn-secondary type="submit" size="sm" class="min-h-11">
                                            {{ __('publik.notifikasi.tandai_dibaca') }}
                                        </x-btn-secondary>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </x-card>
                @endforeach
            </div>
            <div class="mt-4">
                {{ $notifikasi->links() }}
            </div>
        @endif
    </div>
@endsection
