@extends('layouts.app')

@section('title', $document->original_name . ' - ' . __('operator.brand_admin'))

@section('content')
    <x-back-link
        href="{{ route('operator.documents.index') }}"
        :label="__('common.back')"
    />

    <x-page-header
        title="{{ $document->original_name }}"
        :subtitle="__('operator.documents_show_subtitle')"
    />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-1 space-y-4">
            <x-card>
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">{{ __('operator.document_info') }}</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('operator.th_type') }}</dt>
                        <dd class="font-medium text-slate-900 dark:text-slate-100">{{ strtoupper($document->type) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('operator.th_kategori') }}</dt>
                        <dd>
                            @if($document->kategori)
                                <x-badge variant="info" :label="$document->kategori" />
                            @else
                                <span class="text-slate-500 dark:text-slate-400">—</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('operator.th_size') }}</dt>
                        <dd class="font-medium text-slate-900 dark:text-slate-100">{{ number_format($document->size / 1024, 1) }} KB</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('common.status') }}</dt>
                        <dd>
                            <x-badge
                                :variant="$document->status === 'completed' ? 'success' : ($document->status === 'processing' ? 'info' : ($document->status === 'failed' ? 'danger' : 'warning'))"
                            >
                                {{ ucfirst($document->status) }}
                            </x-badge>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('operator.th_chunk') }}</dt>
                        <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $chunks->total() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('operator.th_uploaded') }}</dt>
                        <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $document->created_at->format('d M Y H:i') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('operator.th_by') }}</dt>
                        <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $document->user?->name ?? '-' }}</dd>
                    </div>
                </dl>

                @if($document->status === 'failed' && $document->error_message)
                    <div class="mt-4 p-3 bg-red-50 dark:bg-red-500/10 rounded-lg border border-red-200 dark:border-red-800">
                        <p class="text-xs font-medium text-red-700 dark:text-red-400">{{ __('operator.label_error') }}</p>
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $document->error_message }}</p>
                    </div>
                @endif
            </x-card>
        </div>

        <div class="lg:col-span-2">
            <x-card>
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">{{ __('operator.chunk_list') }} ({{ $chunks->total() }})</h2>
                <div class="space-y-3 max-h-[600px] overflow-y-auto">
                    @forelse($chunks as $chunk)
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-100 dark:border-slate-800">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-mono text-slate-500 dark:text-slate-400">#{{ $chunk->chunk_index }}</span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ $chunk->embedding ? count($chunk->embedding) . 'd' : __('operator.no_emb') }}</span>
                            </div>
                            <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">{{ Str::limit($chunk->content, 500) }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400 text-center py-6">{{ __('operator.empty_chunks') }}</p>
                    @endforelse
                </div>
                <div class="mt-4">
                    {{ $chunks->links() }}
                </div>
            </x-card>
        </div>
    </div>
@endsection
