@extends('layouts.app')

@section('title', 'Edit Operator - Pengelola Operator')

@section('content')
    <x-back-link href="{{ route('pengelola-operator.operator.index') }}" label="Kembali" />
    <x-page-header title="Edit Akun Operator" subtitle="Perbarui data dan kategori tugas operator" />

    <x-card>
        <form action="{{ route('pengelola-operator.operator.update', $operator) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid sm:grid-cols-2 gap-4">
                <x-form-input
                    name="name"
                    label="Nama"
                    type="text"
                    :value="old('name', $operator->name)"
                    required
                />
                <x-form-input
                    name="email"
                    label="Email"
                    type="email"
                    :value="old('email', $operator->email)"
                    required
                />
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <x-form-input
                    name="password"
                    label="Password Baru (opsional)"
                    type="password"
                    placeholder="Kosongkan jika tidak ingin mengubah"
                />
                <x-form-select
                    name="kategori_mapel"
                    label="Kategori Mata Pelajaran"
                    :options="$kategoriList"
                    :value="old('kategori_mapel', $operator->kategori_mapel)"
                    required
                />
            </div>
            <x-btn-primary type="submit">Simpan</x-btn-primary>
        </form>
    </x-card>
@endsection
