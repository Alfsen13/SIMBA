@extends('layouts.app')

@section('title', 'Detail Pengajuan Perubahan Pendidikan - SIMBA')

@section('content')
    <x-back-link href="{{ route('pelajar.pengajuan-pendidikan.index') }}" label="Kembali" />
    <x-page-header title="Detail Pengajuan Perubahan Pendidikan" />

    <x-card class="max-w-xl">
        <div class="space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">Tanggal Pengajuan</span>
                <span class="text-slate-900 dark:text-white">{{ $pengajuan->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">Jenis</span>
                <span class="text-slate-900 dark:text-white">{{ $pengajuan->jenis === 'kenaikan_kelas' ? 'Kenaikan Kelas' : 'Perpindahan Jenjang' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">Jalur Tujuan</span>
                <span class="text-slate-900 dark:text-white">{{ $pengajuan->jalur?->nama ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">Jenjang Tujuan</span>
                <span class="text-slate-900 dark:text-white">{{ $pengajuan->jenjang?->nama ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">Tingkat Tujuan</span>
                <span class="text-slate-900 dark:text-white">{{ $pengajuan->tingkat?->nama ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">{{ __('siswa.pengajuan.status') }}</span>
                @php
                    $map = [
                        'pending' => ['variant' => 'pending', 'label' => __('siswa.pengajuan.menunggu')],
                        'under_review' => ['variant' => 'info', 'label' => __('siswa.pengajuan.sedang_ditinjau')],
                        'revision_required' => ['variant' => 'warning', 'label' => __('siswa.pengajuan.perlu_revisi')],
                        'approved' => ['variant' => 'success', 'label' => __('siswa.pengajuan.disetujui')],
                        'rejected' => ['variant' => 'rejected', 'label' => __('siswa.pengajuan.ditolak')],
                    ];
                    $m = $map[$pengajuan->status] ?? ['variant' => 'default', 'label' => ucfirst($pengajuan->status)];
                @endphp
                <x-badge :variant="$m['variant']">{{ $m['label'] }}</x-badge>
            </div>
            @if($pengajuan->reviewed_at)
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Ditinjau Pada</span>
                    <span class="text-slate-900 dark:text-white">{{ $pengajuan->reviewed_at->format('d/m/Y H:i') }}</span>
                </div>
            @endif
            @if($pengajuan->reviewedBy)
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">Ditinjau Oleh</span>
                    <span class="text-slate-900 dark:text-white">{{ $pengajuan->reviewedBy->name }}</span>
                </div>
            @endif
            <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
                <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Alasan Pengajuan</p>
                <p class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $pengajuan->catatan_operator }}</p>
            </div>
            @if($pengajuan->catatan_operator && in_array($pengajuan->status, ['revision_required', 'rejected']))
                <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Catatan Operator</p>
                    <p class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $pengajuan->catatan_operator }}</p>
                </div>
            @endif
        </div>
    </x-card>
@endsection
