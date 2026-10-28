@extends('layouts.app')

@section('title', 'Tambah Kategori Mata Pelajaran - Pengelola Operator')

@section('content')
    <x-back-link href="{{ route('pengelola-operator.kategori-mata-pelajaran.index') }}" label="Kembali" />
    <x-page-header title="Tambah Kategori Mata Pelajaran" subtitle="Buat kategori baru" />

    <x-card>
        <form action="{{ route('pengelola-operator.kategori-mata-pelajaran.store') }}" method="POST" class="space-y-4">
            @csrf
            <x-form-input
                name="kategori"
                label="Nama Kategori"
                type="text"
                placeholder="Contoh: Matematika"
                required
            />
            <x-btn-primary type="submit">Simpan</x-btn-primary>
        </form>
    </x-card>
@endsection
