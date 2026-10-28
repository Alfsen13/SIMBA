@extends('layouts.app')

@section('title', __('guru_kelas.materi.tambah_title') . ' - SIMBA')

@section('content')
    <x-back-link
        href="{{ route('pengajar.pertemuan.show', $pertemuan) }}"
        :label="__('common.back')"
    />

    <x-page-header :title="__('guru_kelas.materi.tambah_title')" />

    <div class="mb-4 text-sm text-slate-500 dark:text-slate-400">
        {{ __('guru_kelas.kelas_prefix') }} <span class="font-medium text-slate-700 dark:text-slate-300">{{ $pertemuan->kelas->nama_kelas }}</span>
        &middot; {{ __('guru_kelas.pertemuan_prefix') }} <span class="font-medium text-slate-700 dark:text-slate-300">{{ $pertemuan->nama_pertemuan }}</span>
    </div>

    <x-card>
        <form
            action="{{ route('pengajar.materi.store') }}"
            method="POST"
            enctype="multipart/form-data"
        >
            @csrf
            <input type="hidden" name="kelas_id" value="{{ $pertemuan->kelas_id }}">
            <input type="hidden" name="pertemuan_id" value="{{ $pertemuan->id }}">
            <x-form-input
                name="judul"
                :label="__('guru_kelas.materi.judul')"
                :value="old('judul')"
                required
            />
            <x-form-textarea
                name="deskripsi"
                :label="__('guru_kelas.materi.deskripsi')"
                :value="old('deskripsi')"
                rows="3"
            />

            <x-file-preview :label="__('guru_kelas.materi.file')" />

            <x-btn-primary
                type="submit"
                class="w-full"
            >
                {{ __('common.save') }}
            </x-btn-primary>
        </form>
    </x-card>
@endsection
