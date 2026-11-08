@extends('layouts.app')

@section('title', __('siswa.pengajuan.detail_title').' - SIMBA')

@section('content')
    <x-back-link href="{{ route('pelajar.pengajuan-pendidikan.index') }}" :label="__('common.back')" class="min-h-11 min-w-11" />
    <x-page-header :title="__('siswa.pengajuan.detail_title')" />

    <x-card class="max-w-xl">
        <div class="space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">{{ __('siswa.pengajuan.tanggal_pengajuan') }}</span>
                <span class="text-slate-900 dark:text-white">{{ $pengajuan->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">{{ __('siswa.pengajuan.jenis') }}</span>
                <span class="text-slate-900 dark:text-white">{{ $pengajuan->jenis === 'kenaikan_kelas' ? __('siswa.pengajuan.kenaikan_kelas') : __('siswa.pengajuan.perpindahan_jenjang') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">{{ __('siswa.pengajuan.jalur_tujuan') }}</span>
                <span class="text-slate-900 dark:text-white">{{ $pengajuan->jalur?->nama ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">{{ __('siswa.pengajuan.jenjang_tujuan') }}</span>
                <span class="text-slate-900 dark:text-white">{{ $pengajuan->jenjang?->nama ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">{{ __('siswa.pengajuan.tingkat_tujuan') }}</span>
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
                    <span class="text-slate-500 dark:text-slate-400">{{ __('siswa.pengajuan.ditinjau_pada') }}</span>
                    <span class="text-slate-900 dark:text-white">{{ $pengajuan->reviewed_at->format('d/m/Y H:i') }}</span>
                </div>
            @endif
            @if($pengajuan->reviewedBy)
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">{{ __('siswa.pengajuan.ditinjau_oleh') }}</span>
                    <span class="text-slate-900 dark:text-white">{{ $pengajuan->reviewedBy->name }}</span>
                </div>
            @endif
            @if($pengajuan->alasan)
                <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ __('siswa.pengajuan.alasan_pengajuan') }}</p>
                    <p class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $pengajuan->alasan }}</p>
                </div>
            @endif
            @if($pengajuan->catatan_operator)
                <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ $pengajuan->alasan ? __('siswa.pengajuan.catatan_operator') : __('siswa.pengajuan.catatan_lama') }}</p>
                    <p class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $pengajuan->catatan_operator }}</p>
                </div>
            @endif
        </div>
    </x-card>
@endsection
