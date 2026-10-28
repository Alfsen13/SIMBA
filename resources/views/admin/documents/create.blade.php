@extends('layouts.app')

@section('title', __('operator.title_document_upload') . ' - SIMBA')

@section('content')
    <x-back-link
        href="{{ route('operator.documents.index') }}"
        :label="__('common.back')"
    />

    <x-page-header
        :title="__('operator.upload_document')"
        :subtitle="__('operator.documents_upload_subtitle')"
    />

    <div class="max-w-2xl">
        <form
            action="{{ route('operator.documents.store') }}"
            method="POST"
            enctype="multipart/form-data"
        >
            @csrf

            <x-card>
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">{{ __('operator.label_document_file') }}</label>
                    <div
                        class="relative border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-lg p-8 text-center hover:border-emerald-300 transition-colors cursor-pointer"
                        x-data="{ fileName: '' }"
                        @dragover.prevent="$el.classList.add('border-emerald-300', 'bg-emerald-50')"
                        @dragleave.prevent="$el.classList.remove('border-emerald-300', 'bg-emerald-50')"
                        @drop.prevent="
                            $el.classList.remove('border-emerald-300', 'bg-emerald-50');
                            const f = $event.dataTransfer.files[0];
                            if (f) { $el.querySelector('input[type=file]').files = $event.dataTransfer.files; fileName = f.name; }"
                    >
                        <input
                            type="file"
                            name="file"
                            accept=".pdf,.txt,.md,.csv"
                            class="hidden"
                            @change="fileName = $event.target.files[0]?.name || ''"
                        >
                        <template x-if="!fileName">
                            <div>
                                <svg class="w-10 h-10 text-slate-500 dark:text-slate-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-sm text-slate-600 dark:text-slate-400">
                                    {{ __('operator.drag_here') }} <span class="text-emerald-700 dark:text-emerald-400 font-medium">{{ __('operator.click_to_choose') }}</span>
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('operator.file_types_hint') }}</p>
                            </div>
                        </template>
                        <template x-if="fileName">
                            <div>
                                <svg class="w-10 h-10 text-green-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-sm text-slate-900 dark:text-slate-100 font-medium" x-text="fileName"></p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('operator.click_replace_file') }}</p>
                            </div>
                        </template>
                    </div>
                    @error('file')
                        <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-5">
                    <x-form-input
                        name="kategori"
                        :label="__('operator.label_kategori_subject')"
                        :placeholder="__('operator.kategori_placeholder_short')"
                        :value="old('kategori')"
                    />
                </div>

                <x-btn-primary type="submit" size="lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    {{ __('operator.upload_and_process') }}
                </x-btn-primary>
            </x-card>
        </form>
    </div>
@endsection
