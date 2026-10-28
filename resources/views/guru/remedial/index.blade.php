@extends('layouts.app')

@section('title', __('guru_analitik.remedial.page_title') . ' - SIMBA')

@section('content')
    <x-page-header
        :title="__('guru_analitik.remedial.page_title')"
        :subtitle="__('guru_analitik.remedial.count', ['jumlah' => $remedials->count()])"
    />

    @if($remedials->isEmpty())
        <x-empty-state
            icon='<svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>'
            :message="__('guru_analitik.remedial.empty')"
        />
    @else
        <x-card :padding="false">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <x-th align="left">{{ __('guru_analitik.label.no') }}</x-th>
                            <x-th align="left">{{ __('guru_analitik.remedial.student_name') }}</x-th>
                            <x-th align="left">{{ __('guru_analitik.remedial.weak_topic') }}</x-th>
                            <x-th align="left">{{ __('common.status') }}</x-th>
                            <x-th align="left">{{ __('guru_analitik.label.provider') }}</x-th>
                            <x-th align="left">{{ __('guru_analitik.label.date') }}</x-th>
                            <x-th align="left">{{ __('common.actions') }}</x-th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($remedials as $r)
                            <x-tr>
                                <x-td muted>
                                    {{ $loop->iteration }}
                                </x-td>
                                <x-td class="font-medium text-slate-900 dark:text-slate-100">
                                    {{ $r->siswa?->name ?: '-' }}
                                </x-td>
                                <x-td class="max-w-xs truncate">
                                    {{ $r->topik_lemah ?: '-' }}
                                </x-td>
                                <x-td>
                                    <x-badge
                                        variant="{{ $r->status === 'belum_dibaca' ? 'warning' : 'success' }}"
                                        :dot="true"
                                    >
                                        {{ $r->status === 'belum_dibaca' ? __('guru_analitik.remedial.status_unread') : ($r->status === 'selesai' ? __('guru_analitik.remedial.status_done') : __('guru_analitik.remedial.status_read')) }}
                                    </x-badge>
                                </x-td>
                                <x-td>
                                    {{ $r->provider_llm ?: '-' }}
                                </x-td>
                                <x-td muted>
                                    {{ $r->generated_at?->format('d M Y') ?: '-' }}
                                </x-td>
                                <x-td>
                                    <a
                                        href="{{ route('pengajar.remedial.show', $r) }}"
                                        class="inline-flex items-center gap-1 text-sm font-medium text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300"
                                    >
                                        {{ __('guru_analitik.remedial.view') }}
                                        <svg
                                            class="w-4 h-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 5l7 7-7 7"
                                            />
                                        </svg>
                                    </a>
                                </x-td>
                            </x-tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    @endif
@endsection
