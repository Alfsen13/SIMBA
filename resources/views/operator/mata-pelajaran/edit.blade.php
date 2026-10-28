@extends('layouts.app')

@section('title', 'Edit Mata Pelajaran - Operator')

@section('content')
    <x-back-link href="{{ route('operator.mata-pelajaran.index') }}" label="Kembali" />
    <x-page-header title="Edit Mata Pelajaran" subtitle="Untuk kategori {{ $mataPelajaran->kategori }}" />

    <x-card>
        <form action="{{ route('operator.mata-pelajaran.update', $mataPelajaran) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid sm:grid-cols-2 gap-4">
                <x-form-input
                    name="kode"
                    label="Kode Mata Pelajaran"
                    type="text"
                    :value="old('kode', $mataPelajaran->kode)"
                    placeholder="Contoh: MTH101"
                    required
                />
                <x-form-input
                    name="nama"
                    label="Nama Mata Pelajaran"
                    type="text"
                    :value="old('nama', $mataPelajaran->nama)"
                    placeholder="Contoh: Matematika"
                    required
                />
            </div>
            <x-btn-primary type="submit">Simpan</x-btn-primary>
        </form>
    </x-card>
@endsection
