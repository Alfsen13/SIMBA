@extends('layouts.app')

@section('title', $pertemuan->nama_pertemuan . ' - SIMBA')

@section('content')
    <x-back-link
        href="{{ route('pengajar.kelas.show', $pertemuan->kelas) }}"
        :label="__('guru_kelas.pertemuan.kembali_ke_kelas')"
    />

    <x-card class="mt-4">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ ucfirst(__('guru_kelas.kata.pertemuan')) }} {{ $pertemuan->urutan }}: {{ $pertemuan->nama_pertemuan }}</h1>
                    @if($pertemuan->dibuka)
                        <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-600/10 text-emerald-700 dark:text-emerald-300">{{ __('guru_kelas.terbuka') }}</span>
                    @else
                        <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">{{ __('guru_kelas.terkunci') }}</span>
                    @endif
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $pertemuan->kelas->nama_kelas }}</p>
            </div>
            <div class="flex items-center gap-2">
                <form
                    action="{{ $pertemuan->dibuka ? route('pengajar.pertemuan.kunci', $pertemuan) : route('pengajar.pertemuan.buka', $pertemuan) }}"
                    method="POST"
                >
                    @csrf
                    <button class="text-sm px-3 py-1.5 rounded-lg font-medium border transition {{ $pertemuan->dibuka ? 'border-amber-300 text-amber-700 dark:text-amber-400 hover:bg-amber-50' : 'border-emerald-300 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50' }}">
                        {{ $pertemuan->dibuka ? __('guru_kelas.pertemuan.kunci_pertemuan') : __('guru_kelas.pertemuan.buka_pertemuan') }}
                    </button>
                </form>
                <x-btn-secondary href="{{ route('pengajar.pertemuan.edit', $pertemuan) }}" size="sm">
                    {{ __('guru_kelas.edit') }}
                </x-btn-secondary>
            </div>
        </div>
        @if($pertemuan->deskripsi)
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3">{{ $pertemuan->deskripsi }}</p>
        @endif
        @if($pertemuan->tanggal_mulai || $pertemuan->tanggal_selesai)
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-3">
                {{ __('guru_kelas.pertemuan.jadwal') }}
                {{ $pertemuan->tanggal_mulai?->format('d M Y') ?? '-' }}
                &ndash;
                {{ $pertemuan->tanggal_selesai?->format('d M Y') ?? '-' }}
            </p>
        @endif
    </x-card>

    {{-- Kehadiran --}}
    <x-card class="mt-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('guru_kelas.kehadiran.title') }}</h2>
            <x-btn-primary href="{{ route('pengajar.kehadiran.edit', $pertemuan) }}" size="sm">
                {{ __('guru_kelas.pertemuan.kelola_kehadiran') }}
            </x-btn-primary>
        </div>
        @php
            $totalKehadiran = $pertemuan->kehadiran->count();
            $perStatus = $pertemuan->kehadiran->countBy('status');
        @endphp
        @if($totalKehadiran > 0)
            <p class="text-sm text-slate-600 dark:text-slate-400">
                {{ __('guru_kelas.kehadiran.tercatat') }} <strong>{{ $totalKehadiran }}</strong> {{ __('guru_kelas.kata.siswa') }} &mdash;
                {{ __('guru_kelas.kehadiran.hadir') }}: <strong>{{ $perStatus->get('hadir', 0) }}</strong>,
                {{ __('guru_kelas.kehadiran.izin') }}: <strong>{{ $perStatus->get('izin', 0) }}</strong>,
                {{ __('guru_kelas.kehadiran.sakit') }}: <strong>{{ $perStatus->get('sakit', 0) }}</strong>,
                {{ __('guru_kelas.kehadiran.alpha') }}: <strong>{{ $perStatus->get('alpha', 0) }}</strong>.
            </p>
        @else
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('guru_kelas.pertemuan.belum_ada_absensi') }}</p>
        @endif
    </x-card>

    {{-- Materi --}}
    <x-card class="mt-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ ucfirst(__('guru_kelas.kata.materi')) }}</h2>
            <x-btn-primary href="{{ route('pengajar.materi.create', ['pertemuan_id' => $pertemuan->id]) }}" size="sm">
                + {{ __('guru_kelas.materi.tambah') }}
            </x-btn-primary>
        </div>
        @forelse($pertemuan->materi as $materi)
            <div class="flex items-center justify-between py-3 border-b border-slate-100 dark:border-slate-800 last:border-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-600/20 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $materi->judul }}</span>
                        @if($materi->bab)
                            <span class="text-xs text-slate-500 dark:text-slate-400 ml-2">{{ __('guru_kelas.bab_label', ['bab' => $materi->bab]) }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex gap-2">
                    <x-btn-secondary href="{{ route('pengajar.materi.show', $materi) }}" size="sm">
                        {{ __('common.detail') }}
                    </x-btn-secondary>
                    <x-btn-secondary href="{{ route('pengajar.materi.edit', $materi) }}" size="sm">
                        {{ __('guru_kelas.edit') }}
                    </x-btn-secondary>
                    <form
                        action="{{ route('pengajar.materi.destroy', $materi) }}"
                        method="POST"
                        x-data
                        x-on:submit.prevent="confirm('{{ __('guru_kelas.materi.konfirmasi_hapus') }}') && $el.submit()"
                    >
                        @csrf
                        @method('DELETE')
                        <x-btn-secondary type="submit" color="red" size="sm">
                            {{ __('common.delete') }}
                        </x-btn-secondary>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('guru_kelas.pertemuan.belum_ada_materi') }}</p>
        @endforelse
    </x-card>

    {{-- AI Tutor --}}
    <x-card class="mt-6 border-l-4 border-purple-500">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">AI Tutor</h2>
        </div>
        @forelse($pertemuan->materi as $materi)
            <div class="flex items-center justify-between py-3 border-b border-slate-100 dark:border-slate-800 last:border-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-purple-100 dark:bg-purple-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $materi->judul }}</span>
                        @if($materi->bab)
                            <span class="text-xs text-slate-500 dark:text-slate-400 ml-2">{{ __('guru_kelas.bab_label', ['bab' => $materi->bab]) }}</span>
                        @endif
                    </div>
                </div>
                <div>
                    <x-btn-secondary href="{{ route('pelajar.ai-tutor.chat', [$pertemuan->kelas, $pertemuan]) }}" size="sm">
                        {{ __('guru_kelas.pertemuan.buka_ai_tutor') }}
                    </x-btn-secondary>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('guru_kelas.pertemuan.belum_ada_ai_tutor') }}</p>
        @endforelse
    </x-card>

    {{-- Tugas --}}
    <x-card class="mt-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ ucfirst(__('guru_kelas.kata.tugas')) }}</h2>
            <x-btn-primary href="{{ route('pengajar.tugas.create', ['pertemuan_id' => $pertemuan->id]) }}" size="sm">
                + {{ __('guru_kelas.tambah_tugas') }}
            </x-btn-primary>
        </div>
        @forelse($pertemuan->tugas as $tugas)
            <div class="flex items-center justify-between py-3 border-b border-slate-100 dark:border-slate-800 last:border-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-amber-100 dark:bg-amber-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $tugas->judul }}</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 ml-2">{{ match($tugas->tipe) { 'upload' => __('guru_kelas.tugas_tipe.upload'), 'pilihan_ganda' => __('guru_kelas.tugas_tipe.pilihan_ganda'), 'essai' => __('guru_kelas.tugas_tipe.essai'), default => ucfirst($tugas->tipe) } }}</span>
                        @if($tugas->deadline)
                            <span class="text-xs text-slate-500 dark:text-slate-400 ml-2">{{ __('guru_kelas.deadline', ['waktu' => $tugas->deadline->format('d M Y H:i')]) }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex gap-2">
                    <x-btn-secondary href="{{ route('pengajar.tugas.show', $tugas) }}" size="sm">
                        {{ __('common.detail') }}
                    </x-btn-secondary>
                    <x-btn-secondary href="{{ route('pengajar.tugas.edit', $tugas) }}" size="sm">
                        {{ __('guru_kelas.edit') }}
                    </x-btn-secondary>
                    <form
                        action="{{ route('pengajar.tugas.destroy', $tugas) }}"
                        method="POST"
                        x-data
                        x-on:submit.prevent="confirm('{{ __('guru_soal.konfirmasi_hapus_tugas') }}') && $el.submit()"
                    >
                        @csrf
                        @method('DELETE')
                        <x-btn-secondary type="submit" color="red" size="sm">
                            {{ __('common.delete') }}
                        </x-btn-secondary>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('guru_kelas.pertemuan.belum_ada_tugas') }}</p>
        @endforelse
    </x-card>

    {{-- Kuis --}}
    <x-card class="mt-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ ucfirst(__('guru_kelas.kata.kuis')) }}</h2>
            <x-btn-primary href="{{ route('pengajar.kuis.create', ['pertemuan_id' => $pertemuan->id]) }}" size="sm">
                + {{ __('guru_kelas.tambah_kuis') }}
            </x-btn-primary>
        </div>
        @forelse($pertemuan->kuis as $kuis)
            <div class="flex items-center justify-between py-3 border-b border-slate-100 dark:border-slate-800 last:border-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-600/20 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $kuis->judul }}</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 ml-2">{{ $kuis->durasi_menit }} {{ __('guru_kelas.kata.menit') }}</span>
                        <x-badge :variant="$kuis->is_aktif ? 'verified' : 'danger'" class="ml-2">
                            {{ $kuis->is_aktif ? __('common.active') : __('guru_kelas.nonaktif') }}
                        </x-badge>
                    </div>
                </div>
                <div class="flex gap-2">
                    <x-btn-secondary href="{{ route('pengajar.kuis.edit', $kuis) }}" size="sm">
                        {{ __('guru_kelas.edit') }}
                    </x-btn-secondary>
                    <form
                        action="{{ route('pengajar.kuis.destroy', $kuis) }}"
                        method="POST"
                        x-data
                        x-on:submit.prevent="confirm('{{ __('guru_soal.konfirmasi_hapus_kuis') }}') && $el.submit()"
                    >
                        @csrf
                        @method('DELETE')
                        <x-btn-secondary type="submit" color="red" size="sm">
                            {{ __('common.delete') }}
                        </x-btn-secondary>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('guru_kelas.pertemuan.belum_ada_kuis') }}</p>
        @endforelse
    </x-card>
@endsection
