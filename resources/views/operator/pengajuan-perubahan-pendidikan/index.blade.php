@extends('layouts.app')

@section('title', __('operator.pengajuan_perubahan.title') . ' - Operator')

@section('content')
    <x-page-header :title="__('operator.pengajuan_perubahan.title')" />

    <x-card class="mb-4">
        <form method="GET" class="grid grid-cols-1 items-end gap-2 sm:grid-cols-[minmax(0,1fr)_auto] [&_label]:cursor-pointer [&_select]:min-h-11 [&_select]:cursor-pointer">
            <x-form-select
                name="status"
                :label="__('operator.pengajuan_perubahan.filter_label')"
                :placeholder="__('operator.pengajuan_perubahan.status_all')"
                :options="[
                    '' => __('operator.pengajuan_perubahan.status_all'),
                    'pending' => __('operator.pengajuan_perubahan.status.pending'),
                    'under_review' => __('operator.pengajuan_perubahan.status.under_review'),
                    'revision_required' => __('operator.pengajuan_perubahan.status.revision_required'),
                    'approved' => __('operator.pengajuan_perubahan.status.approved'),
                    'rejected' => __('operator.pengajuan_perubahan.status.rejected'),
                ]"
                :value="$status ?? ''"
            />
            <x-btn-primary type="submit" size="sm" class="min-h-11 min-w-11 cursor-pointer">{{ __('operator.pengajuan_perubahan.filter_action') }}</x-btn-primary>
        </form>
    </x-card>

    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('operator.pengajuan_perubahan.student') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('operator.pengajuan_perubahan.type') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('operator.pengajuan_perubahan.current_profile') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('operator.pengajuan_perubahan.target') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('operator.pengajuan_perubahan.status_label') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($pengajuanList as $p)
                        <tr>
                            <td class="px-6 py-3 text-slate-900 dark:text-slate-100">
                                <div class="font-medium">{{ $p->user->name }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $p->user->email }}</div>
                            </td>
                            <td class="px-6 py-3 text-slate-600 dark:text-slate-400">
                                {{ __('operator.pengajuan_perubahan.type_' . $p->jenis) }}
                            </td>
                            <td class="px-6 py-3 text-slate-600 dark:text-slate-400">
                                {{ $p->user->profilPendidikan?->jenjang?->nama ?? '-' }} - {{ $p->user->profilPendidikan?->tingkat?->nama ?? '-' }}
                            </td>
                            <td class="px-6 py-3 text-slate-900 dark:text-white">
                                {{ $p->jenjang?->nama ?? '-' }} - {{ $p->tingkat?->nama ?? '-' }}
                            </td>
                            <td class="px-6 py-3">
                                @php
                                    $map = [
                                        'pending' => ['key' => 'pending', 'variant' => 'pending'],
                                        'under_review' => ['key' => 'under_review', 'variant' => 'default'],
                                        'revision_required' => ['key' => 'revision_required', 'variant' => 'default'],
                                        'approved' => ['key' => 'approved', 'variant' => 'verified'],
                                        'rejected' => ['key' => 'rejected', 'variant' => 'rejected'],
                                    ];
                                    $m = $map[$p->status] ?? ['key' => null, 'variant' => 'default'];
                                @endphp
                                <x-badge :variant="$m['variant']" :label="$m['key'] ? __('operator.pengajuan_perubahan.status.' . $m['key']) : $p->status" />
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('operator.pengajuan-perubahan-pendidikan.show', $p) }}" class="inline-flex min-h-11 min-w-11 cursor-pointer items-center justify-center px-2 text-xs text-emerald-700 dark:text-emerald-400 hover:underline">{{ __('operator.pengajuan_perubahan.review') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center">
                                @if(in_array($status, ['pending', 'under_review', 'revision_required', 'approved', 'rejected'], true))
                                    <x-empty-state :message="__('operator.pengajuan_perubahan.empty_filtered')" />
                                    <a href="{{ route('operator.pengajuan-perubahan-pendidikan.index') }}" class="inline-flex min-h-11 cursor-pointer items-center justify-center px-3 text-sm font-medium text-emerald-700 dark:text-emerald-400 hover:underline">
                                        {{ __('operator.pengajuan_perubahan.clear_filter') }}
                                    </a>
                                @else
                                    <x-empty-state :message="__('operator.pengajuan_perubahan.empty_list')" />
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $pengajuanList->links() }}
        </div>
    </x-card>
@endsection
