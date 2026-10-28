@extends('layouts.app')

@section('title', __('operator.title_news_create') . ' - SIMBA')

@section('content')
    <x-back-link
        href="{{ route('operator.berita.index') }}"
        :label="__('common.back')"
    />

    <x-page-header :title="__('operator.page_news_create')" />

    <div class="max-w-3xl">
        <form
            action="{{ route('operator.berita.store') }}"
            method="POST"
        >
            @csrf

            <x-card>
                <x-form-input
                    name="judul"
                    :label="__('operator.label_news_title')"
                    value="{{ old('judul') }}"
                    :placeholder="__('operator.placeholder_news_title')"
                    required
                />

                <x-form-textarea
                    name="konten"
                    :label="__('operator.label_content')"
                    value="{{ old('konten') }}"
                    :placeholder="__('operator.placeholder_content')"
                    rows="12"
                    required
                />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-form-input
                        name="excerpt"
                        :label="__('operator.label_summary_optional')"
                        value="{{ old('excerpt') }}"
                        :placeholder="__('operator.placeholder_summary')"
                    />

                    <x-form-input
                        name="published_at"
                        type="datetime-local"
                        :label="__('operator.th_published_at')"
                        value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}"
                    />
                </div>

                <x-btn-primary type="submit" size="lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('operator.publish_news') }}
                </x-btn-primary>
            </x-card>
        </form>
    </div>
@endsection
