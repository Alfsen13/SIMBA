@extends('layouts.app')

@section('title', __('pengelola_operator.kategori_mata_pelajaran.page_title'))

@section('content')
    <x-page-header
        :title="__('pengelola_operator.kategori_mata_pelajaran.title')"
        :subtitle="__('pengelola_operator.kategori_mata_pelajaran.subtitle')"
    >
        <x-slot name="actions">
            <x-btn-primary :href="route('pengelola-operator.kategori-mata-pelajaran.create')" class="min-h-11 min-w-11 justify-center px-2">
                {{ __('pengelola_operator.kategori_mata_pelajaran.add') }}
            </x-btn-primary>
        </x-slot>
    </x-page-header>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('pengelola_operator.kategori_mata_pelajaran.column_category') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('pengelola_operator.kategori_mata_pelajaran.column_subjects') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">{{ __('pengelola_operator.kategori_mata_pelajaran.column_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($kategoriList as $kategori)
                        <tr>
                            <td class="px-6 py-3 text-slate-900 dark:text-slate-100">
                                <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs font-medium">
                                    {{ $kategori->nama }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-slate-600 dark:text-slate-400 text-xs">
                                {{ $kategori->mapel_count ?? '-' }}
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('pengelola-operator.kategori-mata-pelajaran.edit', $kategori->nama) }}" class="inline-flex min-h-11 min-w-11 items-center justify-center px-2 text-xs text-emerald-700 dark:text-emerald-400 hover:underline">{{ __('pengelola_operator.kategori_mata_pelajaran.edit') }}</a>
                                    <form action="{{ route('pengelola-operator.kategori-mata-pelajaran.destroy', $kategori->nama) }}" method="POST" onsubmit="return confirm('{{ __('pengelola_operator.kategori_mata_pelajaran.confirm_delete') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="inline-flex min-h-11 min-w-11 items-center justify-center px-2 text-xs text-red-600 dark:text-red-400 hover:underline">{{ __('pengelola_operator.kategori_mata_pelajaran.delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-slate-500">
                                <p class="text-sm">{{ __('pengelola_operator.kategori_mata_pelajaran.empty') }}</p>
                                <a href="{{ route('pengelola-operator.kategori-mata-pelajaran.create') }}" class="mt-2 inline-flex min-h-11 min-w-11 items-center gap-1.5 px-2 text-sm font-medium text-emerald-700 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors">
                                    {{ __('pengelola_operator.kategori_mata_pelajaran.add_first') }}
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
@endsection
