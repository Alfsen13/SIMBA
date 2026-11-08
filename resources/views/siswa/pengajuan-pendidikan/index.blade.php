@extends('layouts.app')

@section('title', __('siswa.pengajuan.title').' - SIMBA')

@section('content')
    <x-page-header :title="__('siswa.pengajuan.title')">
        <x-slot:actions>
            @if(! $pengajuanList->contains(fn($p) => in_array($p->status, ['pending', 'under_review'])))
                <x-btn-primary href="{{ route('pelajar.pengajuan-pendidikan.create') }}" size="md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('siswa.pengajuan.ajukan_perubahan') }}
                </x-btn-primary>
            @endif
        </x-slot:actions>
    </x-page-header>

    <x-card class="mb-4">
        <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-2">{{ __('siswa.pengajuan.profil_saat_ini') }}</h3>
        @if($profil)
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('siswa.pengajuan.jalur_pendidikan') }}</p>
                    <p class="font-medium text-slate-900 dark:text-white">{{ $profil->jalur?->nama ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('siswa.pengajuan.jenjang') }}</p>
                    <p class="font-medium text-slate-900 dark:text-white">{{ $profil->jenjang?->nama ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('siswa.pengajuan.tingkat') }}</p>
                    <p class="font-medium text-slate-900 dark:text-white">{{ $profil->tingkat?->nama ?? '-' }}</p>
                </div>
            </div>
        @else
            <p class="text-sm text-amber-600 dark:text-amber-400">{{ __('siswa.pengajuan.profil_belum_lengkap') }}</p>
        @endif
    </x-card>

    <x-card>
        <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">{{ __('siswa.pengajuan.riwayat_pengajuan') }}</h3>
        @if($pengajuanList->isEmpty())
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('siswa.pengajuan.belum_ada_pengajuan') }}</p>
        @else
            <x-table :headers="[
                __('siswa.pengajuan.tanggal'),
                __('siswa.pengajuan.jenis'),
                __('siswa.pengajuan.tujuan'),
                __('siswa.pengajuan.status'),
                __('common.actions'),
            ]">
                @foreach($pengajuanList as $p)
                    <x-tr>
                        <x-td>{{ $p->created_at->format('d/m/Y H:i') }}</x-td>
                        <x-td>{{ $p->jenis === 'kenaikan_kelas' ? __('siswa.pengajuan.kenaikan_kelas') : __('siswa.pengajuan.perpindahan_jenjang') }}</x-td>
                        <x-td>{{ $p->jenjang?->nama }} - {{ $p->tingkat?->nama }}</x-td>
                        <x-td>
                            @php
                                $map = [
                                    'pending' => ['variant' => 'pending', 'label' => __('siswa.pengajuan.menunggu')],
                                    'under_review' => ['variant' => 'info', 'label' => __('siswa.pengajuan.sedang_ditinjau')],
                                    'revision_required' => ['variant' => 'warning', 'label' => __('siswa.pengajuan.perlu_revisi')],
                                    'approved' => ['variant' => 'success', 'label' => __('siswa.pengajuan.disetujui')],
                                    'rejected' => ['variant' => 'rejected', 'label' => __('siswa.pengajuan.ditolak')],
                                ];
                                $m = $map[$p->status] ?? ['variant' => 'default', 'label' => ucfirst($p->status)];
                            @endphp
                            <x-badge :variant="$m['variant']">{{ $m['label'] }}</x-badge>
                        </x-td>
                        <x-td align="right">
                            <a href="{{ route('pelajar.pengajuan-pendidikan.show', $p) }}" class="inline-flex items-center justify-center min-h-11 min-w-11 text-emerald-700 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300 text-sm">{{ __('common.detail') }}</a>
                        </x-td>
                    </x-tr>
                @endforeach
            </x-table>
        @endif
    </x-card>
@endsection
