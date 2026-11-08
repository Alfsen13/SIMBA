@php $title = 'AI Tutor — ' . $kelas->nama_kelas; @endphp

@extends('layouts.app')

@section('content')
<div class="py-6">
    <x-page-header :title="__('navigation.ai_tutor')" :subtitle="__('publik.ai_tutor.subtitle_pilih_pertemuan', ['kelas' => $kelas->nama_kelas])">
        <x-slot:actions>
            <a href="{{ route('pelajar.ai-tutor') }}" class="inline-flex items-center min-h-11 min-w-11 text-sm text-emerald-700 dark:text-emerald-400 hover:underline">&larr; {{ __('publik.ai_tutor.kembali_daftar_kelas') }}</a>
        </x-slot:actions>
    </x-page-header>

    @if($pertemuans->isEmpty())
        <x-empty-state :message="__('publik.ai_tutor.kosong_pertemuan')" />
    @else
        <div class="space-y-3">
            @foreach($pertemuans as $index => $pertemuan)
                <div class="group relative flex gap-4">
                    {{-- Timeline indicator --}}
                    <div class="flex flex-col items-center shrink-0">
                        <div class="w-10 h-10 rounded-full border-2 border-emerald-200 dark:border-emerald-800 bg-white dark:bg-slate-900 flex items-center justify-center text-sm font-bold text-emerald-700 dark:text-emerald-400 group-hover:border-emerald-400 group-hover:bg-emerald-50 transition-colors z-10">
                            {{ $pertemuan->urutan }}
                        </div>
                        @if(!$loop->last)
                            <div class="w-0.5 flex-1 bg-gradient-to-b from-emerald-200 to-slate-100 mt-1"></div>
                        @endif
                    </div>

                    {{-- Content card --}}
                    <div class="flex-1 pb-4">
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden group-hover:shadow-md group-hover:border-emerald-100 transition-all duration-200">
                            <div class="px-5 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="font-semibold text-slate-800 dark:text-slate-200 group-hover:text-emerald-700 dark:group-hover:text-emerald-300 transition-colors">
                                            {{ $pertemuan->nama_pertemuan }}
                                        </h3>
                                        @if($pertemuan->deskripsi)
                                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">{{ $pertemuan->deskripsi }}</p>
                                        @endif
                                    </div>
                                    <a
                                        href="{{ route('pelajar.ai-tutor.chat', [$kelas, $pertemuan]) }}"
                                        class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-700 text-white text-sm font-medium rounded-lg hover:bg-emerald-800 active:scale-[0.97] transition-all shadow-sm"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                        {{ __('publik.ai_tutor.mulai_belajar') }}
                                    </a>
                                </div>

                                {{-- Materi preview --}}
                                <div class="mt-3 flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    <span>{{ $pertemuan->materi->count() }} {{ __('publik.ai_tutor.materi_tersedia') }}</span>
                                    @if($pertemuan->materi->isNotEmpty())
                                        <span class="text-slate-300">|</span>
                                        <span>{{ $pertemuan->materi->filter(fn($m) => $m->document)->count() }} {{ __('publik.ai_tutor.dokumen') }}</span>
                                    @endif
                                </div>

                                {{-- Materi detail cards (collapsible) --}}
                                @if($pertemuan->materi->isNotEmpty())
                                    <div x-data="{ expanded: false }" class="mt-3">
                                        <button
                                            type="button"
                                            @click="expanded = !expanded"
                                            :aria-expanded="expanded.toString()"
                                            class="flex items-center min-h-11 min-w-11 gap-1.5 text-xs font-medium text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 transition-colors"
                                        >
                                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="expanded && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            <span x-show="!expanded">{{ __('publik.ai_tutor.lihat_materi') }}</span>
                                            <span x-show="expanded" x-cloak>{{ __('publik.ai_tutor.sembunyikan_materi') }}</span>
                                        </button>
                                        <div x-show="expanded" x-collapse class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-2.5">
                                            @foreach($pertemuan->materi as $materi)
                                                <div class="flex items-start gap-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-100 dark:border-slate-800 hover:border-emerald-100 hover:bg-emerald-50/30 transition-colors">
                                                    <div class="shrink-0 mt-0.5">
                                                        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-600/20 flex items-center justify-center">
                                                            <svg class="w-4 h-4 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                        </div>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <h4 class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate">{{ $materi->judul }}</h4>
                                                        @if($materi->babSummary)
                                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">{{ Str::limit($materi->babSummary->ringkasan, 100) }}</p>
                                                        @else
                                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('publik.ai_tutor.ringkasan_belum_tersedia') }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
