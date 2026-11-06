@extends('layouts.app')

@section('title', $materi->judul . ' - Operator')

@section('content')
    <x-back-link href="{{ route('operator.kelas.materi', $materi->kelas) }}" :label="'Materi ' . $materi->kelas->nama_kelas" />

    <x-card class="mt-4">
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ $materi->judul }}</h1>
        <x-rag-status :document="$materi->document" />
        <dl class="mt-4 space-y-3">
            <div class="flex">
                <dt class="w-32 text-sm text-slate-500 dark:text-slate-400">Kelas</dt>
                <dd class="text-sm text-slate-900 dark:text-slate-100">{{ $materi->kelas->nama_kelas }}</dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-sm text-slate-500 dark:text-slate-400">Mata Pelajaran</dt>
                <dd class="text-sm text-slate-900 dark:text-slate-100">{{ $materi->kelas->mataPelajaran?->nama ?? '-' }}</dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-sm text-slate-500 dark:text-slate-400">Pertemuan</dt>
                <dd class="text-sm text-slate-900 dark:text-slate-100">{{ $materi->pertemuan?->nama_pertemuan ?? '-' }}</dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-sm text-slate-500 dark:text-slate-400">Bab</dt>
                <dd class="text-sm text-slate-900 dark:text-slate-100">{{ $materi->bab ?? '-' }}</dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-sm text-slate-500 dark:text-slate-400">Pengajar</dt>
                <dd class="text-sm text-slate-900 dark:text-slate-100">{{ $materi->guru?->name ?? '-' }}</dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-sm text-slate-500 dark:text-slate-400">Berkas</dt>
                <dd class="text-sm">
                    @if($materi->file_path)
                        <div class="flex items-center gap-3">
                            <a href="{{ Storage::url($materi->file_path) }}" target="_blank" rel="noopener" class="text-emerald-700 hover:underline dark:text-emerald-400">Lihat</a>
                            <a href="{{ Storage::url($materi->file_path) }}" download class="text-emerald-700 hover:underline dark:text-emerald-400">Unduh</a>
                        </div>
                    @else
                        <span class="text-slate-500 dark:text-slate-400">Tidak ada berkas</span>
                    @endif
                </dd>
            </div>
        </dl>

        @if($materi->deskripsi)
            <div class="mt-6 border-t border-slate-200 pt-4 dark:border-slate-700">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Deskripsi</h2>
                <p class="mt-2 whitespace-pre-line text-sm text-slate-600 dark:text-slate-300">{{ $materi->deskripsi }}</p>
            </div>
        @endif

        @if($materi->file_path && strtolower($materi->file_type ?? '') === 'pdf')
            <div class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900" style="height: 75vh;">
                <iframe src="{{ Storage::url($materi->file_path) }}" title="{{ $materi->judul }}" class="h-full w-full border-0"></iframe>
            </div>
        @elseif($materi->file_path && in_array(strtolower($materi->file_type ?? ''), ['png', 'jpg', 'jpeg', 'gif', 'webp']))
            <div class="mt-6 rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-800 dark:bg-slate-900">
                <img src="{{ Storage::url($materi->file_path) }}" alt="{{ $materi->judul }}" class="mx-auto max-w-full rounded-lg">
            </div>
        @endif
    </x-card>
@endsection
