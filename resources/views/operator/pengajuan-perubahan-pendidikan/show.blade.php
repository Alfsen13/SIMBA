@extends('layouts.app')

@section('title', __('operator.pengajuan_perubahan.review_title'))

@section('content')
    <x-back-link href="{{ route('operator.pengajuan-perubahan-pendidikan.index') }}" :label="__('common.back')" />
    <x-page-header :title="__('operator.pengajuan_perubahan.review_title')" />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <x-card>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">{{ __('operator.pengajuan_perubahan.student_profile') }}</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between gap-3"><span class="text-slate-500 dark:text-slate-400">{{ __('operator.pengajuan_perubahan.name') }}</span><span class="text-right text-slate-900 dark:text-white">{{ $pengajuan->user->name }}</span></div>
                <div class="flex justify-between gap-3"><span class="text-slate-500 dark:text-slate-400">{{ __('operator.pengajuan_perubahan.email') }}</span><span class="text-right text-slate-900 dark:text-white">{{ $pengajuan->user->email }}</span></div>
                <div class="flex justify-between gap-3"><span class="text-slate-500 dark:text-slate-400">{{ __('operator.pengajuan_perubahan.current_track') }}</span><span class="text-right text-slate-900 dark:text-white">{{ $pengajuan->user->profilPendidikan?->jalur?->nama ?? '-' }}</span></div>
                <div class="flex justify-between gap-3"><span class="text-slate-500 dark:text-slate-400">{{ __('operator.pengajuan_perubahan.current_level') }}</span><span class="text-right text-slate-900 dark:text-white">{{ $pengajuan->user->profilPendidikan?->jenjang?->nama ?? '-' }}</span></div>
                <div class="flex justify-between gap-3"><span class="text-slate-500 dark:text-slate-400">{{ __('operator.pengajuan_perubahan.current_grade') }}</span><span class="text-right text-slate-900 dark:text-white">{{ $pengajuan->user->profilPendidikan?->tingkat?->nama ?? '-' }}</span></div>
            </div>
        </x-card>

        <x-card>
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">{{ __('operator.pengajuan_perubahan.application') }}</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between gap-3"><span class="text-slate-500 dark:text-slate-400">{{ __('operator.pengajuan_perubahan.type') }}</span><span class="text-right text-slate-900 dark:text-white">{{ __('operator.pengajuan_perubahan.type_' . $pengajuan->jenis) }}</span></div>
                <div class="flex justify-between gap-3"><span class="text-slate-500 dark:text-slate-400">{{ __('operator.pengajuan_perubahan.target_track') }}</span><span class="text-right text-slate-900 dark:text-white">{{ $pengajuan->jalur?->nama ?? '-' }}</span></div>
                <div class="flex justify-between gap-3"><span class="text-slate-500 dark:text-slate-400">{{ __('operator.pengajuan_perubahan.target_level') }}</span><span class="text-right text-slate-900 dark:text-white">{{ $pengajuan->jenjang?->nama ?? '-' }}</span></div>
                <div class="flex justify-between gap-3"><span class="text-slate-500 dark:text-slate-400">{{ __('operator.pengajuan_perubahan.target_grade') }}</span><span class="text-right text-slate-900 dark:text-white">{{ $pengajuan->tingkat?->nama ?? '-' }}</span></div>
                @if($pengajuan->alasan)
                <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ __('operator.pengajuan_perubahan.student_reason') }}</p>
                    <p class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $pengajuan->alasan }}</p>
                </div>
                @endif
            </div>
        </x-card>
    </div>

    @if($pengajuan->catatan_operator)
        <x-card class="mt-4">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-2">{{ $pengajuan->alasan ? __('operator.pengajuan_perubahan.latest_operator_note') : __('siswa.pengajuan.catatan_lama') }}</h3>
            <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $pengajuan->catatan_operator }}</p>
            @if($pengajuan->reviewedBy)
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">{{ __('operator.pengajuan_perubahan.reviewed_by', ['name' => $pengajuan->reviewedBy->name]) }} • {{ $pengajuan->reviewed_at?->format('d/m/Y H:i') }}</p>
            @endif
        </x-card>
    @endif

    @if(in_array($pengajuan->status, ['pending', 'under_review', 'revision_required']))
        <x-card class="mt-4">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">{{ __('operator.pengajuan_perubahan.actions') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <form action="{{ route('operator.pengajuan-perubahan-pendidikan.setujui', $pengajuan) }}" method="POST" class="space-y-2">
                    @csrf
                    <x-form-textarea
                        name="catatan_operator"
                        :label="__('operator.pengajuan_perubahan.note_optional')"
                        :placeholder="__('operator.pengajuan_perubahan.note_placeholder')"
                        rows="3"
                    />
                    <x-btn-primary type="submit" class="min-h-11 cursor-pointer">{{ __('operator.pengajuan_perubahan.approve_update') }}</x-btn-primary>
                </form>
                <form action="{{ route('operator.pengajuan-perubahan-pendidikan.minta-revisi', $pengajuan) }}" method="POST" class="space-y-2">
                    @csrf
                    <x-form-textarea
                        name="catatan_operator"
                        :label="__('operator.pengajuan_perubahan.revision_note_required')"
                        :placeholder="__('operator.pengajuan_perubahan.revision_note_placeholder')"
                        rows="3"
                        required
                    />
                    <x-btn-primary type="submit" class="min-h-11 cursor-pointer">{{ __('operator.pengajuan_perubahan.request_revision') }}</x-btn-primary>
                </form>
                <form action="{{ route('operator.pengajuan-perubahan-pendidikan.tolak', $pengajuan) }}" method="POST" class="space-y-2">
                    @csrf
                    <x-form-textarea
                        name="catatan_operator"
                        :label="__('operator.pengajuan_perubahan.rejection_reason_required')"
                        :placeholder="__('operator.pengajuan_perubahan.rejection_reason_placeholder')"
                        rows="3"
                        required
                    />
                    <x-btn-primary type="submit" class="min-h-11 cursor-pointer">{{ __('operator.pengajuan_perubahan.reject') }}</x-btn-primary>
                </form>
            </div>
        </x-card>
    @endif
@endsection
