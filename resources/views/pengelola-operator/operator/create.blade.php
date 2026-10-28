@extends('layouts.app')

@section('title', 'Tambah Operator - Pengelola Operator')

@section('content')
    <x-back-link href="{{ route('pengelola-operator.operator.index') }}" label="Kembali" />
    <x-page-header title="Tambah Akun Operator" subtitle="Buat operator dan tugaskan kategori mata pelajaran" />

    <x-card>
        <form action="{{ route('pengelola-operator.operator.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid sm:grid-cols-2 gap-4">
                <x-form-input
                    name="name"
                    label="Nama"
                    type="text"
                    placeholder="Nama lengkap operator"
                    required
                />
                <x-form-input
                    name="email"
                    label="Email"
                    type="email"
                    placeholder="email@contoh.com"
                    required
                />
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <x-form-input
                    name="password"
                    label="Password"
                    type="password"
                    placeholder="Minimal 8 karakter"
                    required
                />
                <x-form-select
                    name="kategori_mapel"
                    label="Kategori Mata Pelajaran"
                    :options="$kategoriList"
                    required
                />
            </div>
            <x-btn-primary type="submit">Simpan</x-btn-primary>
        </form>
    </x-card>
@endsection
