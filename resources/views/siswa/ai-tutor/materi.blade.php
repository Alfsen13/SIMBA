@php $title = 'AI Tutor — ' . $kelas->nama_kelas; @endphp

@extends('layouts.app')

@section('content')
<div class="py-6">
    <x-page-header :title="__('navigation.ai_tutor')" :subtitle="__('publik.ai_tutor.subtitle_pilih_materi', ['kelas' => $kelas->nama_kelas])">
        <x-slot:actions>
            <a href="{{ route('pelajar.ai-tutor') }}" class="text-sm text-emerald-700 dark:text-emerald-400 hover:underline">&larr; {{ __('publik.ai_tutor.kembali_daftar_kelas') }}</a>
        </x-slot:actions>
    </x-page-header>

    @if($materis->isEmpty())
        <x-empty-state :message="__('publik.ai_tutor.kosong_materi')" />
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($materis as $materi)
                <x-card padding="true" class="border-l-4 border-emerald-500 hover:shadow-md transition-shadow">
                    <h3 class="font-semibold text-lg">{{ $materi->judul }}</h3>
                    @if($materi->babSummary)
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">{{ Str::limit($materi->babSummary->ringkasan, 120) }}</p>
                    @else
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('publik.ai_tutor.ringkasan_belum_tersedia') }}</p>
                    @endif
                    <div class="flex items-center gap-2 mt-3">
                        <a
                            href="{{ route('pelajar.ai-tutor.chat', [$kelas, $materi]) }}"
                            class="inline-flex items-center gap-1 text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 text-sm font-medium"
                        >
                            {{ __('publik.ai_tutor.mulai_belajar') }} &rarr;
                        </a>
                    </div>
                </x-card>
            @endforeach
        </div>
    @endif
</div>
@endsection