@extends('layouts.app')

@section('title', __('guru_kelas.pertemuan.tambah_title') . ' - SIMBA')

@section('content')
    <x-back-link
        href="{{ route('pengajar.kelas.show', $kelas) }}"
        :label="__('common.back')"
    />

    <x-page-header
        :title="__('guru_kelas.pertemuan.tambah_title')"
        :subtitle="__('guru_kelas.kelas_label', ['nama' => $kelas->nama_kelas])"
    />

    <x-card>
        <form
            action="{{ route('pengajar.kelas.pertemuan.store', $kelas) }}"
            method="POST"
        >
            @csrf
            <x-form-columns :cols="2">
                <x-form-input
                    name="nama_pertemuan"
                    :label="__('guru_kelas.pertemuan.nama')"
                    :value="old('nama_pertemuan')"
                    required
                />
                <x-form-input
                    name="urutan"
                    type="number"
                    :label="__('guru_kelas.pertemuan.urutan')"
                    :value="old('urutan', 0)"
                    required
                />
            </x-form-columns>
            <x-form-columns :cols="2">
                <x-form-input
                    name="tanggal_mulai"
                    type="date"
                    :label="__('guru_kelas.pertemuan.tanggal_mulai')"
                    :value="old('tanggal_mulai')"
                />
                <x-form-input
                    name="tanggal_selesai"
                    type="date"
                    :label="__('guru_kelas.pertemuan.tanggal_selesai')"
                    :value="old('tanggal_selesai')"
                />
            </x-form-columns>
            <x-form-textarea
                name="deskripsi"
                :label="__('guru_kelas.pertemuan.deskripsi')"
                :value="old('deskripsi')"
                rows="3"
            />
            <label class="flex items-center gap-2 mt-4">
                <input type="hidden" name="dibuka" value="0">
                <input
                    type="checkbox"
                    name="dibuka"
                    value="1"
                    @checked(old('dibuka', false))
                    class="rounded border-slate-300 dark:border-slate-700 text-emerald-700 dark:text-emerald-400 focus:ring-emerald-500"
                >
                <span class="text-sm text-slate-700 dark:text-slate-300">{{ __('guru_kelas.pertemuan.buka_untuk_pelajar') }}</span>
            </label>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                {{ __('guru_kelas.pertemuan.catatan_kunci') }}
            </p>
            <x-btn-primary type="submit" class="w-full">
                {{ __('common.save') }}
            </x-btn-primary>
        </form>
    </x-card>
@endsection
