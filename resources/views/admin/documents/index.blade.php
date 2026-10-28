@extends('layouts.app')

@section('title', __('operator.title_documents') . ' - SIMBA')

@section('content')
    <x-page-header
        :title="__('operator.title_documents')"
        :subtitle="__('operator.documents_subtitle')"
    >
        <x-slot:actions>
            <x-btn-primary
                href="{{ route('operator.documents.create') }}"
                size="md"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('operator.upload_document') }}
            </x-btn-primary>
        </x-slot:actions>
    </x-page-header>

    @forelse($grouped as $kategori => $docs)
        <div class="mb-6">
            <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/80">
                <div class="w-2 h-2 rounded-full bg-emerald-600"></div>
                <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $kategori }}</h2>
                <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('operator.documents_count', ['count' => $docs->count()]) }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-900/30">
                            <x-th align="left">{{ __('operator.th_name_doc') }}</x-th>
                            <x-th align="left">{{ __('operator.th_type') }}</x-th>
                            <x-th align="left">{{ __('operator.th_size') }}</x-th>
                            <x-th align="left">{{ __('common.status') }}</x-th>
                            <x-th align="left">{{ __('operator.th_uploaded') }}</x-th>
                            <x-th align="right">{{ __('common.actions') }}</x-th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                        @foreach($docs as $doc)
                            <x-tr>
                                <x-td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-emerald-100 to-purple-100 flex items-center justify-center text-sm font-semibold text-emerald-700 dark:text-emerald-300 shrink-0">
                                            {{ substr($doc->original_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-900 dark:text-slate-100">{{ $doc->original_name }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $doc->user?->name ?? '—' }}</p>
                                        </div>
                                    </div>
                                </x-td>
                                <x-td>
                                    <span class="font-mono text-xs bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-lg text-slate-700 dark:text-slate-300">{{ strtoupper($doc->type) }}</span>
                                </x-td>
                                <x-td>{{ number_format($doc->size / 1024, 1) }} KB</x-td>
                                <x-td>
                                    <x-badge
                                        :variant="$doc->status === 'completed' ? 'success' : ($doc->status === 'processing' ? 'info' : ($doc->status === 'failed' ? 'danger' : 'warning'))"
                                        dot
                                    >
                                        {{ ucfirst($doc->status) }}
                                    </x-badge>
                                </x-td>
                                <x-td muted class="text-xs">{{ $doc->created_at->diffForHumans() }}</x-td>
                                <x-td align="right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a
                                            href="{{ route('operator.documents.show', $doc) }}"
                                            class="p-2 text-slate-500 dark:text-slate-400 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-600/10 rounded-lg transition-colors"
                                            title="{{ __('operator.title_view_detail') }}"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        @if($doc->status === 'failed')
                                            <form
                                                action="{{ route('operator.documents.reprocess', $doc) }}"
                                                method="POST"
                                                class="inline"
                                            >
                                                @csrf
                                                <button
                                                    class="p-2 text-slate-500 dark:text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors"
                                                    title="{{ __('operator.title_reprocess') }}"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                        <form
                                            action="{{ route('operator.documents.destroy', $doc) }}"
                                            method="POST"
                                            class="inline"
                                            onsubmit="return confirm('{{ __('operator.confirm_delete_document') }}')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                class="p-2 text-slate-500 dark:text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors"
                                                title="{{ __('common.delete') }}"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </x-td>
                            </x-tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <x-card>
            <x-empty-state
                icon='<svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
                :message="__('operator.empty_documents')"
                action="{{ route('operator.documents.create') }}"
                :actionLabel="__('operator.action_upload_first')"
            />
        </x-card>
    @endforelse

    @if($documents->hasPages())
        <div class="mt-4">
            {{ $documents->appends(request()->query())->links() }}
        </div>
    @endif
@endsection
