@php $title = $kelas->nama_kelas; @endphp

@extends('layouts.app')

@section('content')
<div class="py-6">
    <x-back-link href="{{ route('pelajar.kelas.index') }}" :label="__('common.back')" class="min-h-11 min-w-11 px-2" />

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold">{{ $kelas->nama_kelas }}</h1>
            @if($kelas->kategori)<p class="text-sm text-slate-500 dark:text-slate-400">{{ __('siswa.kelas.kategori', ['kategori' => $kelas->kategori]) }}</p>@endif
            <p class="text-slate-500 dark:text-slate-400">{{ $kelas->deskripsi }}</p>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('siswa.kelas.guru', ['guru' => $kelas->guru?->name]) }}</p>
        </div>
        <form
            action="{{ route('pelajar.kelas.unenroll', $kelas) }}"
            method="POST"
            x-data
            x-on:submit.prevent="confirm('{{ __('siswa.kelas.konfirmasi_keluar') }}') && $el.submit()"
        >
            @csrf
            <x-btn-secondary type="submit" color="red" size="sm" class="min-h-11 min-w-11 justify-center">
                {{ __('siswa.kelas.keluar') }}
            </x-btn-secondary>
        </form>
    </div>

    <h2 class="text-lg font-semibold mt-4 mb-4">{{ __('siswa.kelas.daftar_pertemuan') }}</h2>

    <div class="space-y-3">
        @forelse($kelas->pertemuan->sortBy('urutan') as $p)
            <a
                href="{{ route('pelajar.pertemuan.show', $p) }}"
                class="block p-4 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 hover:border-emerald-300 hover:shadow-sm transition-all"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wide">{{ __('siswa.shared.pertemuan') }} {{ $p->urutan }}</span>
                        <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 mt-0.5">{{ $p->nama_pertemuan }}</h3>
                        @if($p->deskripsi)
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ Str::limit($p->deskripsi, 120) }}</p>
                        @endif
                    </div>
                    <svg class="w-5 h-5 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        @empty
            <p class="text-slate-500 dark:text-slate-400 text-center py-6">{{ __('siswa.kelas.belum_ada_pertemuan') }}</p>
        @endforelse
    </div>
</div>
@endsection
