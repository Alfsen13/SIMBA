@extends('layouts.app')

@section('title', $materi->judul . ' - SIMBA')

@section('content')
    <x-back-link
        href="{{ $materi->pertemuan_id ? route('pengajar.pertemuan.show', $materi->pertemuan_id) : route('pengajar.kelas.show', $materi->kelas_id) }}"
        :label="__('common.back')"
    />

    <x-card class="mt-4">
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ $materi->judul }}</h1>
        <x-rag-status :document="$materi->document" />
        <dl class="mt-4 space-y-3">
            <div class="flex">
                <dt class="w-32 text-sm text-slate-500 dark:text-slate-400">{{ __('guru_kelas.materi.kelas') }}</dt>
                <dd class="text-sm text-slate-900 dark:text-slate-100">{{ $materi->kelas?->nama_kelas ?? '-' }}</dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-sm text-slate-500 dark:text-slate-400">{{ __('guru_kelas.materi.bab') }}</dt>
                <dd class="text-sm text-slate-900 dark:text-slate-100">{{ $materi->bab ?? '-' }}</dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-sm text-slate-500 dark:text-slate-400">{{ __('guru_kelas.materi.file') }}</dt>
                <dd class="text-sm">
                    @if($materi->file_path)
                        <div class="flex items-center gap-3">
                            <a
                                href="{{ Storage::url($materi->file_path) }}"
                                class="text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300"
                                target="_blank"
                            >
                                {{ __('guru_kelas.materi.lihat') }}
                            </a>
                            <a
                                href="{{ Storage::url($materi->file_path) }}"
                                class="text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300"
                                download
                            >
                                {{ __('guru_kelas.download') }}
                            </a>
                        </div>
                    @else
                        <span class="text-slate-500 dark:text-slate-400">{{ __('guru_kelas.materi.tidak_ada_file') }}</span>
                    @endif
                </dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-sm text-slate-500 dark:text-slate-400">{{ __('guru_kelas.materi.diunggah_oleh') }}</dt>
                <dd class="text-sm">
                    {{ $materi->guru?->name ?? '-' }}
                </dd>
            </div>
        </dl>

        @if($materi->file_path)
            @php $tipeFile = strtolower($materi->file_type ?? ''); @endphp
            @if($tipeFile === 'pdf')
                <div class="mt-6 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900" style="height: 75vh;">
                    <iframe src="{{ Storage::url($materi->file_path) }}" class="w-full h-full border-0"></iframe>
                </div>
            @elseif(in_array($tipeFile, ['png', 'jpg', 'jpeg', 'gif', 'webp']))
                <div class="mt-6 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-2">
                    <img src="{{ Storage::url($materi->file_path) }}" alt="{{ $materi->judul }}" class="max-w-full mx-auto rounded-lg">
                </div>
            @else
                <p class="mt-6 text-sm text-slate-500 dark:text-slate-400">{{ __('guru_kelas.materi.pratinjau_tidak_tersedia') }} ({{ strtoupper($tipeFile ?: '?') }})</p>
            @endif
        @endif
    </x-card>
@endsection
