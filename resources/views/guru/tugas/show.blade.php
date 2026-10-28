@extends('layouts.app')

@section('title', $tugas->judul . ' - SIMBA')

@section('content')
    <x-back-link
        href="{{ route('pengajar.tugas.index') }}"
        :label="__('common.back')"
    />

    <x-card class="mt-4">
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ $tugas->judul }}</h1>
        <dl class="mt-4 space-y-3">
            <div class="flex">
                <dt class="w-32 text-sm text-slate-500 dark:text-slate-400">{{ __('guru_soal.kelas') }}</dt>
                <dd class="text-sm text-slate-900 dark:text-slate-100">{{ $tugas->kelas?->nama_kelas ?? '-' }}</dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-sm text-slate-500 dark:text-slate-400">{{ __('guru_soal.tipe') }}</dt>
                <dd class="text-sm">
                    <x-badge variant="info">
                        {{ match($tugas->tipe) { 'upload' => __('guru_soal.tipe_upload'), 'pilihan_ganda' => __('guru_soal.tipe_pilihan_ganda'), 'essai' => __('guru_soal.tipe_esai'), default => ucfirst($tugas->tipe) } }}
                    </x-badge>
                </dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-sm text-slate-500 dark:text-slate-400">{{ __('guru_soal.deadline') }}</dt>
                <dd class="text-sm text-slate-900 dark:text-slate-100">{{ $tugas->deadline ? $tugas->deadline->format('d M Y H:i') : '-' }}</dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-sm text-slate-500 dark:text-slate-400">{{ __('guru_soal.pengumpulan') }}</dt>
                <dd class="text-sm text-slate-900 dark:text-slate-100">{{ __('guru_soal.jumlah_siswa', ['jumlah' => $tugas->pengumpulan->count()]) }}</dd>
            </div>
        </dl>
        @if($tugas->deskripsi)
            <div class="mt-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg text-sm text-slate-700 dark:text-slate-300">{{ $tugas->deskripsi }}</div>
        @endif
    </x-card>
@endsection
