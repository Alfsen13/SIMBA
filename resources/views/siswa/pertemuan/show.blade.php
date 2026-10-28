@php $title = $pertemuan->nama_pertemuan; @endphp

@extends('layouts.app')

@section('content')
<div class="py-6">
    <x-aktivitas-timer :log="$aktivitasLog" />
    <x-back-link
        href="{{ route('pelajar.kelas.show', $pertemuan->kelas_id) }}"
        :label="__('siswa.kelas.kembali_ke_kelas')"
    />

    <div class="mt-2">
        <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wide">{{ __('siswa.shared.pertemuan') }} {{ $pertemuan->urutan }}</span>
        <h1 class="text-xl font-bold mt-0.5">{{ $pertemuan->nama_pertemuan }}</h1>
        @if($pertemuan->deskripsi)
            <p class="text-slate-500 dark:text-slate-400 mt-1">{{ $pertemuan->deskripsi }}</p>
        @endif
    </div>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-card padding="true" class="border-l-4 border-emerald-500">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-slate-900 dark:text-slate-100">{{ __('siswa.shared.materi') }}</h2>
                <span class="text-xs font-medium text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-600/10 px-2 py-0.5 rounded-full">{{ $pertemuan->materi->count() }}</span>
            </div>
            @forelse($pertemuan->materi as $m)
                <a
                    href="{{ route('pelajar.materi.show', $m) }}"
                    class="block py-2 px-3 -mx-3 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-600/10 transition-colors"
                >
                    <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300">{{ $m->judul }}</p>
                    @if($m->bab)
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('siswa.shared.bab', ['bab' => $m->bab]) }}</p>
                    @endif
                </a>
            @empty
                <p class="text-sm text-slate-500 dark:text-slate-400 text-center py-4">{{ __('siswa.materi.belum_ada') }}</p>
            @endforelse
        </x-card>

        <x-card padding="true" class="border-l-4 border-purple-500">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-slate-900 dark:text-slate-100">{{ __('siswa.shared.ai_tutor') }}</h2>
                <span class="text-xs font-medium text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-500/10 px-2 py-0.5 rounded-full">{{ $pertemuan->materi->count() }}</span>
            </div>
            @forelse($pertemuan->materi as $m)
                <a
                    href="{{ route('pelajar.ai-tutor.chat', [$pertemuan->kelas, $pertemuan]) }}"
                    class="block py-2 px-3 -mx-3 rounded-lg hover:bg-purple-50 transition-colors"
                >
                    <p class="text-sm font-medium text-purple-700 dark:text-purple-300">{{ $m->judul }}</p>
                    @if($m->bab)
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('siswa.shared.bab', ['bab' => $m->bab]) }}</p>
                    @endif
                </a>
            @empty
                <p class="text-sm text-slate-500 dark:text-slate-400 text-center py-4">{{ __('siswa.materi.belum_ada') }}</p>
            @endforelse
        </x-card>

        <x-card padding="true" class="border-l-4 border-emerald-500">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-slate-900 dark:text-slate-100">{{ __('navigation.assignments') }}</h2>
                <span class="text-xs font-medium text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-600/10 px-2 py-0.5 rounded-full">{{ $pertemuan->tugas->count() }}</span>
            </div>
            @forelse($pertemuan->tugas as $t)
                <a
                    href="{{ route('pelajar.tugas.show', $t) }}"
                    class="block py-2 px-3 -mx-3 rounded-lg hover:bg-emerald-50 transition-colors"
                >
                    <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300">{{ $t->judul }}</p>
                    @if($t->deadline)
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('siswa.shared.deadline', ['deadline' => $t->deadline->format('d M Y H:i')]) }}</p>
                    @endif
                </a>
            @empty
                <p class="text-sm text-slate-500 dark:text-slate-400 text-center py-4">{{ __('siswa.tugas.belum_ada') }}</p>
            @endforelse
        </x-card>

        <x-card padding="true" class="border-l-4 border-amber-500">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-slate-900 dark:text-slate-100">{{ __('navigation.quizzes') }}</h2>
                <span class="text-xs font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 rounded-full">{{ $pertemuan->kuis->count() }}</span>
            </div>
            @forelse($pertemuan->kuis as $k)
                <a
                    href="{{ route('pelajar.kuis.show', $k) }}"
                    class="block py-2 px-3 -mx-3 rounded-lg hover:bg-amber-50 transition-colors"
                >
                    <p class="text-sm font-medium text-amber-700 dark:text-amber-300">{{ $k->judul }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $k->durasi_menit }} {{ __('siswa.shared.menit') }}</p>
                </a>
            @empty
                <p class="text-sm text-slate-500 dark:text-slate-400 text-center py-4">{{ __('siswa.kuis.belum_ada') }}</p>
            @endforelse
        </x-card>
    </div>
</div>
@endsection
