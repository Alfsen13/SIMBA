@extends('layouts.app')

@section('title', __('pengelola_operator.kategori_mata_pelajaran.create_page_title'))

@section('content')
    <x-back-link href="{{ route('pengelola-operator.kategori-mata-pelajaran.index') }}" :label="__('pengelola_operator.kategori_mata_pelajaran.back')" class="min-h-11 min-w-11 items-center justify-center px-2" />
    <x-page-header :title="__('pengelola_operator.kategori_mata_pelajaran.create_title')" :subtitle="__('pengelola_operator.kategori_mata_pelajaran.create_subtitle')" />

    <x-card>
        <form action="{{ route('pengelola-operator.kategori-mata-pelajaran.store') }}" method="POST" class="space-y-4">
            @csrf
            <x-form-input
                name="kategori"
                :label="__('pengelola_operator.kategori_mata_pelajaran.name_label')"
                type="text"
                :placeholder="__('pengelola_operator.kategori_mata_pelajaran.name_placeholder')"
                required
            />
            <x-btn-primary type="submit" class="min-h-11 min-w-11 justify-center">{{ __('pengelola_operator.kategori_mata_pelajaran.save') }}</x-btn-primary>
        </form>
    </x-card>
@endsection
