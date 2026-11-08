@php $title = __('siswa.dashboard.title'); @endphp

@extends('layouts.app')

@section('content')
<div class="py-4">
    {{-- Welcome Banner --}}
    <div class="relative overflow-hidden rounded-lg bg-gradient-to-br from-emerald-700 via-emerald-800 to-teal-900 px-5 py-4 mb-4">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
        <div class="relative">
            <p class="text-white text-sm font-medium">{{ __('siswa.dashboard.selamat_datang') }}</p>
            <h1 class="text-xl font-bold text-white mt-1">{{ auth()->user()->name }}</h1>
            <p class="text-white text-sm mt-2 max-w-xl">
                {{ __('siswa.dashboard.deskripsi') }}
            </p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <x-stat-card
            value="{{ $stats['total_kelas'] }}"
            :label="__('siswa.dashboard.kelas_ikuti')"
            color="emerald"
            icon='<svg class="w-5 h-5 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'
        />
        <x-stat-card
            value="{{ $stats['total_tugas'] }}"
            :label="__('siswa.dashboard.stat_tugas')"
            color="emerald"
            icon='<svg class="w-5 h-5 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>'
        />
        <x-stat-card
            value="{{ $stats['total_kuis'] }}"
            :label="__('siswa.dashboard.stat_kuis')"
            color="amber"
            icon='<svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>'
        />
        <x-stat-card
            value="{{ round($stats['rata_nilai']) }}"
            :label="__('siswa.dashboard.stat_nilai')"
            color="purple"
            icon='<svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>'
        />
    </div>

    {{-- 1. Kelas Diikuti --}}
    <section class="mb-4">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('siswa.dashboard.kelas_ikuti') }}</h2>
            <a href="{{ route('pelajar.kelas.index') }}" class="inline-flex min-h-11 items-center text-sm text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 font-medium">{{ __('siswa.dashboard.lihat_semua') }} &rarr;</a>
        </div>
        @if($kelasDiikuti->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($kelasDiikuti as $kelas)
                    <div class="bg-white dark:bg-slate-900 rounded-lg border border-slate-100 dark:border-slate-800 p-4 hover:shadow-md hover:border-emerald-200 transition-all">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-100 to-emerald-200 flex items-center justify-center text-base font-bold text-emerald-700 dark:text-emerald-300 shrink-0">{{ substr($kelas->nama_kelas, 0, 1) }}</div>
                            @if($kelas->kategori)
                                <x-badge variant="info" :label="$kelas->kategori" class="text-xs" />
                            @endif
                        </div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">{{ $kelas->nama_kelas }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">{{ __('siswa.shared.jumlah_pertemuan', ['n' => $kelas->pertemuan_count]) }} &bull; {{ __('siswa.shared.jumlah_materi', ['n' => $kelas->materi_count]) }}</p>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('pelajar.kelas.show', $kelas) }}" class="inline-flex min-h-11 items-center text-xs font-medium text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 bg-emerald-50 dark:bg-emerald-600/10 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-colors">{{ __('common.detail') }}</a>
                            <a href="{{ route('pelajar.ai-tutor.pertemuan', $kelas) }}" class="inline-flex min-h-11 items-center text-xs font-medium text-purple-600 dark:text-purple-400 hover:text-purple-800 bg-purple-50 dark:bg-purple-500/10 hover:bg-purple-100 px-3 py-1.5 rounded-lg transition-colors">{{ __('siswa.shared.ai_tutor') }}</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 bg-white dark:bg-slate-900 rounded-lg border border-slate-100 dark:border-slate-800">
                <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <p class="text-slate-500 dark:text-slate-400 text-sm">{{ __('siswa.dashboard.belum_ikut_kelas') }}</p>
                <a href="{{ route('pelajar.kelas.available') }}" class="mt-3 inline-flex min-h-11 items-center gap-1 px-2 text-sm text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 font-medium">
                    {{ __('siswa.kelas.cari_kelas') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        @endif
    </section>

    {{-- 2. Materi & Pertemuan Terbaru + Tugas dengan Deadline --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        {{-- Materi & Pertemuan Terbaru --}}
        <x-card padding="true">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('siswa.dashboard.materi_pertemuan_terbaru') }}</h2>
                <a href="{{ route('pelajar.kelas.index') }}" class="inline-flex min-h-11 items-center text-sm text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 font-medium">{{ __('siswa.dashboard.lihat_semua') }} &rarr;</a>
            </div>
            @if($materiTerbaru->count())
                <div class="space-y-2">
                    @foreach($materiTerbaru as $materi)
                        <a href="{{ route('pelajar.kelas.show', $materi->kelas) }}" class="flex items-start gap-3 p-3 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors group">
                            <div class="w-9 h-9 rounded-lg bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate group-hover:text-purple-700 transition-colors">{{ $materi->judul }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                                    {{ $materi->kelas?->nama_kelas ?? '-' }}
                                    @if($materi->pertemuan)
                                        &bull; {{ $materi->pertemuan->nama_pertemuan }}
                                    @endif
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $materi->created_at->diffForHumans() }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6">
                    <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('siswa.materi.belum_ada') }}</p>
                </div>
            @endif
        </x-card>

        {{-- Tugas dengan Tenggat Waktu --}}
        <x-card padding="true">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('navigation.assignments') }} <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">{{ __('siswa.dashboard.dengan_tenggat') }}</span></h2>
                <a href="{{ route('pelajar.tugas.index') }}" class="inline-flex min-h-11 items-center text-sm text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 font-medium">{{ __('siswa.dashboard.lihat_semua') }} &rarr;</a>
            </div>
            @if($tugasTerbaru->count())
                <div class="space-y-2">
                    @foreach($tugasTerbaru as $t)
                        @php
                            $deadline = $t->deadline;
                            $isOverdue = $deadline && $deadline->isPast();
                            $isNear = $deadline && $deadline->isFuture() && $deadline->diffInDays(now()) <= 3;
                            $isSubmitted = $t->pengumpulan?->sudahDikumpulkan() ?? false;
                        @endphp
                        <a href="{{ route('pelajar.tugas.show', $t) }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors group {{ $isOverdue && !$isSubmitted ? 'bg-red-50 dark:bg-red-500/10 border border-red-100 dark:border-red-800' : '' }}">
                            <div class="w-2 h-2 shrink-0 rounded-full mt-1.5 self-start {{ $isOverdue && !$isSubmitted ? 'bg-red-500' : ($isNear && !$isSubmitted ? 'bg-amber-400' : ($isSubmitted ? 'bg-emerald-400' : 'bg-slate-300 dark:bg-slate-600')) }}"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium truncate {{ $isOverdue && !$isSubmitted ? 'text-red-800 dark:text-red-400' : 'text-slate-900 dark:text-slate-100' }} group-hover:text-emerald-700 transition-colors">{{ $t->judul }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $t->kelas?->nama_kelas ?? '-' }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-xs whitespace-nowrap {{ $isOverdue && !$isSubmitted ? 'text-red-600 font-semibold' : ($isNear && !$isSubmitted ? 'text-amber-700 dark:text-amber-400 font-semibold' : 'text-slate-500 dark:text-slate-400') }}">
                                    {{ $deadline ? $deadline->format('d M') : '-' }}
                                </span>
                                @if($isOverdue && !$isSubmitted)
                                    <div class="text-[10px] text-red-600 dark:text-red-400 font-medium">{{ __('siswa.shared.status_terlambat') }}</div>
                                @elseif($isSubmitted)
                                    <div class="text-[10px] text-emerald-700 dark:text-emerald-400 font-medium">{{ __('siswa.shared.status_selesai') }}</div>
                                @elseif($isNear)
                                    <div class="text-[10px] text-amber-700 dark:text-amber-400 font-medium">{{ __('siswa.shared.segera') }}</div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6">
                    <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('siswa.tugas.belum_ada') }}</p>
                </div>
            @endif
        </x-card>
    </div>

    {{-- 3. Kuis Tersedia + Remedial --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Kuis Tersedia --}}
        <x-card padding="true">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('siswa.dashboard.kuis_tersedia') }}</h2>
                <a href="{{ route('pelajar.kuis.index') }}" class="inline-flex min-h-11 items-center text-sm text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 font-medium">{{ __('siswa.dashboard.lihat_semua') }} &rarr;</a>
            </div>
            @if($kuisTersedia->count())
                <div class="space-y-2">
                    @foreach($kuisTersedia as $kuis)
                        @php $done = $kuis->hasil !== null; @endphp
                        <div class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors {{ $done ? 'opacity-75' : '' }}">
                            <div class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">{{ $kuis->judul }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $kuis->kelas?->nama_kelas ?? '-' }}</p>
                                @if($kuis->tanggal_mulai && $kuis->tanggal_selesai)
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $kuis->tanggal_mulai->format('d M') }} &ndash; {{ $kuis->tanggal_selesai->format('d M') }}</p>
                                @endif
                            </div>
                            @if($done)
                                <div class="text-right shrink-0">
                                    <span class="text-xs font-medium text-emerald-700 dark:text-emerald-400">{{ round($kuis->hasil->skor * 100) }}%</span>
                                    <div class="text-[10px] text-emerald-700 dark:text-emerald-400">{{ __('siswa.shared.status_selesai') }}</div>
                                </div>
                            @else
                                <a href="{{ route('pelajar.kuis.show', $kuis) }}" class="inline-flex min-h-11 shrink-0 items-center text-xs font-medium text-amber-950 bg-amber-500 hover:bg-amber-400 px-3 py-1.5 rounded-lg transition-colors">
                                    {{ __('siswa.kuis.kerjakan') }}
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6">
                    <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('siswa.kuis.tidak_tersedia') }}</p>
                </div>
            @endif
        </x-card>

        {{-- Remedial --}}
        <x-card padding="true">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('navigation.remedial') }}</h2>
                <a href="{{ route('pelajar.remedial.index') }}" class="inline-flex min-h-11 items-center text-sm text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 font-medium">{{ __('siswa.dashboard.lihat_semua') }} &rarr;</a>
            </div>
            @if($remedialList->count())
                <div class="space-y-2">
                    @foreach($remedialList as $rem)
                        <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            <div class="w-9 h-9 rounded-lg bg-teal-100 dark:bg-teal-500/20 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $rem->topik_lemah }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ Str::limit($rem->ringkasan, 80) }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $rem->created_at->diffForHumans() }}</p>
                            </div>
                            @if($rem->status === 'belum_dibaca')
                                <span class="shrink-0 rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700 dark:bg-red-500/10 dark:text-red-300">{{ __('siswa.shared.status_belum_dibaca') }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6">
                    <svg class="w-10 h-10 text-green-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('siswa.remedial.tidak_ada_mantap') }}</p>
                </div>
            @endif
        </x-card>
    </div>

    {{-- Quick Actions row --}}
    <div class="mt-4 grid grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('pelajar.kelas.index') }}" class="group relative overflow-hidden rounded-lg bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-4 hover:border-emerald-200 hover:shadow-md transition-all">
            <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-600/20 rounded-lg flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                <svg class="w-5 h-5 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 mt-3 group-hover:text-emerald-700 transition-colors">{{ __('siswa.kelas.title') }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('siswa.dashboard.lihat_semua_kelas') }}</p>
        </a>
        <a href="{{ route('pelajar.tugas.index') }}" class="group relative overflow-hidden rounded-lg bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-4 hover:border-emerald-200 hover:shadow-md transition-all">
            <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-600/20 rounded-lg flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                <svg class="w-5 h-5 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 mt-3 group-hover:text-emerald-700 transition-colors">{{ __('navigation.assignments') }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('siswa.dashboard.kerjakan_tugas') }}</p>
        </a>
        <a href="{{ route('pelajar.ai-tutor') }}" class="group relative overflow-hidden rounded-lg bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-4 hover:border-purple-200 hover:shadow-md transition-all">
            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-500/20 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            </div>
            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 mt-3 group-hover:text-purple-700 transition-colors">{{ __('siswa.shared.ai_tutor') }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('siswa.dashboard.belajar_dengan_ai') }}</p>
        </a>
        <a href="{{ route('pelajar.kuis.index') }}" class="group relative overflow-hidden rounded-lg bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-4 hover:border-amber-200 hover:shadow-md transition-all">
            <div class="w-10 h-10 bg-amber-100 dark:bg-amber-500/20 rounded-lg flex items-center justify-center group-hover:bg-amber-200 transition-colors">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 mt-3 group-hover:text-amber-700 transition-colors">{{ __('navigation.quizzes') }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('siswa.dashboard.uji_pemahaman') }}</p>
        </a>
    </div>
</div>
@endsection
