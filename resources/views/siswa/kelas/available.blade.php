@php $title = __('siswa.kelas.cari_kelas'); @endphp

@extends('layouts.app')

@section('content')
<div class="py-6">
    <x-back-link href="{{ route('pelajar.kelas.index') }}" :label="__('siswa.kelas.title')" class="min-h-11 min-w-11 px-2" />

    <x-page-header :title="__('siswa.kelas.cari_kelas')" />

    <form
        method="GET"
        action="{{ route('pelajar.kelas.available') }}"
        class="mb-4 grid grid-cols-1 gap-2 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end"
    >
        <label for="search" class="block text-xs font-semibold text-slate-600 dark:text-slate-300">
            {{ __('siswa.kelas.cari_label') }}
            <input
                type="search"
                name="search"
                id="search"
                value="{{ request('search') }}"
                placeholder="{{ __('siswa.kelas.cari_placeholder') }}"
                class="mt-1 min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus-visible:outline-emerald-300"
            >
        </label>
        <x-btn-primary type="submit" size="sm" class="min-h-11 min-w-11 justify-center">
            {{ __('siswa.kelas.cari_action') }}
        </x-btn-primary>
    </form>

    @if($hanyaTerbuka)
        <div
            class="flex items-start gap-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg px-3 py-2.5 mb-4 text-sm"
            role="note"
        >
            <svg class="w-5 h-5 text-emerald-700 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="flex-1 text-emerald-800 dark:text-emerald-300">{{ __('siswa.kelas.info_belajar_mandiri') }}</span>
        </div>
    @endif

    @if($availableKelas->isEmpty())
        <x-empty-state
            :message="__('siswa.kelas.tidak_tersedia')"
            action="{{ route('pelajar.kelas.index') }}"
            :actionLabel="__('siswa.kelas.lihat_kelas_saya')"
        />
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($availableKelas as $kelas)
                <x-card padding="true" class="border-l-4 border-green-500">
                    <h3 class="font-semibold text-lg">{{ $kelas->nama_kelas }}</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('siswa.kelas.kode', ['kode' => $kelas->kode_kelas]) }}</p>
                    @if($kelas->kategori)<p class="text-sm text-slate-500 dark:text-slate-400">{{ __('siswa.kelas.kategori', ['kategori' => $kelas->kategori]) }}</p>@endif
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('siswa.kelas.guru', ['guru' => $kelas->guru?->name]) }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $kelas->deskripsi }}</p>
                    <form
                        action="{{ route('pelajar.kelas.enroll', $kelas) }}"
                        method="POST"
                        class="mt-3"
                    >
                        @csrf
                        <x-btn-primary type="submit" class="min-h-11 w-full">
                            {{ __('siswa.kelas.arkan_pendaftaran') }}
                        </x-btn-primary>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400 text-center">
                            {{ __('siswa.kelas.catatan_verifikasi') }}
                        </p>
                    </form>
                </x-card>
            @endforeach
        </div>
        <div class="mt-4">{{ $availableKelas->links() }}</div>
    @endif
</div>
@endsection
