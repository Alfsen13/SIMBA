@extends('layouts.app')

@section('title', __('operator.title_news') . ' - SIMBA')

@section('content')
    <x-page-header
        :title="__('operator.news_page_title')"
        :subtitle="__('operator.news_subtitle')"
    >
        <x-slot:actions>
            <x-btn-primary
                href="{{ route('operator.berita.create') }}"
                size="md"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('operator.create_news') }}
            </x-btn-primary>
        </x-slot:actions>
    </x-page-header>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-900/30">
                        <x-th align="left">{{ __('operator.th_title') }}</x-th>
                        <x-th align="left">{{ __('operator.th_published_at') }}</x-th>
                        <x-th align="left">{{ __('operator.th_by') }}</x-th>
                        <x-th align="right">{{ __('common.actions') }}</x-th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    @forelse($berita as $item)
                        <x-tr>
                            <x-td>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-cyan-100 to-blue-100 dark:from-cyan-500/20 dark:to-blue-500/20 flex items-center justify-center text-sm font-semibold text-cyan-700 dark:text-cyan-300 shrink-0">
                                        {{ substr($item->judul, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ $item->judul }}</p>
                                        @if($item->excerpt)
                                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-[300px]">{{ $item->excerpt }}</p>
                                        @endif
                                    </div>
                                </div>
                            </x-td>
                            <x-td class="text-xs">
                                {{ $item->published_at ? $item->published_at->format('d M Y H:i') : '-' }}
                            </x-td>
                            <x-td>{{ $item->user?->name ?? '-' }}</x-td>
                            <x-td align="right">
                                <div class="flex items-center justify-end gap-1">
                                    <a
                                        href="{{ route('operator.berita.edit', $item) }}"
                                        class="p-2 text-slate-500 dark:text-slate-400 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-600/10 rounded-lg transition-colors"
                                        title="{{ __('common.edit') }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form
                                        action="{{ route('operator.berita.destroy', $item) }}"
                                        method="POST"
                                        class="inline"
                                        onsubmit="return confirm('{{ __('operator.confirm_delete_news') }}')"
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
                    @empty
                        <tr>
                            <x-td colspan="4" class="py-12 text-center">
                                <x-empty-state
                                    icon='<svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>'
                                    :message="__('operator.empty_news')"
                                    action="{{ route('operator.berita.create') }}"
                                    :actionLabel="__('operator.action_create_first_news')"
                                />
                            </x-td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($berita->hasPages())
            <div class="px-4 py-4 border-t border-slate-100 dark:border-slate-800">
                {{ $berita->appends(request()->query())->links() }}
            </div>
        @endif
    </x-card>
@endsection
