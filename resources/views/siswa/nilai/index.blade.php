@php $title = __('siswa.nilai.title'); @endphp

@extends('layouts.app')

@section('content')
<div class="py-6">
    <x-page-header :title="__('siswa.nilai.title')" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <x-card padding="true">
            <h2 class="font-semibold text-lg mb-3">{{ __('siswa.nilai.harian') }}</h2>
            @forelse($nilaiHarian as $n)
                <div class="flex justify-between py-2 border-b last:border-0">
                    <span>{{ $n->kelas?->nama_kelas ?: '-' }}</span>
                    <span class="font-medium">{{ $n->nilai }}</span>
                </div>
            @empty
                <p class="text-slate-500 dark:text-slate-400">{{ __('siswa.nilai.belum_ada_harian') }}</p>
            @endforelse
        </x-card>

        <x-card padding="true">
            <h2 class="font-semibold text-lg mb-3">{{ __('siswa.nilai.tugas') }}</h2>
            @forelse($nilaiTugas as $nt)
                <div class="flex justify-between py-2 border-b last:border-0">
                    <span>{{ $nt->tugas?->judul ?: '-' }}</span>
                    <span class="font-medium">{{ $nt->nilai }}</span>
                </div>
            @empty
                <p class="text-slate-500 dark:text-slate-400">{{ __('siswa.nilai.belum_ada_tugas') }}</p>
            @endforelse
        </x-card>

        <x-card padding="true">
            <h2 class="font-semibold text-lg mb-3">{{ __('siswa.nilai.kuis') }}</h2>
            @forelse($nilaiKuis as $nk)
                <div class="flex justify-between py-2 border-b last:border-0">
                    <span>{{ $nk->kuis?->judul ?: '-' }}</span>
                    <span class="font-medium">{{ $nk->skor }}</span>
                </div>
            @empty
                <p class="text-slate-500 dark:text-slate-400">{{ __('siswa.nilai.belum_ada_kuis') }}</p>
            @endforelse
        </x-card>
    </div>
</div>
@endsection
