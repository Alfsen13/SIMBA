@extends('layouts.app')

@section('title', __('operator.mata_pelajaran.create_title') . ' - SIMBA')

@section('content')
    <x-back-link href="{{ route('operator.mata-pelajaran.index') }}" :label="__('common.back')" />
    <x-page-header
        :title="__('operator.mata_pelajaran.create_title')"
        :subtitle="__('operator.mata_pelajaran.category_subtitle', ['category' => auth()->user()->kategori_mapel])"
    />

    <x-card>
        <form action="{{ route('operator.mata-pelajaran.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid sm:grid-cols-2 gap-4">
                <x-form-input
                    name="kode"
                    :label="__('operator.mata_pelajaran.code_label')"
                    type="text"
                    :placeholder="__('operator.mata_pelajaran.code_placeholder')"
                    required
                />
                <x-form-input
                    name="nama"
                    :label="__('operator.mata_pelajaran.name_label')"
                    type="text"
                    :placeholder="__('operator.mata_pelajaran.name_placeholder')"
                    required
                />
            </div>
            <x-btn-primary type="submit">{{ __('common.save') }}</x-btn-primary>
        </form>
    </x-card>
@endsection
