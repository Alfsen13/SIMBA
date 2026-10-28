@php
    $role = auth()->user()->role;
    $title = 'Notifikasi';
@endphp

@extends('layouts.app')

@section('content')
    <div class="py-6 max-w-4xl mx-auto">
        <x-page-header :title="__('navigation.notifications')">
            <x-slot:actions>
                @if($notifikasi->where('is_read', false)->count() > 0)
                    <form action="{{ route($role.'.notifikasi.read-all') }}" method="POST">
                        @csrf
                        <x-btn-secondary
                            type="submit"
                            size="sm"
                            color="indigo"
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
                        <div
                            class="p-4 flex items-start justify-between {{ $n->is_read ? '' : 'border-l-4 border-emerald-500' }}"
                        >
                            <div class="flex-1">
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
                            @if($n->link)
                                <a href="{{ $n->link }}" class="ml-2 shrink-0">
                                    <x-btn-secondary type="button" size="sm">
                                        {{ __('publik.notifikasi.lihat') }}
                                    </x-btn-secondary>
                                </a>
                            @endif
                            @if(!$n->is_read)
                                <form
                                    action="{{ route($role.'.notifikasi.read', $n) }}"
                                    method="POST"
                                    class="ml-2"
                                >
                                    @csrf
                                    <x-btn-secondary type="submit" size="sm">
                                        {{ __('publik.notifikasi.tandai_dibaca') }}
                                    </x-btn-secondary>
                                </form>
                            @endif
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
