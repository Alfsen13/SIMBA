@php $title = 'AI Tutor — Pilih Kelas'; @endphp

@extends('layouts.app')

@section('content')
<div class="py-6">
    <x-page-header :title="__('navigation.ai_tutor')" :subtitle="__('publik.ai_tutor.subtitle_pilih_kelas')" />

    @if($kelasList->isEmpty())
        <x-empty-state
            :message="__('publik.ai_tutor.kosong_kelas')"
            action="{{ route('pelajar.kelas.available') }}"
            :actionLabel="__('publik.ai_tutor.cari_kelas')"
        />
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($kelasList as $kelas)
                <a
                    href="{{ route('pelajar.ai-tutor.pertemuan', $kelas) }}"
                    class="group block bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden hover:shadow-lg hover:border-emerald-200 transition-all duration-200"
                >
                    {{-- Card header with gradient --}}
                    <div class="h-2 bg-gradient-to-r from-emerald-500 to-purple-500 group-hover:from-emerald-600 group-hover:to-purple-600 transition-all"></div>

                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <div class="shrink-0">
                                <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-600/10 group-hover:bg-emerald-100 flex items-center justify-center transition-colors">
                                    <svg class="w-5.5 h-5.5 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-semibold text-slate-800 dark:text-slate-200 group-hover:text-emerald-700 transition-colors truncate">{{ $kelas->nama_kelas }}</h3>
                                @if($kelas->kategori)
                                    <span class="inline-block mt-1 px-2 py-0.5 text-xs rounded-full bg-emerald-100 dark:bg-emerald-600/20 text-emerald-700 dark:text-emerald-300">{{ $kelas->kategori }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                            <div class="flex items-center gap-3">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ $kelas->pertemuan->count() }} {{ __('publik.ai_tutor.pertemuan') }}
                                </span>
                            </div>
                            <span class="text-emerald-700 dark:text-emerald-400 font-medium group-hover:translate-x-0.5 transition-transform inline-flex items-center gap-1">
                                {{ __('publik.ai_tutor.pilih') }}
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
