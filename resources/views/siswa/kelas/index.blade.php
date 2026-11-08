@php $title = __('siswa.kelas.title'); @endphp

@extends('layouts.app')

@section('content')
<div class="py-6">
    <x-page-header :title="__('siswa.kelas.title')">
        <x-slot:actions>
            <x-btn-primary href="{{ route('pelajar.kelas.available') }}" size="sm" class="min-h-11 min-w-11 justify-center">
                {{ __('siswa.kelas.tambah_cari_kelas') }}
            </x-btn-primary>
        </x-slot:actions>
    </x-page-header>

    @if($kelasMenunggu->isNotEmpty())
        <div class="mb-4">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">{{ __('siswa.kelas.menunggu_validasi') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($kelasMenunggu as $kelas)
                    <x-card padding="true" class="border-l-4 border-amber-500 bg-amber-50/40">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="font-semibold text-lg">{{ $kelas->nama_kelas }}</h3>
                            <x-badge variant="warning">{{ __('siswa.kelas.menunggu') }}</x-badge>
                        </div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('siswa.kelas.kode', ['kode' => $kelas->kode_kelas]) }}</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('siswa.kelas.guru', ['guru' => $kelas->guru?->name ?? '-']) }}</p>
                        <div class="mt-3 flex items-center justify-between gap-3">
                            <span class="text-xs text-amber-700 dark:text-amber-300">{{ __('siswa.kelas.ditinjau_operator') }}</span>
                            <form
                                method="POST"
                                action="{{ route('pelajar.kelas.unenroll', $kelas) }}"
                                x-data
                                x-on:submit.prevent="confirm('{{ __('siswa.kelas.konfirmasi_batalkan') }}') && $el.submit()"
                            >
                                @csrf
                                <x-btn-secondary type="submit" color="red" size="sm" class="min-h-11 min-w-11 justify-center">
                                    {{ __('common.cancel') }}
                                </x-btn-secondary>
                            </form>
                        </div>
                    </x-card>
                @endforeach
            </div>
        </div>
    @endif

    @if($kelasList->isEmpty() && $kelasMenunggu->isEmpty())
        <x-empty-state
            :message="__('siswa.kelas.belum_terdaftar')"
            action="{{ route('pelajar.kelas.available') }}"
            :actionLabel="__('siswa.kelas.cari_kelas')"
        />
    @elseif($kelasList->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($kelasList as $kelas)
                <x-card padding="true" class="border-l-4 border-emerald-500">
                    <h3 class="font-semibold text-lg">{{ $kelas->nama_kelas }}</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('siswa.kelas.kode', ['kode' => $kelas->kode_kelas]) }}</p>
                    @if($kelas->kategori)<p class="text-sm text-slate-500 dark:text-slate-400">{{ __('siswa.kelas.kategori', ['kategori' => $kelas->kategori]) }}</p>@endif
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('siswa.kelas.guru', ['guru' => $kelas->guru?->name]) }}</p>
                    <a
                        href="{{ route('pelajar.kelas.show', $kelas) }}"
                        class="mt-2 inline-flex min-h-11 min-w-11 items-center gap-1 px-2 text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 text-sm font-medium"
                    >
                        {{ __('common.detail') }} &rarr;
                    </a>
                </x-card>
            @endforeach
        </div>
        <div class="mt-4">{{ $kelasList->links() }}</div>
    @endif
</div>
@endsection
