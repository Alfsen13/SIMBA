@extends('layouts.app')

@section('title', __('pengelola_operator.operator.edit_page_title'))

@section('content')
    <x-back-link href="{{ route('pengelola-operator.operator.index') }}" :label="__('pengelola_operator.operator.back')" class="min-h-11 min-w-11 px-2" />
    <x-page-header
        :title="__('pengelola_operator.operator.edit_title')"
        :subtitle="__('pengelola_operator.operator.edit_subtitle')"
    />

    <x-card>
        <form action="{{ route('pengelola-operator.operator.update', $operator) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid sm:grid-cols-2 gap-4">
                <x-form-input
                    name="name"
                    :label="__('pengelola_operator.operator.name_label')"
                    type="text"
                    :value="old('name', $operator->name)"
                    input-class="min-h-11"
                    required
                />
                <x-form-input
                    name="email"
                    :label="__('pengelola_operator.operator.email_label')"
                    type="email"
                    :value="old('email', $operator->email)"
                    input-class="min-h-11"
                    required
                />
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <x-form-input
                    name="password"
                    :label="__('pengelola_operator.operator.new_password_label')"
                    type="password"
                    :placeholder="__('pengelola_operator.operator.new_password_placeholder')"
                    input-class="min-h-11"
                />
                <x-form-select
                    name="kategori_mapel"
                    :label="__('pengelola_operator.operator.category_label')"
                    :options="$kategoriList"
                    :value="old('kategori_mapel', $operator->kategori_mapel)"
                    select-class="min-h-11"
                    required
                />
            </div>
            <x-btn-primary type="submit" class="min-h-11 min-w-11">{{ __('pengelola_operator.operator.save') }}</x-btn-primary>
        </form>
    </x-card>
@endsection
