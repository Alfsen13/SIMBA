@extends('layouts.app')

@section('title', __('guru_kelas.dashboard.title') . ' - SIMBA')

@section('content')
<div>
    {{-- Welcome Banner --}}
    <div class="relative overflow-hidden rounded-lg bg-gradient-to-br from-emerald-600 via-emerald-700 to-teal-800 px-5 py-4 mb-4">
        <div class="absolute top-0 right-0 w-48 h-48 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
        <div class="relative">
            <p class="text-emerald-200 text-xs font-medium">{{ __('guru_kelas.dashboard.subtitle') }}</p>
            <h1 class="text-xl lg:text-xl font-bold text-white mt-0.5">{{ __('guru_kelas.dashboard.selamat_datang', ['nama' => auth()->user()->name]) }}</h1>
            <p class="text-emerald-100/90 text-xs mt-1 max-w-2xl">{{ __('guru_kelas.dashboard.deskripsi') }}</p>
        </div>
    </div>

    {{-- Stats ringkas --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-4">
        <x-stat-card :value="$stats['total_kelas']" :label="__('guru_kelas.dashboard.stat.kelas_diampu')" color="emerald" />
        <x-stat-card :value="$stats['total_siswa']" :label="__('guru_kelas.dashboard.stat.total_siswa')" color="blue" />
        <x-stat-card :value="$stats['total_materi']" :label="__('guru_kelas.dashboard.stat.materi')" color="purple" />
        <x-stat-card :value="$stats['total_tugas']" :label="__('guru_kelas.dashboard.stat.tugas')" color="amber" />
        <x-stat-card :value="$stats['siswa_berisiko']" :label="__('guru_kelas.dashboard.stat.berisiko')" color="red" />
    </div>

    {{-- Daftar Kelas + AI Generate Pertemuan --}}
    <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">{{ __('guru_kelas.dashboard.kelas_diampu') }}</h2>

    @if($kelasList->count())
        <div class="space-y-3">
            @foreach($kelasList as $kelas)
                <div class="bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800 overflow-hidden" x-data="{ open: false, loading: false }">
                    {{-- Header Kelas --}}
                    <div class="p-3.5 flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-100 to-emerald-200 dark:from-emerald-500/20 dark:to-emerald-500/10 flex items-center justify-center text-base font-bold text-emerald-700 dark:text-emerald-300 shrink-0">{{ substr($kelas->nama_kelas, 0, 1) }}</div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $kelas->nama_kelas }}</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5 flex-wrap">
                                    <span>{{ $kelas->pertemuan_count }} {{ __('guru_kelas.kata.pertemuan') }}</span>
                                    <span class="text-slate-300 dark:text-slate-400">&bull;</span>
                                    <span>{{ $kelas->siswa_count }} {{ __('guru_kelas.kata.siswa') }}</span>
                                    @if($kelas->kategori)
                                        <x-badge variant="info" class="text-[11px]">{{ $kelas->kategori }}</x-badge>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <x-btn-secondary :href="route('pengajar.kelas.show', $kelas)" size="sm">{{ __('common.detail') }}</x-btn-secondary>
                            <button @click="open = !open" class="text-xs font-medium px-2.5 py-1.5 rounded-lg transition-colors"
                                :class="open ? 'bg-emerald-700 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-600/10 dark:text-emerald-300 dark:hover:bg-emerald-600/20'">
                                <span x-show="!open">{{ __('guru_kelas.generate_pertemuan_ai') }}</span>
                                <span x-show="open" x-cloak>{{ __('common.close') }}</span>
                            </button>
                        </div>
                    </div>

                    {{-- AI Generate Form (expandable) --}}
                    <div x-show="open" x-cloak x-collapse class="border-t border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/30">
                        <form method="POST" action="{{ route('pengajar.kelas.generate-pertemuan', $kelas) }}" @submit="loading = true" class="p-3.5 space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ __('guru_kelas.dashboard.topik_materi') }}</label>
                                <textarea name="materi_topics" rows="2" required placeholder="{{ __('guru_kelas.dashboard.topik_placeholder') }}"
                                    class="w-full border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-800 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none"></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ __('guru_kelas.jumlah_pertemuan') }}</label>
                                    <input type="number" name="jumlah_pertemuan" min="4" max="20" value="8" required
                                        class="w-full border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-800 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ __('guru_kelas.dashboard.catatan_opsional') }}</label>
                                    <input type="text" name="catatan" placeholder="{{ __('guru_kelas.dashboard.catatan_placeholder') }}"
                                        class="w-full border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-800 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none">
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="submit"
                                    class="inline-flex items-center gap-2 bg-emerald-700 hover:bg-emerald-800 text-white font-medium px-3.5 py-2 rounded-lg text-sm transition-colors disabled:opacity-50"
                                    :disabled="loading">
                                    <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    <svg x-show="loading" x-cloak class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span x-show="!loading">{{ __('guru_kelas.generate_dengan_ai') }}</span>
                                    <span x-show="loading" x-cloak>{{ __('guru_kelas.memproses') }}</span>
                                </button>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('guru_kelas.dashboard.ai_note', ['kategori' => $kelas->kategori ? $kelas->kategori : '']) }}</p>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800">
            <svg class="w-10 h-10 text-slate-300 dark:text-slate-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('guru_kelas.dashboard.belum_ada_kelas') }}</p>
            <a href="{{ route('pengajar.kelas.available') }}" class="mt-3 inline-flex items-center gap-1 text-sm text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 font-medium">
                {{ __('guru_kelas.dashboard.ambil_kelas') }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    @endif

    {{-- Bottom: Remedial & Berisiko --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
        {{-- Remedial Terbaru --}}
        <x-card>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ __('guru_kelas.dashboard.remedial_terbaru') }}</h2>
                <a href="{{ route('pengajar.remedial.index') }}" class="text-xs text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 font-medium">{{ __('guru_kelas.lihat_semua') }} &rarr;</a>
            </div>
            @if($remedialList->count())
                <div class="space-y-1">
                    @foreach($remedialList as $remedial)
                        <div class="flex items-start gap-3 p-2.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            <div class="w-8 h-8 rounded-lg bg-teal-100 dark:bg-teal-500/20 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $remedial->topik_lemah }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('guru_kelas.dashboard.siswa_label', ['nama' => $remedial->siswa?->name ?? '-']) }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $remedial->created_at->diffForHumans() }}</p>
                            </div>
                            <x-badge :variant="$remedial->status === 'belum_dibaca' ? 'baru' : ($remedial->status === 'selesai' ? 'verified' : 'pending')">{{ $remedial->status === 'belum_dibaca' ? __('guru_kelas.baru') : ucfirst($remedial->status) }}</x-badge>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6">
                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('guru_kelas.dashboard.belum_ada_remedial') }}</p>
                </div>
            @endif
        </x-card>

        {{-- Siswa Berisiko Tinggi --}}
        <x-card>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ __('guru_kelas.dashboard.siswa_berisiko') }}</h2>
                <a href="{{ route('pengajar.prediksi') }}" class="text-xs text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 font-medium">{{ __('guru_kelas.lihat_semua') }} &rarr;</a>
            </div>
            @if($siswaBerisikoList->count())
                <div class="space-y-1">
                    @foreach($siswaBerisikoList as $prediksi)
                        <div class="flex items-center justify-between p-2.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-500/20 flex items-center justify-center text-sm font-semibold text-red-700 dark:text-red-400 shrink-0">{{ substr($prediksi->siswa?->name ?? '?', 0, 1) }}</div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">{{ $prediksi->siswa?->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('guru_kelas.dashboard.skor', ['nilai' => number_format($prediksi->skor_risiko, 1)]) }}</p>
                                </div>
                            </div>
                            <x-badge variant="danger">{{ __('guru_kelas.dashboard.stat.berisiko') }}</x-badge>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6">
                    <svg class="w-10 h-10 text-green-300 dark:text-green-700/60 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('guru_kelas.dashboard.tidak_ada_berisiko') }}</p>
                </div>
            @endif
        </x-card>
    </div>
</div>
@endsection
