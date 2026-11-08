@extends('layouts.app')

@section('title', __('operator.import_page_title'))

@section('content')
    <x-page-header :title="__('operator.import_page_title')">
        <x-slot name="actions">
            <a
                href="{{ route('operator.import.template', ['jenis' => 'pengguna']) }}"
                class="inline-flex min-h-11 items-center gap-2 px-4 py-2.5 text-sm font-medium text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-600/10 hover:bg-emerald-100 rounded-lg transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ __('operator.download_template') }}
            </a>
        </x-slot>
    </x-page-header>

    <x-alert />

    <x-card>
        <p class="text-slate-600 dark:text-slate-400 mb-4">
            {{ __('operator.import_desc_1') }} <strong>CSV</strong> {{ __('operator.import_desc_2') }} <strong>Excel</strong> {{ __('operator.import_desc_3') }}
            <code class="bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded text-sm">
                {{ __('operator.import_columns') }}
            </code>
        </p>

        <form method="POST" action="{{ route('operator.import.users') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label for="file" class="mb-1 block cursor-pointer text-xs font-semibold text-slate-600 dark:text-slate-300">
                    {{ __('operator.label_file_import') }}
                    <span class="text-red-600 dark:text-red-400">*</span>
                </label>
                <input
                    type="file"
                    name="file"
                    id="file"
                    accept=".csv,.txt,.xlsx"
                    required
                    class="min-h-11 max-w-full cursor-pointer text-xs text-slate-600 dark:text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 dark:file:bg-emerald-900/40 dark:file:text-emerald-300 hover:file:bg-emerald-100 dark:hover:file:bg-emerald-900/60"
                >
                @error('file')
                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <x-btn-primary type="submit" class="min-h-11">
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
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
                    />
                </svg>
                {{ __('operator.import_button') }}
            </x-btn-primary>
        </form>
    </x-card>
@endsection
