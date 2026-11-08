@extends('layouts.app')

@section('title', __('pengelola_operator.operator.create_page_title'))

@section('content')
    <x-back-link href="{{ route('pengelola-operator.operator.index') }}" :label="__('pengelola_operator.operator.back')" class="min-h-11 min-w-11 px-2" />
    <x-page-header
        :title="__('pengelola_operator.operator.create_title')"
        :subtitle="__('pengelola_operator.operator.create_subtitle')"
    />

    <x-card>
        <form action="{{ route('pengelola-operator.operator.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid sm:grid-cols-2 gap-4">
                <x-form-input
                    name="name"
                    :label="__('pengelola_operator.operator.name_label')"
                    type="text"
                    :placeholder="__('pengelola_operator.operator.name_placeholder')"
                    input-class="min-h-11"
                    required
                />
                <x-form-input
                    name="email"
                    :label="__('pengelola_operator.operator.email_label')"
                    type="email"
                    :placeholder="__('pengelola_operator.operator.email_placeholder')"
                    input-class="min-h-11"
                    required
                />
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <x-form-input
                    name="password"
                    :label="__('pengelola_operator.operator.password_label')"
                    type="password"
                    :placeholder="__('pengelola_operator.operator.password_placeholder')"
                    input-class="min-h-11"
                    required
                />
                <x-form-select
                    name="kategori_mapel"
                    :label="__('pengelola_operator.operator.category_label')"
                    :options="$kategoriList"
                    select-class="min-h-11"
                    required
                />
            </div>
            <x-btn-primary type="submit" class="min-h-11 min-w-11">{{ __('pengelola_operator.operator.save') }}</x-btn-primary>
        </form>
    </x-card>
@endsection
