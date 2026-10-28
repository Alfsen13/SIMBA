@extends('layouts.app')

@section('title', __('operator.title_user_edit') . ' - SIMBA')

@section('content')
    <x-back-link
        href="{{ route('operator.users.index') }}"
        :label="__('common.back')"
    />

    <x-page-header :title="__('operator.page_edit_user')" />

    <x-card class="max-w-xl">
        <form
            action="{{ route('operator.users.update', $user) }}"
            method="POST"
        >
            @csrf
            @method('PUT')
            <x-form-input
                name="name"
                :label="__('operator.th_name')"
                :value="$user->name"
                required
            />
            <x-form-input
                name="email"
                :label="__('operator.th_email')"
                type="email"
                :value="$user->email"
                required
            />
            <x-form-select
                name="role"
                :label="__('operator.th_role')"
                :options="['pengajar' => __('operator.role_pengajar'), 'pelajar' => __('operator.role_pelajar'), 'operator' => __('operator.role_operator')]"
                :value="$user->role"
                required
            />
            <x-btn-primary type="submit" class="w-full">
                {{ __('common.save_changes') }}
            </x-btn-primary>
        </form>
    </x-card>
@endsection
