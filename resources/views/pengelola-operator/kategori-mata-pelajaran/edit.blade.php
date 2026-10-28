@extends('layouts.app')

@section('title', 'Edit Kategori Mata Pelajaran - Pengelola Operator')

@section('content')
    <x-back-link href="{{ route('pengelola-operator.kategori-mata-pelajaran.index') }}" label="Kembali" />
    <x-page-header title="Edit Kategori Mata Pelajaran" subtitle="Ubah nama kategori" />

    <x-card>
        <form action="{{ route('pengelola-operator.kategori-mata-pelajaran.update', $kategoriData) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <x-form-input
                name="kategori"
                label="Nama Kategori"
                type="text"
                :value="old('kategori', $kategoriData)"
                placeholder="Contoh: Matematika"
                required
            />
            <x-btn-primary type="submit">Simpan</x-btn-primary>
        </form>
    </x-card>
@endsection
