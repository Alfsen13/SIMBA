@extends('layouts.app')

@section('title', __('pengelola_operator.dashboard.page_title'))

@section('content')
    <x-page-header
        :title="__('pengelola_operator.dashboard.title')"
        :subtitle="__('pengelola_operator.dashboard.subtitle')"
    />

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card :label="__('pengelola_operator.dashboard.operators')" :value="$jumlahOperator" icon="user-group" />
        <x-stat-card :label="__('pengelola_operator.dashboard.categories')" :value="$jumlahKategori" icon="book-open" />
        <x-stat-card :label="__('pengelola_operator.dashboard.teachers')" :value="$jumlahPengajar" icon="academic-cap" />
        <x-stat-card :label="__('pengelola_operator.dashboard.students')" :value="$jumlahPelajar" icon="users" />
    </div>

    <x-card>
        <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
            <div>
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ __('pengelola_operator.dashboard.quick_actions') }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('pengelola_operator.dashboard.quick_actions_hint') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <x-btn-primary :href="route('pengelola-operator.kategori-mata-pelajaran.index')" class="min-h-11 min-w-11 justify-center">
                    {{ __('pengelola_operator.dashboard.manage_categories') }}
                </x-btn-primary>
                <x-btn-primary :href="route('pengelola-operator.operator.index')" class="min-h-11 min-w-11 justify-center bg-slate-700 hover:bg-slate-800">
                    {{ __('pengelola_operator.dashboard.manage_operators') }}
                </x-btn-primary>
            </div>
        </div>
    </x-card>
@endsection
