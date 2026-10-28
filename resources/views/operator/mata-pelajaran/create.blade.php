@extends('layouts.app')

@section('title', 'Tambah Mata Pelajaran - Operator')

@section('content')
    <x-back-link href="{{ route('operator.mata-pelajaran.index') }}" label="Kembali" />
    <x-page-header title="Tambah Mata Pelajaran" subtitle="Untuk kategori {{ auth()->user()->kategori_mapel }}" />

    <x-card>
        <form action="{{ route('operator.mata-pelajaran.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid sm:grid-cols-2 gap-4">
                <x-form-input
                    name="kode"
                    label="Kode Mata Pelajaran"
                    type="text"
                    placeholder="Contoh: MTH101"
                    required
                />
                <x-form-input
                    name="nama"
                    label="Nama Mata Pelajaran"
                    type="text"
                    placeholder="Contoh: Matematika"
                    required
                />
            </div>
            <x-btn-primary type="submit">Simpan</x-btn-primary>
        </form>
    </x-card>
@endsection
